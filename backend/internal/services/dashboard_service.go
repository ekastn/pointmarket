package services

import (
	"context"
	"database/sql"
	"errors"
	"fmt"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
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
