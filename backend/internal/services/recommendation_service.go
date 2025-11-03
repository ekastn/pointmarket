package services

import (
    "context"
    "database/sql"
    "fmt"
    "log"
    "pointmarket/backend/internal/dtos"
    "pointmarket/backend/internal/gateway"
    "pointmarket/backend/internal/store/gen"
    "sort"
    "sync"
    "time"
    "net/url"
)

// RecommendationService orchestrates fetching and mapping recommendations.
type RecommendationService struct {
    gateway    *gateway.RecommendationGateway
    studentSvc *StudentService
    missionSvc *MissionService
	// training control
	trainMux       sync.Mutex
	lastTrainStart time.Time
	trainCooldown  time.Duration
}

func NewRecommendationService(gw *gateway.RecommendationGateway, studentSvc *StudentService, missionSvc *MissionService) *RecommendationService {
	return &RecommendationService{gateway: gw, studentSvc: studentSvc, missionSvc: missionSvc, trainCooldown: 5 * time.Minute}
}

// GetStudentRecommendations returns mapped recommendations with fallback heuristics.
func (s *RecommendationService) GetStudentRecommendations(ctx context.Context, studentID string) (*dtos.StudentRecommendationsDTO, error) {
	// Best-effort ensure student mirrored upstream (non-blocking failure)
	go s.ensureUpstreamStudent(studentID)
	up, err := s.gateway.GetStudentRecommendations(studentID)
	if err != nil {
		log.Printf("upstream error %v", err)
		fb, fbErr := s.fallback(ctx, studentID)
		if fbErr != nil {
			return nil, fmt.Errorf("upstream error %v; fallback error %v", err, fbErr)
		}
		return fb, nil
	}

	mapped := s.mapUpstream(up)
	mapped.Source = s.detectSource(up)

	// Detect cold start: no trained Q-values AND zero items overall
	isUntrained := mapped.Source != "trained"
	empty := mapped.TotalItems == 0
	if isUntrained && empty {
		mapped.EmptyReason = "untrained"
		// Attempt to trigger training (non-blocking) with cooldown
		if s.maybeTriggerTraining() {
			mapped.TrainingPending = true
			mapped.Source = "training"
		}
	} else if !isUntrained && empty {
		mapped.EmptyReason = "trained_but_empty"
	}

	// Best-effort persistence of mission recommendations (side-effect, non-blocking)
	go s.persistRecommendedMissions(studentID, up)
	return mapped, nil
}

// GetStudentRecommendationsTrace returns the upstream trace payload for admin analysis.
func (s *RecommendationService) GetStudentRecommendationsTrace(ctx context.Context, studentID string) (map[string]interface{}, error) {
    up, err := s.gateway.GetStudentRecommendationsTrace(studentID)
    if err != nil {
        return nil, err
    }
    // Return only the trace block plus minimal header context
    trace := map[string]interface{}{
        "siswa_id":      up.StudentID,
        "current_state": up.CurrentState,
        "trace":         up.Trace,
        "message":       up.Message,
        "summary":       up.Summary,
    }
    return trace, nil
}

// Admin — Items management proxies
func (s *RecommendationService) AdminListItems(ctx context.Context, q url.Values) (map[string]interface{}, error) {
    return s.gateway.AdminListItems(ctx, q)
}

func (s *RecommendationService) AdminCreateItems(ctx context.Context, body []byte) (map[string]interface{}, error) {
    return s.gateway.AdminCreateItems(ctx, body)
}

func (s *RecommendationService) AdminUpdateItem(ctx context.Context, id int64, body []byte) (map[string]interface{}, error) {
    return s.gateway.AdminUpdateItem(ctx, id, body)
}

func (s *RecommendationService) AdminToggleItem(ctx context.Context, id int64, body []byte) (map[string]interface{}, error) {
    return s.gateway.AdminToggleItem(ctx, id, body)
}

func (s *RecommendationService) AdminDeleteItem(ctx context.Context, id int64, force bool) error {
    return s.gateway.AdminDeleteItem(ctx, id, force)
}

