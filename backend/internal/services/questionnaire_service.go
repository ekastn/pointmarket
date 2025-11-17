package services

import (
	"context"
	"database/sql"
	"encoding/json"
	"errors"
	"fmt"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
	"pointmarket/backend/internal/utils"
	"sort"
	"strconv"
	"strings"
)

// QuestionnaireService provides business logic for questionnaires
type QuestionnaireService struct {
	db *sql.DB
	q  *gen.Queries
}

// NewQuestionnaireService creates a new instance of QuestionnaireService
func NewQuestionnaireService(db *sql.DB, q *gen.Queries) *QuestionnaireService {
	return &QuestionnaireService{db: db, q: q}
}

// GetQuestionnaires retrieves all questionnaires
func (s *QuestionnaireService) GetQuestionnaires(ctx context.Context) ([]gen.Questionnaire, error) {
	return s.q.GetQuestionnaires(ctx)
}

// GetActiveQuestionnaires retrieves all active questionnaires
func (s *QuestionnaireService) GetActiveQuestionnaires(ctx context.Context) ([]gen.Questionnaire, error) {
	return s.q.GetActiveQuestionnaires(ctx)
}

// GetQuestionnaireByID retrieves a questionnaire by ID
func (s *QuestionnaireService) GetQuestionnaireByID(ctx context.Context, id int32) (gen.Questionnaire, error) {
	row, err := s.q.GetQuestionnaireByID(ctx, id)
	if err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return gen.Questionnaire{}, nil
		}
		return gen.Questionnaire{}, err
	}
	return row, nil
}

// GetQuestionsByQuestionnaireID retrieves all questions for a given questionnaire ID
func (s *QuestionnaireService) GetQuestionsByQuestionnaireID(ctx context.Context, questionnaireID int32) ([]gen.QuestionnaireQuestion, error) {
	return s.q.GetQuestionsByQuestionnaireID(ctx, questionnaireID)
}

// GetVarkOptionsByQuestionnaireID retrieves all VARK options for a given questionnaire ID
func (s *QuestionnaireService) GetVarkOptionsByQuestionnaireID(ctx context.Context, questionnaireID int32) ([]gen.QuestionnaireVarkOption, error) {
	return s.q.GetVarkOptionsByQuestionnaireID(ctx, questionnaireID)
}

// SubmitLikert saves a student's Likert answers
func (s *QuestionnaireService) SubmitLikert(ctx context.Context, studentID int64, questionnaireID int32, weeklyEvaluationID *int64, answers map[string]string) (*float64, error) {
	qMeta, err := s.GetQuestionnaireByID(ctx, questionnaireID)
	if err != nil {
		return nil, err
	}
	if qMeta.Type != "MSLQ" && qMeta.Type != "AMS" {
		return nil, fmt.Errorf("questionnaire type %s not likert", qMeta.Type)
	}

	questions, err := s.GetQuestionsByQuestionnaireID(ctx, questionnaireID)
	if err != nil {
		return nil, err
	}
	if len(questions) == 0 {
		return nil, errors.New("no questions defined")
	}
	if len(answers) != len(questions) {
		return nil, fmt.Errorf("answer length %d mismatch questions %d", len(answers), len(questions))
	}

	totalScore := 0.0
	subscaleScores := make(map[string]float64)
	subscaleCounts := make(map[string]int)

	for _, q := range questions {
		answerStr, ok := answers[fmt.Sprintf("%d", q.ID)]
		if !ok {
			continue
		}
		answer, err := strconv.ParseFloat(answerStr, 64)
		if err != nil {
			return nil, fmt.Errorf("invalid answer format for question %d: %w", q.ID, err)
		}

		// reverse scoring
		// if q.ReverseScored {
		// 	answer = 8 - answer
		// }

		totalScore += answer

		if q.Subscale.Valid {
			subscaleScores[q.Subscale.String] += answer
			subscaleCounts[q.Subscale.String]++
		}
	}

	avgTotalScore := totalScore / float64(len(questions))

	for subscale, sum := range subscaleScores {
		if subscaleCounts[subscale] > 0 {
			subscaleScores[subscale] = sum / float64(subscaleCounts[subscale])
		}
	}

	subscaleScoresJSON, err := json.Marshal(subscaleScores)
	if err != nil {
		return nil, fmt.Errorf("failed to marshal subscale scores: %w", err)
	}

	answersJSON, err := json.Marshal(answers)
	if err != nil {
		return nil, fmt.Errorf("failed to marshal answers: %w", err)
	}

	data := gen.CreateLikertResultParams{
		UserID:          studentID,
		QuestionnaireID: questionnaireID,
		Answers:         answersJSON,
		TotalScore:      avgTotalScore,
		SubscaleScores:  subscaleScoresJSON,
	}

	if weeklyEvaluationID != nil {
		data.WeeklyEvaluationID = sql.NullInt64{Int64: *weeklyEvaluationID, Valid: true}
	}

	err = s.q.CreateLikertResult(ctx, data)
	if err != nil {
		return nil, err
	}

	// If the submission is for a weekly evaluation, update its status
	if weeklyEvaluationID != nil {
		err = s.q.UpdateWeeklyEvaluationStatus(ctx, gen.UpdateWeeklyEvaluationStatusParams{
			ID:     *weeklyEvaluationID,
			UserID: studentID,
		})
		if err != nil {
			// Log the error but don't fail the whole transaction,
			// as the main result was already saved.
			// Or handle this in a transaction. For now, just log.
			fmt.Printf("Warning: failed to update weekly evaluation status for id %d: %v\n", *weeklyEvaluationID, err)
		}
	}

	return &avgTotalScore, nil
}

