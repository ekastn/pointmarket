package services

import (
	"context"
	"database/sql"
	"errors"
	"fmt"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
	"sort"
)

type DashboardService struct {
	q                       gen.Querier
	weeklyEvaluationService *WeeklyEvaluationService
}

func NewDashboardService(q gen.Querier, weeklyEvaluationService *WeeklyEvaluationService) *DashboardService {
	return &DashboardService{
		q:                       q,
		weeklyEvaluationService: weeklyEvaluationService,
	}
}

func (s *DashboardService) GetDashboardData(ctx context.Context, userID int64, userRole string) (dtos.DashboardDTO, error) {
	var dashboardData dtos.DashboardDTO
	var err error

	userModel, err := s.q.GetUserByID(ctx, userID)
	if err != nil {
		return dashboardData, err
	}

	userDTO := dtos.UserDTO{
		ID:       int(userModel.ID),
		Email:    userModel.Email,
		Username: userModel.Username,
		Name:     userModel.DisplayName,
		Role:     string(userModel.Role),
	}

	dashboardData.User = userDTO

	// Fetch data based on role
	switch userRole {
	case "admin":
		stats, err := s.q.GetAdminStatistic(ctx)
		if err != nil {
			return dashboardData, err
		}
		dashboardData.AdminStats = &dtos.AdminDashboardStatsDTO{
			TotalUsers:              stats.TotalUsers,
			TotalTeachers:           stats.TotalTeachers,
			TotalStudents:           stats.TotalStudents,
			TotalCourses:            stats.TotalCourses,
			TotalBadges:             stats.TotalBadges,
			TotalPointsTransactions: stats.TotalPointsTransactions,
			TotalProducts:           stats.TotalProducts,
			TotalMissions:           stats.TotalMissions,
		}
	case "guru":
		stats, err := s.q.GetTeacherStatistic(ctx, gen.GetTeacherStatisticParams{TeacherID: userID})
		if err != nil {
			return dashboardData, err
		}
		dashboardData.Teacherstats = &dtos.TeacherDashboardStatsDTO{
			TotalStudents:    stats.TotalStudents,
			TotalCourses:     stats.TotalCourses,
			TotalAssignments: stats.TotalAssignments,
			TotalQuizzes:     stats.TotalQuizzes,
		}
	case "siswa":
		stats, err := s.q.GetStudentStatistic(ctx, userID)
		if err != nil {
			return dashboardData, err
		}

		learningStyle, err := s.q.GetStudentLearningStyle(ctx, userID)
		if err != nil {
			if !errors.Is(err, sql.ErrNoRows) {
				return dashboardData, err
			}
		}

		const defaultScore = 0.0
		scoreVisual := defaultScore
		scoreAuditory := defaultScore
		scoreReading := defaultScore
		scoreKinesthetic := defaultScore

		if learningStyle.ScoreVisual != nil {
			scoreVisual = *learningStyle.ScoreVisual
		}

		if learningStyle.ScoreAuditory != nil {
			scoreAuditory = *learningStyle.ScoreAuditory
		}

		if learningStyle.ScoreReading != nil {
			scoreReading = *learningStyle.ScoreReading
		}

		if learningStyle.ScoreKinesthetic != nil {
			scoreKinesthetic = *learningStyle.ScoreReading
		}

		studentStatsDTO := &dtos.StudentDashboardStatsDTO{
			TotalPoints:          stats.TotalPoints,
			CompletedAssignments: stats.CompletedAssignments,
			MSLQScore:            stats.MslqScore,
			AMSScore:             stats.AmsScore,
			LearningStyle: dtos.StudentLearningStyle{
				Type:  string(learningStyle.Type),
				Label: learningStyle.Label,
				Scores: dtos.VARKScores{
					Visual:      scoreVisual,
					Auditory:    scoreAuditory,
					Reading:     scoreReading,
					Kinesthetic: scoreKinesthetic,
				},
			},
		}

		// --- Badge Progress Computation ---
		// Fetch all badges (assume small list). Use large limit.
		badges, _, err := func() ([]dtos.BadgeDTO, int64, error) {
			// Reuse existing badge retrieval through direct queries for simplicity
			list, err := s.q.GetBadges(ctx, gen.GetBadgesParams{Limit: 100, Offset: 0})
			if err != nil {
				return nil, 0, err
			}
			var out []dtos.BadgeDTO
			for _, b := range list {
				var dto dtos.BadgeDTO
				dto.FromBadgeModel(b)
				out = append(out, dto)
			}
			return out, int64(len(out)), nil
		}()
		if err != nil {
			fmt.Printf("warning: failed to load badges for dashboard: %v\n", err)
		} else {
			// Fetch user awarded badges to mark achieved and awarded time
			userBadges, err2 := s.q.GetUserBadgesByUserID(ctx, userID)
			awardedMap := map[int64]string{}
			if err2 == nil {
				for _, ub := range userBadges {
					awardedMap[ub.BadgeID] = ub.AwardedAt.Format("2006-01-02T15:04:05Z07:00")
				}
			}
			progress := make([]dtos.BadgeProgressDTO, 0, len(badges))
			for _, b := range badges {
				if b.PointsMin == nil { // skip badges without points_min criteria for this simple progress view
					continue
				}
				required := *b.PointsMin
				current := stats.TotalPoints
				percent := 0.0
				if required > 0 {
					percent = float64(current) / float64(required) * 100.0
					if percent > 100 {
						percent = 100
					}
				}
				achieved := current >= int64(required)
				var awardedAt *string
				if ts, ok := awardedMap[b.ID]; ok {
					awardedAt = &ts
					achieved = true
				}
				remaining := int64(required) - current
				if remaining < 0 {
					remaining = 0
				}
				progress = append(progress, dtos.BadgeProgressDTO{
					ID:              b.ID,
					Title:           b.Title,
					Description:     b.Description,
					PointsRequired:  required,
					CurrentPoints:   current,
					Percent:         percent,
					Achieved:        achieved,
					RemainingPoints: remaining,
					AwardedAt:       awardedAt,
				})
			}
			// Sort by required points ascending
			sort.Slice(progress, func(i, j int) bool { return progress[i].PointsRequired < progress[j].PointsRequired })
			studentStatsDTO.BadgesProgress = progress
		}

		// Fetch current week's evaluations
		currentWeekEvals, err := s.weeklyEvaluationService.GetCurrentWeekEvaluationsByStudentID(ctx, userID)
		if err != nil {
			fmt.Printf("Warning: Failed to get current week evaluations for dashboard: %v\n", err)
			// Decide how to handle error: return error, or proceed with empty slice
			studentStatsDTO.WeeklyEvaluations = []dtos.WeeklyEvaluationDetailDTO{}
		} else {
			studentStatsDTO.WeeklyEvaluations = currentWeekEvals
		}

		dashboardData.StudentStats = studentStatsDTO
	}

	return dashboardData, nil
}