// EnrichItemRefsWithTitles augments rec-service items payload with human-friendly ref titles.
// It expects payload shape: { items: [ { ref_type, ref_id, ... } ], meta: {...} }
func (s *RecommendationService) EnrichItemRefsWithTitles(ctx context.Context, payload map[string]interface{}) map[string]interface{} {
    rawItems, ok := payload["items"].([]interface{})
    if !ok || len(rawItems) == 0 {
        return payload
    }
    // Collect IDs by ref_type
    missionIDs := make([]int64, 0)
    rewardIDs := make([]int64, 0)
    punishIDs := make([]int64, 0)
    coachIDs := make([]int64, 0)
    productIDs := make([]int64, 0)

    for _, it := range rawItems {
        m, _ := it.(map[string]interface{})
        if m == nil { continue }
        rtype, _ := m["ref_type"].(string)
        // ref_id may be float64 from JSON decode
        var rid int64
        switch v := m["ref_id"].(type) {
        case float64:
            rid = int64(v)
        case int64:
            rid = v
        case int:
            rid = int64(v)
        case string:
            // ignore non-numeric
        }
        if rid <= 0 { continue }
        switch rtype {
        case "mission":
            missionIDs = append(missionIDs, rid)
        case "reward":
            rewardIDs = append(rewardIDs, rid)
        case "punishment":
            punishIDs = append(punishIDs, rid)
        case "coaching":
            coachIDs = append(coachIDs, rid)
        case "product":
            productIDs = append(productIDs, rid)
        }
    }
    // Fetch titles by type
    mByID := map[int64]gen.Mission{}
    rByID := map[int64]gen.Reward{}
    pByID := map[int64]gen.Punishment{}
    cByID := map[int64]gen.Coaching{}
    prByID := map[int64]gen.Product{}

    if len(missionIDs) > 0 {
        if rows, err := s.studentSvc.q.GetMissionsByIDs(ctx, missionIDs); err == nil {
            for _, r := range rows { mByID[r.ID] = r }
        }
    }
    if len(rewardIDs) > 0 {
        if rows, err := s.studentSvc.q.GetRewardsByIDs(ctx, rewardIDs); err == nil {
            for _, r := range rows { rByID[r.ID] = r }
        }
    }
    if len(punishIDs) > 0 {
        if rows, err := s.studentSvc.q.GetPunishmentsByIDs(ctx, punishIDs); err == nil {
            for _, r := range rows { pByID[r.ID] = r }
        }
    }
    if len(coachIDs) > 0 {
        if rows, err := s.studentSvc.q.GetCoachingsByIDs(ctx, coachIDs); err == nil {
            for _, r := range rows { cByID[r.ID] = r }
        }
    }
    if len(productIDs) > 0 {
        if rows, err := s.studentSvc.q.GetProductsByIDs(ctx, productIDs); err == nil {
            for _, r := range rows { prByID[r.ID] = r }
        }
    }

    // Attach ref_title
    for _, it := range rawItems {
        m, _ := it.(map[string]interface{})
        if m == nil { continue }
        rtype, _ := m["ref_type"].(string)
        var rid int64
        switch v := m["ref_id"].(type) {
        case float64:
            rid = int64(v)
        case int64:
            rid = v
        case int:
            rid = int64(v)
        case string:
            // try parse
        }
        var title string
        switch rtype {
        case "mission":
            if r, ok := mByID[rid]; ok { title = r.Title }
        case "reward":
            if r, ok := rByID[rid]; ok { title = r.Title }
        case "punishment":
            if r, ok := pByID[rid]; ok { title = r.Title }
        case "coaching":
            if r, ok := cByID[rid]; ok { title = r.Title }
        case "product":
            if r, ok := prByID[rid]; ok { title = r.Name }
        }
        if title != "" {
            m["ref_title"] = title
        }
    }
    return payload
}

// Admin list states proxy
func (s *RecommendationService) AdminListStates(ctx context.Context, q url.Values) (map[string]interface{}, error) {
    return s.gateway.AdminListStates(ctx, q)
}

// Admin search refs proxy
func (s *RecommendationService) AdminSearchRefs(ctx context.Context, q url.Values) (map[string]interface{}, error) {
    return s.gateway.AdminSearchRefs(ctx, q)
}

// maybeTriggerTraining ensures we don't spam /train. Returns true if a trigger was started.
func (s *RecommendationService) maybeTriggerTraining() bool {
	s.trainMux.Lock()
	defer s.trainMux.Unlock()
	now := time.Now()
	if !s.lastTrainStart.IsZero() && now.Sub(s.lastTrainStart) < s.trainCooldown {
		return false
	}
	s.lastTrainStart = now
	go func(start time.Time) {
		ctx, cancel := context.WithTimeout(context.Background(), 15*time.Second)
		defer cancel()
		if err := s.gateway.Train(ctx, 50); err != nil {
			log.Printf("recommendation training trigger failed: %v", err)
			return
		}
		log.Printf("recommendation training triggered successfully at %s", start.Format(time.RFC3339))
	}(now)
	return true
}