// SubmitVARK calculates and saves a student's VARK assessment result
func (s *QuestionnaireService) SubmitVARK(
	ctx context.Context,
	studentID int64,
	questionnaireID int32,
	answers map[string]string,
) (dtos.VARKScores, error) {
	options, err := s.q.GetVarkOptionsByQuestionnaireID(ctx, questionnaireID)
	if err != nil {
		return dtos.VARKScores{}, err
	}

	// Create a map for quick lookup: questionID -> optionLetter -> learningStyle
	optionMap := make(map[int32]map[string]string)
	for _, opt := range options {
		if _, ok := optionMap[opt.QuestionID]; !ok {
			optionMap[opt.QuestionID] = make(map[string]string)
		}
		optionMap[opt.QuestionID][opt.OptionLetter] = string(opt.LearningStyle)
	}

	scores := map[string]int32{
		"Visual":      0,
		"Auditory":    0,
		"Reading":     0,
		"Kinesthetic": 0,
	}

	for questionIDStr, answerLetter := range answers {
		questionID := 0
		fmt.Sscanf(questionIDStr, "%d", &questionID) // Convert string key to int

		if qOptions, ok := optionMap[int32(questionID)]; ok {
			if learningStyle, ok := qOptions[answerLetter]; ok {
				scores[learningStyle]++
			}
		}
	}

	answersJSON, err := json.Marshal(answers)
	if err != nil {
		return dtos.VARKScores{}, err
	}

	prefType, prefLabel := utils.DetermineLearningPreferenceType(dtos.VARKScores{
		Visual:      float64(scores["Visual"]),
		Auditory:    float64(scores["Auditory"]),
		Reading:     float64(scores["Reading"]),
		Kinesthetic: float64(scores["Kinesthetic"]),
	})

	varkResult := gen.CreateVarkResultParams{
		UserID:           studentID,
		QuestionnaireID:  questionnaireID,
		VarkType:         gen.StudentQuestionnaireVarkResultsVarkType(prefType),
		VarkLabel:        prefLabel,
		ScoreVisual:      scores["Visual"],
		ScoreAuditory:    scores["Auditory"],
		ScoreReading:     scores["Reading"],
		ScoreKinesthetic: scores["Kinesthetic"],
		Answers:          answersJSON,
	}

	if err := s.q.CreateVarkResult(ctx, varkResult); err != nil {
		return dtos.VARKScores{}, err
	}

	return dtos.VARKScores{
		Visual:      float64(scores["Visual"]),
		Auditory:    float64(scores["Auditory"]),
		Reading:     float64(scores["Reading"]),
		Kinesthetic: float64(scores["Kinesthetic"]),
	}, nil
}

