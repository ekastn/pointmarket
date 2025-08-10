package services

import (
	"context"
	"pointmarket/backend/internal/dtos"
	// Removed unused import: "pointmarket/backend/internal/models"
)

type DashboardService struct {
	userService             UserService
	nlpService              NLPService
	varkService             VARKService
	questionnaireService    QuestionnaireService
	weeklyEvaluationService WeeklyEvaluationService
}

func NewDashboardService(
	userService UserService,
	nlpService NLPService,
	varkService VARKService,
	questionnaireService QuestionnaireService,
	weeklyEvaluationService WeeklyEvaluationService,
) *DashboardService {
	return &DashboardService{
		userService:             userService,
		nlpService:              nlpService,
		varkService:             varkService,
		questionnaireService:    questionnaireService,
		weeklyEvaluationService: weeklyEvaluationService,
	}
}

func (s *DashboardService) GetComprehensiveDashboardData(ctx context.Context, userID uint, userRole string) (dtos.ComprehensiveDashboardDTO, error) {
	var dashboardData dtos.ComprehensiveDashboardDTO
	var err error

	// Fetch User Profile
	userModel, err := s.userService.GetUserByID(ctx, int64(userID))
	if err != nil {
		return dashboardData, err
	}
	userProfileDTO := dtos.UserDTO{
		ID:       int(userModel.ID),
		Email:    userModel.Email,
		Username: userModel.Username,
		Role:     string(userModel.Role),
	}
	dashboardData.UserProfile = userProfileDTO

	// Fetch NLP Stats
	nlpProgressModel, err := s.nlpService.GetNLPStats(userID)
	if err != nil {
		return dashboardData, err
	}
	var nlpStatsDTO dtos.NLPStatsResponse
	if nlpProgressModel != nil {
		nlpStatsDTO.FromNLPStats(*nlpProgressModel)
	}
	dashboardData.NLPStats = nlpStatsDTO

	// Fetch Latest VARK Result
	varkResultModel, err := s.varkService.GetLatestVARKResult(userID)
	if err != nil {
		return dashboardData, err
	}
	var varkResultDTO dtos.VARKResultResponse
	if varkResultModel != nil {
		varkResultDTO.FromVARKResult(*varkResultModel)
	}
	dashboardData.LatestVARKResult = varkResultDTO

	// Fetch data based on role
	switch userRole {
	case "admin":
		adminCountsModel, err := s.userService.GetAdminDashboardCounts()
		if err != nil {
			return dashboardData, err
		}
		var adminCountsDTO dtos.AdminDashboardCountsDTO
		if adminCountsModel != nil {
			adminCountsDTO.FromAdminDashboardCounts(adminCountsModel)
		}
		dashboardData.AdminCounts = adminCountsDTO
	case "guru": // Teacher
		teacherCountsModel, err := s.userService.GetTeacherDashboardCounts(userID)
		if err != nil {
			return dashboardData, err
		}
		var teacherCountsDTO dtos.TeacherDashboardCountsDTO
		if teacherCountsModel != nil {
			teacherCountsDTO.FromTeacherDashboardCounts(teacherCountsModel)
		}
		dashboardData.TeacherCounts = teacherCountsDTO
	case "siswa": // Student
		studentStatsModel, err := s.userService.GetStudentDashboardStats(userID)
		if err != nil {
			return dashboardData, err
		}
		var studentStatsDTO dtos.StudentDashboardStatsDTO
		if studentStatsModel != nil {
			studentStatsDTO.FromStudentDashboardStats(studentStatsModel)
		}
		dashboardData.StudentStats = studentStatsDTO

		assignmentStatsModel, err := s.userService.GetAssignmentStatsByStudentID(userID)
		if err != nil {
			return dashboardData, err
		}
		var assignmentStatsDTO dtos.AssignmentStatsDTO
		if assignmentStatsModel != nil {
			assignmentStatsDTO.FromAssignmentStats(assignmentStatsModel)
		}
		dashboardData.AssignmentStats = assignmentStatsDTO

		recentActivitiesModels, err := s.userService.GetRecentActivityByUserID(userID, 10)
		if err != nil {
			return dashboardData, err
		}
		var recentActivitiesDTOs []dtos.RecentActivityDTO
		for _, activityModel := range recentActivitiesModels {
			var activityDTO dtos.RecentActivityDTO
			activityDTO.FromActivityLog(&activityModel)
			recentActivitiesDTOs = append(recentActivitiesDTOs, activityDTO)
		}
		dashboardData.RecentActivities = recentActivitiesDTOs

		weeklyProgressModels, err := s.weeklyEvaluationService.GetWeeklyEvaluationProgressByStudentID(int(userID), 8)
		if err != nil {
			return dashboardData, err
		}
		var weeklyProgressDTOs []dtos.WeeklyEvaluationProgressDTO
		for _, progressModel := range weeklyProgressModels {
			var progressDTO dtos.WeeklyEvaluationProgressDTO
			progressDTO.Year = progressModel.Year
			progressDTO.WeekNumber = progressModel.WeekNumber
			progressDTO.QuestionnaireName = progressModel.QuestionnaireName
			progressDTO.MslqScore = progressModel.MSLQScore
			progressDTO.AmsScore = progressModel.AMSScore
			progressDTO.Status = progressModel.Status
			if progressModel.DueDate != nil {
				progressDTO.DueDate = *progressModel.DueDate
			}
			progressDTO.CompletedAt = progressModel.CompletedAt
			weeklyProgressDTOs = append(weeklyProgressDTOs, progressDTO)
		}
		dashboardData.WeeklyProgress = weeklyProgressDTOs
	}

	// Fetch Questionnaire Stats (might be common for all roles or specific)
	questionnaireStatsModels, err := s.questionnaireService.GetQuestionnaireStatsByStudentID(userID)
	if err != nil {
		return dashboardData, err
	}
	var questionnaireStatsDTOs []dtos.QuestionnaireStatsDTO
	for _, statModel := range questionnaireStatsModels {
		var statDTO dtos.QuestionnaireStatsDTO
		statDTO.FromQuestionnaireStat(&statModel)
		questionnaireStatsDTOs = append(questionnaireStatsDTOs, statDTO)
	}
	dashboardData.QuestionnaireStats = questionnaireStatsDTOs

	return dashboardData, nil
}