// mapUpstream converts upstream response into internal DTO.
func (s *RecommendationService) mapUpstream(up *gateway.UpstreamResponseExportShim) *dtos.StudentRecommendationsDTO {
	actionNames := make([]string, 0, len(up.ActionRecommendations))
	for name := range up.ActionRecommendations {
		actionNames = append(actionNames, name)
	}
	sort.Strings(actionNames)

	// Collect refs by type across actions
	coachIDsSet := map[int64]struct{}{}
	rewardIDsSet := map[int64]struct{}{}
	punishIDsSet := map[int64]struct{}{}
	missionIDsSet := map[int64]struct{}{}
	productIDsSet := map[int64]struct{}{}

	for _, name := range actionNames {
		ua := up.ActionRecommendations[name]
		if len(ua.ItemsRefs) > 0 {
			for _, ref := range ua.ItemsRefs {
				switch ref.RefType {
				case "coaching":
					coachIDsSet[ref.RefID] = struct{}{}
				case "reward":
					rewardIDsSet[ref.RefID] = struct{}{}
				case "punishment":
					punishIDsSet[ref.RefID] = struct{}{}
				case "mission":
					missionIDsSet[ref.RefID] = struct{}{}
				case "product":
					productIDsSet[ref.RefID] = struct{}{}
				}
			}
		}
	}

	// Convert sets to slices
	coachIDs := make([]int64, 0, len(coachIDsSet))
	for id := range coachIDsSet {
		coachIDs = append(coachIDs, id)
	}
	rewardIDs := make([]int64, 0, len(rewardIDsSet))
	for id := range rewardIDsSet {
		rewardIDs = append(rewardIDs, id)
	}
	punishIDs := make([]int64, 0, len(punishIDsSet))
	for id := range punishIDsSet {
		punishIDs = append(punishIDs, id)
	}
	missionIDs := make([]int64, 0, len(missionIDsSet))
	for id := range missionIDsSet {
		missionIDs = append(missionIDs, id)
	}
	productIDs := make([]int64, 0, len(productIDsSet))
	for id := range productIDsSet {
		productIDs = append(productIDs, id)
	}

	// Batch fetch by type (skip empty slices)
	cByID := map[int64]gen.Coaching{}
	rByID := map[int64]gen.Reward{}
	pByID := map[int64]gen.Punishment{}
	mByID := map[int64]gen.Mission{}
	prByID := map[int64]gen.Product{}

	if len(coachIDs) > 0 {
		if rows, err := s.studentSvc.q.GetCoachingsByIDs(context.Background(), coachIDs); err == nil {
			for _, r := range rows {
				cByID[r.ID] = r
			}
		} else {
			log.Printf("GetCoachingsByIDs error: %v", err)
		}
	}
	if len(rewardIDs) > 0 {
		if rows, err := s.studentSvc.q.GetRewardsByIDs(context.Background(), rewardIDs); err == nil {
			for _, r := range rows {
				rByID[r.ID] = r
			}
		} else {
			log.Printf("GetRewardsByIDs error: %v", err)
		}
	}
	if len(punishIDs) > 0 {
		if rows, err := s.studentSvc.q.GetPunishmentsByIDs(context.Background(), punishIDs); err == nil {
			for _, r := range rows {
				pByID[r.ID] = r
			}
		} else {
			log.Printf("GetPunishmentsByIDs error: %v", err)
		}
	}
	if len(missionIDs) > 0 {
		if rows, err := s.studentSvc.q.GetMissionsByIDs(context.Background(), missionIDs); err == nil {
			for _, r := range rows {
				mByID[r.ID] = r
			}
		} else {
			log.Printf("GetMissionsByIDs error: %v", err)
		}
	}
	if len(productIDs) > 0 {
		if rows, err := s.studentSvc.q.GetProductsByIDs(context.Background(), productIDs); err == nil {
			for _, r := range rows {
				prByID[r.ID] = r
			}
		} else {
			log.Printf("GetProductsByIDs error: %v", err)
		}
	}

	// Build actions preserving order; prefer items_refs; fallback to legacy items text if provided
	resActions := make([]dtos.RecommendationAction, 0, len(actionNames))
	for _, name := range actionNames {
		ua := up.ActionRecommendations[name]
		items := make([]dtos.RecommendationItem, 0)
		if len(ua.ItemsRefs) > 0 {
			for _, ref := range ua.ItemsRefs {
				switch ref.RefType {
				case "coaching":
					if c, ok := cByID[ref.RefID]; ok {
						items = append(items, dtos.RecommendationItem{
							Title:             c.Title,
							Description:       nsToString(c.Description),
							Category:          "",
							TargetAudience:    "",
							DifficultyLevel:   "",
							EstimatedDuration: niToDurationStr(c.DurationMinutes),
						})
					}
				case "reward":
					if r, ok := rByID[ref.RefID]; ok {
						items = append(items, dtos.RecommendationItem{
							Title:             r.Title,
							Description:       nsToString(r.Description),
							Category:          r.Type,
							TargetAudience:    "",
							DifficultyLevel:   "",
							EstimatedDuration: "",
						})
					}
				case "punishment":
					if p, ok := pByID[ref.RefID]; ok {
						items = append(items, dtos.RecommendationItem{
							Title:             p.Title,
							Description:       nsToString(p.Description),
							Category:          "",
							TargetAudience:    "",
							DifficultyLevel:   "",
							EstimatedDuration: "",
						})
					}
				case "mission":
					if m, ok := mByID[ref.RefID]; ok {
						items = append(items, dtos.RecommendationItem{
							Title:             m.Title,
							Description:       nsToString(m.Description),
							Category:          "",
							TargetAudience:    "",
							DifficultyLevel:   "",
							EstimatedDuration: "",
						})
					}
				case "product":
					if pr, ok := prByID[ref.RefID]; ok {
						items = append(items, dtos.RecommendationItem{
							Title:             pr.Name,
							Description:       nsToString(pr.Description),
							Category:          pr.Type,
							TargetAudience:    "",
							DifficultyLevel:   "",
							EstimatedDuration: "",
						})
					}
				default:
					// Ignore unsupported ref types for now
				}
			}
		} else if len(ua.Items) > 0 {
			// Legacy path: map textual items as before
			for _, it := range ua.Items {
				items = append(items, dtos.RecommendationItem{
					Title:             it.Title,
					Description:       it.Description,
					Category:          it.Category,
					TargetAudience:    it.TargetAudience,
					DifficultyLevel:   it.DifficultyLevel.Value,
					EstimatedDuration: it.EstimatedDuration,
				})
			}
		}

		resActions = append(resActions, dtos.RecommendationAction{
			ActionCode: ua.ActionCode,
			ActionName: name,
			QValue:     ua.QValue,
			Items:      items,
		})
	}

	return &dtos.StudentRecommendationsDTO{
		StudentID:    up.StudentID,
		State:        up.CurrentState,
		ModelVersion: "",
		Actions:      resActions,
		TotalActions: len(resActions),
		TotalItems:   totalItems(resActions),
	}
}