func (s *QuestionnaireService) GetLatestLikertByType(ctx context.Context, studentID int64, qType string) (gen.StudentQuestionnaireLikertResult, error) {
	row, err := s.q.GetLatestLikertResultByType(
		ctx,
		gen.GetLatestLikertResultByTypeParams{
			UserID: studentID,
			Type:   gen.QuestionnairesType(qType),
		},
	)
	if err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return gen.StudentQuestionnaireLikertResult{}, nil
		}
		return gen.StudentQuestionnaireLikertResult{}, err
	}
	return row, nil
}

func (s *QuestionnaireService) GetLatestVark(ctx context.Context, studentID int64) (gen.GetLatestVarkResultRow, error) {
	row, err := s.q.GetLatestVarkResult(ctx, studentID)
	if err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return gen.GetLatestVarkResultRow{}, nil
		}
		return gen.GetLatestVarkResultRow{}, err
	}
	return row, nil
}

// GetQuestionnaireStats aggregates per-type stats for Likert questionnaires and a compact VARK summary.
func (s *QuestionnaireService) GetQuestionnaireStats(ctx context.Context, userID int64) (dtos.StudentQuestionnaireStatsDTO, error) {
	var res dtos.StudentQuestionnaireStatsDTO

	// Likert type stats (MSLQ, AMS)
	typeRows, err := s.q.GetLikertTypeStatsByStudent(ctx, userID)
	if err != nil {
		return res, err
	}
	likertStats := make([]dtos.QuestionnaireTypeStatDTO, 0, len(typeRows))
	for _, r := range typeRows {
		var lastCompleted *string
		if !r.LastCompleted.IsZero() {
			s := r.LastCompleted.Format("2006-01-02T15:04:05Z07:00")
			lastCompleted = &s
		}
		likertStats = append(likertStats, dtos.QuestionnaireTypeStatDTO{
			Type:           string(r.Type),
			TotalCompleted: r.TotalCompleted,
			AverageScore:   r.AverageScore,
			BestScore:      r.BestScore,
			LowestScore:    r.LowestScore,
			LastCompleted:  lastCompleted,
		})
	}
	res.Likert = likertStats

	// VARK summary
	varkCount, err := s.q.CountVarkResultsByStudent(ctx, userID)
	if err != nil {
		return res, err
	}
	varkSummary := dtos.VarkStatsDTO{TotalCompleted: varkCount}
	if varkCount > 0 {
		if last, err := s.q.GetLatestVarkResult(ctx, userID); err == nil {
			if last.CreatedAt.Valid {
				s := last.CreatedAt.Time.Format("2006-01-02T15:04:05Z07:00")
				varkSummary.LastCompleted = &s
			}
		}
	}
	res.Vark = varkSummary
	return res, nil
}