func nsToString(ns sql.NullString) string {
	if ns.Valid {
		return ns.String
	}
	return ""
}

func niToDurationStr(n sql.NullInt32) string {
	if n.Valid && n.Int32 > 0 {
		return fmt.Sprintf("%dm", n.Int32)
	}
	return ""
}

// detectSource interprets upstream summary for a source label.
func (s *RecommendationService) detectSource(up *gateway.UpstreamResponseExportShim) string {
	if up.Summary.HasTrainedQValues {
		return "trained"
	}
	return "default"
}

// fallback builds heuristic default recommendations when upstream unavailable.
func (s *RecommendationService) fallback(ctx context.Context, studentID string) (*dtos.StudentRecommendationsDTO, error) {
	// fetch student to derive simple heuristic, ignoring errors for now
	_, _ = s.studentSvc.GetByStudentID(ctx, studentID)
	// simple static defaults
	actions := []dtos.RecommendationAction{
		{ActionCode: 105, ActionName: "Misi", QValue: 0.6, Items: []dtos.RecommendationItem{{Title: "Tantangan Harian", Description: "Misi sederhana untuk menjaga konsistensi"}}},
		{ActionCode: 101, ActionName: "Reward", QValue: 0.5, Items: []dtos.RecommendationItem{{Title: "Poin Bonus", Description: "Hadiah kecil untuk progres"}}},
	}
	return &dtos.StudentRecommendationsDTO{
		StudentID:    studentID,
		State:        "unknown",
		Source:       "fallback",
		ModelVersion: "",
		Actions:      actions,
		TotalActions: len(actions),
		TotalItems:   len(actions[0].Items) + len(actions[1].Items),
	}, nil
}

func totalItems(actions []dtos.RecommendationAction) int {
	t := 0
	for _, a := range actions {
		t += len(a.Items)
	}
	return t
}

// persistRecommendedMissions ensures all mission refs are created in user_missions with not_started status.
func (s *RecommendationService) persistRecommendedMissions(studentID string, up *gateway.UpstreamResponseExportShim) {
	if s.missionSvc == nil || s.studentSvc == nil || up == nil {
		return
	}
	ctx, cancel := context.WithTimeout(context.Background(), 3*time.Second)
	defer cancel()
	st, err := s.studentSvc.GetByStudentID(ctx, studentID)
	if err != nil || st == nil {
		return
	}
	userID := st.UserID
	for _, ar := range up.ActionRecommendations {
		for _, ref := range ar.ItemsRefs {
			if ref.RefType == "mission" {
				_ = s.missionSvc.EnsureUserMission(ctx, userID, ref.RefID)
			}
		}
	}
}

// ensureUpstreamStudent tries to mirror student core metrics into recommendation service.
func (s *RecommendationService) ensureUpstreamStudent(studentID string) {
	ctx, cancel := context.WithTimeout(context.Background(), 2*time.Second)
	defer cancel()

	st, err := s.studentSvc.GetByStudentID(ctx, studentID)
	if err != nil || st == nil {
		return
	}

	// Fetch latest questionnaire-derived scores (VARK raw counts, MSLQ & AMS averages) using existing queries.
	// We treat missing results gracefully by skipping the upsert.
	querier, ok := interface{}(s.studentSvc.q).(interface {
		GetLatestVarkResult(ctx context.Context, userID int64) (gen.GetLatestVarkResultRow, error)
		GetLatestLikertResultByType(ctx context.Context, arg gen.GetLatestLikertResultByTypeParams) (gen.StudentQuestionnaireLikertResult, error)
	})
	if !ok {
		return
	}

	varkRow, err := querier.GetLatestVarkResult(ctx, st.UserID)
	if err != nil {
		return
	}
	// Likert types: MSLQ & AMS
	mslqRow, err := querier.GetLatestLikertResultByType(ctx, gen.GetLatestLikertResultByTypeParams{UserID: st.UserID, Type: "MSLQ"})
	if err != nil {
		return
	}
	amsRow, err := querier.GetLatestLikertResultByType(ctx, gen.GetLatestLikertResultByTypeParams{UserID: st.UserID, Type: "AMS"})
	if err != nil {
		return
	}

	// Convert VARK raw counts to a 0-10 scale heuristically: find max of four, scale each proportionally.
	maxV := float64(varkRow.ScoreVisual)
	if fv := float64(varkRow.ScoreAuditory); fv > maxV {
		maxV = fv
	}
	if fv := float64(varkRow.ScoreReading); fv > maxV {
		maxV = fv
	}
	if fv := float64(varkRow.ScoreKinesthetic); fv > maxV {
		maxV = fv
	}
	if maxV == 0 {
		return
	}
	norm := func(v float64) float64 { return (v / maxV) * 10.0 }
	varkComposite := (norm(float64(varkRow.ScoreVisual)) + norm(float64(varkRow.ScoreAuditory)) + norm(float64(varkRow.ScoreReading)) + norm(float64(varkRow.ScoreKinesthetic))) / 4.0

	// MSLQ / AMS already stored as average (0-7 or 1-7 Likert). Scale to 0-10.
	scaleLikert := func(score float64) float64 { return (score / 7.0) * 10.0 }
	mslqScore := scaleLikert(mslqRow.TotalScore)
	amsScore := scaleLikert(amsRow.TotalScore)

	// Derive simple engagement estimate (blend of MSLQ & AMS & varkComposite) until richer metrics are available.
	engagement := (mslqScore*0.4 + amsScore*0.4 + varkComposite*0.2)
	engagementLevel := "medium"
	switch {
	case engagement >= 7.5:
		engagementLevel = "high"
	case engagement < 4.5:
		engagementLevel = "basic"
	}

	_ = s.gateway.EnsureStudent(studentID, varkComposite, mslqScore, amsScore, engagementLevel)
}