// GetQuestionnaireHistory returns a paginated list of Likert submissions (optionally filtered by type).
func (s *QuestionnaireService) GetQuestionnaireHistory(ctx context.Context, userID int64, typ string, limit, offset int32) ([]dtos.QuestionnaireHistoryItemDTO, int64, error) {
	t := strings.ToUpper(strings.TrimSpace(typ))
	switch t {
	case string(gen.QuestionnairesTypeMSLQ), string(gen.QuestionnairesTypeAMS):
		rows, err := s.q.GetLikertHistoryByStudent(ctx, gen.GetLikertHistoryByStudentParams{
			UserID:            userID,
			TypeFilterIsEmpty: 0,
			TypeFilter:        gen.QuestionnairesType(t),
			Limit:             limit,
			Offset:            offset,
		})
		if err != nil {
			return nil, 0, err
		}
		total, err := s.q.CountLikertHistoryByStudent(ctx, gen.CountLikertHistoryByStudentParams{
			UserID:            userID,
			TypeFilterIsEmpty: 0,
			TypeFilter:        gen.QuestionnairesType(t),
		})
		if err != nil {
			return nil, 0, err
		}
		return mapLikertHistory(rows), total, nil
	case string(gen.QuestionnairesTypeVARK):
		rows, err := s.q.GetVarkHistoryByStudent(ctx, gen.GetVarkHistoryByStudentParams{UserID: userID, Limit: limit, Offset: offset})
		if err != nil {
			return nil, 0, err
		}
		total, err := s.q.CountVarkHistoryByStudent(ctx, userID)
		if err != nil {
			return nil, 0, err
		}
		return mapVarkHistory(rows), total, nil
	default:
		fetchN := limit + offset
		if fetchN < 1 {
			fetchN = limit
		}
		lRows, err := s.q.GetLikertHistoryByStudent(ctx, gen.GetLikertHistoryByStudentParams{
			UserID:            userID,
			TypeFilterIsEmpty: 1,
			TypeFilter:        gen.QuestionnairesType(""),
			Limit:             fetchN,
			Offset:            0,
		})
		if err != nil {
			return nil, 0, err
		}
		vRows, err := s.q.GetVarkHistoryByStudent(ctx, gen.GetVarkHistoryByStudentParams{UserID: userID, Limit: fetchN, Offset: 0})
		if err != nil {
			return nil, 0, err
		}
		likertItems := mapLikertHistory(lRows)
		varkItems := mapVarkHistory(vRows)
		merged := mergeHistory(likertItems, varkItems)
		lTotal, err := s.q.CountLikertHistoryByStudent(ctx, gen.CountLikertHistoryByStudentParams{UserID: userID, TypeFilterIsEmpty: 1, TypeFilter: gen.QuestionnairesType("")})
		if err != nil {
			return nil, 0, err
		}
		vTotal, err := s.q.CountVarkHistoryByStudent(ctx, userID)
		if err != nil {
			return nil, 0, err
		}
		total := lTotal + vTotal
		start := int(offset)
		end := start + int(limit)
		if start > len(merged) {
			return []dtos.QuestionnaireHistoryItemDTO{}, total, nil
		}
		if end > len(merged) {
			end = len(merged)
		}
		return merged[start:end], total, nil
	}
}

func mapLikertHistory(rows []gen.GetLikertHistoryByStudentRow) []dtos.QuestionnaireHistoryItemDTO {
	items := make([]dtos.QuestionnaireHistoryItemDTO, 0, len(rows))
	for _, r := range rows {
		var completed string
		var weekNum, yearNum int
		if r.CreatedAt.Valid {
			t := r.CreatedAt.Time
			completed = t.Format("2006-01-02T15:04:05Z07:00")
			y, w := t.ISOWeek()
			weekNum = int(w)
			yearNum = int(y)
		}
		var desc *string
		if r.QuestionnaireDescription.Valid {
			desc = &r.QuestionnaireDescription.String
		}
		ts := r.TotalScore
		var weid *int64
		if r.WeeklyEvaluationID.Valid {
			v := r.WeeklyEvaluationID.Int64
			weid = &v
		}
		item := dtos.QuestionnaireHistoryItemDTO{
			QuestionnaireID:          r.QuestionnaireID,
			QuestionnaireType:        string(r.QuestionnaireType),
			QuestionnaireName:        r.QuestionnaireName,
			QuestionnaireDescription: desc,
			CompletedAt:              completed,
			TotalScore:               &ts,
			WeeklyEvaluationID:       weid,
		}
		if completed != "" {
			item.WeekNumber = &weekNum
			item.Year = &yearNum
		}
		items = append(items, item)
	}
	return items
}

func mapVarkHistory(rows []gen.GetVarkHistoryByStudentRow) []dtos.QuestionnaireHistoryItemDTO {
	items := make([]dtos.QuestionnaireHistoryItemDTO, 0, len(rows))
	for _, r := range rows {
		var completed string
		var weekNum, yearNum int
		if r.CreatedAt.Valid {
			t := r.CreatedAt.Time
			completed = t.Format("2006-01-02T15:04:05Z07:00")
			y, w := t.ISOWeek()
			weekNum = int(w)
			yearNum = int(y)
		}
		var desc *string
		if r.QuestionnaireDescription.Valid {
			desc = &r.QuestionnaireDescription.String
		}
		scores := dtos.VARKScores{
			Visual:      float64(r.ScoreVisual),
			Auditory:    float64(r.ScoreAuditory),
			Reading:     float64(r.ScoreReading),
			Kinesthetic: float64(r.ScoreKinesthetic),
		}
		stype := string(r.VarkType)
		slabel := r.VarkLabel
		item := dtos.QuestionnaireHistoryItemDTO{
			QuestionnaireID:          r.QuestionnaireID,
			QuestionnaireType:        string(r.QuestionnaireType),
			QuestionnaireName:        r.QuestionnaireName,
			QuestionnaireDescription: desc,
			CompletedAt:              completed,
			VarkStyleType:            &stype,
			VarkStyleLabel:           &slabel,
			VarkScores:               &scores,
		}
		if completed != "" {
			item.WeekNumber = &weekNum
			item.Year = &yearNum
		}
		items = append(items, item)
	}
	return items
}

func mergeHistory(a, b []dtos.QuestionnaireHistoryItemDTO) []dtos.QuestionnaireHistoryItemDTO {
	all := append([]dtos.QuestionnaireHistoryItemDTO{}, a...)
	all = append(all, b...)
	sort.SliceStable(all, func(i, j int) bool { return all[i].CompletedAt > all[j].CompletedAt })
	return all
}

func boolToTinyInt(b bool) int32 {
	if b {
		return 1
	}
	return 0
}

func (s *QuestionnaireService) GetLikertStats(ctx context.Context, studentID int64) ([]gen.GetLikertStatsByStudentRow, error) {
	return s.q.GetLikertStatsByStudent(ctx, studentID)
}

// GetQuestionnaireByType retrieves a questionnaire by its type
func (s *QuestionnaireService) GetQuestionnaireByType(ctx context.Context, qType gen.QuestionnairesType) (gen.Questionnaire, error) {
	row, err := s.q.GetQuestionnaireByType(ctx, qType)
	if err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return gen.Questionnaire{}, fmt.Errorf("questionnaire of type %s not found", qType)
		}
		return gen.Questionnaire{}, fmt.Errorf("failed to get questionnaire by type %s: %w", qType, err)
	}
	return row, nil
}

func (s *QuestionnaireService) CreateQuestionnaire(ctx context.Context, arg dtos.AdminQuestionnaireDTO) (gen.Questionnaire, error) {
	tx, err := s.db.BeginTx(ctx, nil)
	if err != nil {
		return gen.Questionnaire{}, err
	}
	defer tx.Rollback()

	qtx := s.q.WithTx(tx)

	questionnaireResult, err := qtx.CreateQuestionnaire(ctx, gen.CreateQuestionnaireParams{
		Type:           gen.QuestionnairesType(arg.Type),
		Name:           arg.Name,
		Description:    sql.NullString{String: arg.Description, Valid: true},
		TotalQuestions: int32(len(arg.Questions)),
		Status:         gen.NullQuestionnairesStatus{QuestionnairesStatus: gen.QuestionnairesStatus(arg.Status), Valid: true},
	})
	if err != nil {
		return gen.Questionnaire{}, err
	}
	questionnaireId, err := questionnaireResult.LastInsertId()
	if err != nil {
		return gen.Questionnaire{}, err
	}

	for _, qDto := range arg.Questions {
		questionResult, err := qtx.CreateQuestion(ctx, gen.CreateQuestionParams{
			QuestionnaireID: int32(questionnaireId),
			QuestionNumber:  qDto.QuestionNumber,
			QuestionText:    qDto.QuestionText,
			Subscale:        sql.NullString{String: *qDto.Subscale, Valid: qDto.Subscale != nil},
		})
		if err != nil {
			return gen.Questionnaire{}, err
		}

		if arg.Type == "VARK" {
			questionId, err := questionResult.LastInsertId()
			if err != nil {
				return gen.Questionnaire{}, err
			}
			for _, oDto := range qDto.Options {
				_, err := qtx.CreateVarkOption(ctx, gen.CreateVarkOptionParams{
					QuestionID:    int32(questionId),
					OptionText:    oDto.OptionText,
					OptionLetter:  oDto.OptionLetter,
					LearningStyle: gen.QuestionnaireVarkOptionsLearningStyle(oDto.LearningStyle),
				})
				if err != nil {
					return gen.Questionnaire{}, err
				}
			}
		}
	}

	if err := tx.Commit(); err != nil {
		return gen.Questionnaire{}, err
	}

	return s.q.GetQuestionnaireByID(ctx, int32(questionnaireId))
}

func (s *QuestionnaireService) UpdateQuestionnaire(ctx context.Context, id int32, arg dtos.AdminQuestionnaireDTO) (gen.Questionnaire, error) {
	tx, err := s.db.BeginTx(ctx, nil)
	if err != nil {
		return gen.Questionnaire{}, err
	}
	defer tx.Rollback()

	qtx := s.q.WithTx(tx)

	err = qtx.UpdateQuestionnaire(ctx, gen.UpdateQuestionnaireParams{
		ID:             id,
		Name:           arg.Name,
		Description:    sql.NullString{String: arg.Description, Valid: true},
		TotalQuestions: int32(len(arg.Questions)),
		Status:         gen.NullQuestionnairesStatus{QuestionnairesStatus: gen.QuestionnairesStatus(arg.Status), Valid: true},
	})
	if err != nil {
		return gen.Questionnaire{}, err
	}

	// Delete existing questions and options
	questions, err := qtx.GetQuestionsByQuestionnaireID(ctx, id)
	if err != nil {
		return gen.Questionnaire{}, err
	}
	for _, q := range questions {
		if arg.Type == "VARK" {
			options, err := qtx.GetVarkOptionsByQuestionnaireID(ctx, q.QuestionnaireID)
			if err != nil {
				return gen.Questionnaire{}, err
			}
			for _, o := range options {
				err := qtx.DeleteVarkOption(ctx, o.ID)
				if err != nil {
					return gen.Questionnaire{}, err
				}
			}
		}
		err := qtx.DeleteQuestion(ctx, q.ID)
		if err != nil {
			return gen.Questionnaire{}, err
		}
	}

	for _, qDto := range arg.Questions {
		questionResult, err := qtx.CreateQuestion(ctx, gen.CreateQuestionParams{
			QuestionnaireID: id,
			QuestionNumber:  qDto.QuestionNumber,
			QuestionText:    qDto.QuestionText,
			Subscale:        utils.NullString(qDto.Subscale),
		})
		if err != nil {
			return gen.Questionnaire{}, err
		}

		if arg.Type == "VARK" {
			questionId, err := questionResult.LastInsertId()
			if err != nil {
				return gen.Questionnaire{}, err
			}
			for _, oDto := range qDto.Options {
				_, err := qtx.CreateVarkOption(ctx, gen.CreateVarkOptionParams{
					QuestionID:    int32(questionId),
					OptionText:    oDto.OptionText,
					OptionLetter:  oDto.OptionLetter,
					LearningStyle: gen.QuestionnaireVarkOptionsLearningStyle(oDto.LearningStyle),
				})
				if err != nil {
					return gen.Questionnaire{}, err
				}
			}
		}
	}

	if err := tx.Commit(); err != nil {
		return gen.Questionnaire{}, err
	}

	return s.q.GetQuestionnaireByID(ctx, id)
}

func (s *QuestionnaireService) DeleteQuestionnaire(ctx context.Context, id int32) error {
	return s.q.DeleteQuestionnaire(ctx, id)
}
