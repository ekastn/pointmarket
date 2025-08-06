package dtos

import (
	"pointmarket/backend/internal/models"
	"time"
)

// ComprehensiveDashboardDTO holds all data required for the dashboard.
type ComprehensiveDashboardDTO struct {
	UserProfile        UserDTO                       `json:"user_profile"`
	NLPStats           NLPStatsResponse              `json:"nlp_stats"`
	LatestVARKResult   VARKResultResponse            `json:"latest_vark_result"`
	AdminCounts        AdminDashboardCountsDTO       `json:"admin_counts"`
	StudentStats       StudentDashboardStatsDTO      `json:"student_stats"`
	TeacherCounts      TeacherDashboardCountsDTO     `json:"teacher_counts"`
	QuestionnaireStats []QuestionnaireStatsDTO       `json:"questionnaire_stats"` // Changed to slice
	RecentActivities   []RecentActivityDTO           `json:"recent_activities"`
	AssignmentStats    AssignmentStatsDTO            `json:"assignment_stats"`
	WeeklyProgress     []WeeklyEvaluationProgressDTO `json:"weekly_progress"`
}

// AdminDashboardCountsDTO holds counts for the admin dashboard.
type AdminDashboardCountsDTO struct {
	TotalUsers       int `json:"total_users"`
	TotalAssignments int `json:"total_assignments"`
	TotalMaterials   int `json:"total_materials"`
}

// FromAdminDashboardCounts converts a models.AdminDashboardCounts to an AdminDashboardCountsDTO.
func (dto *AdminDashboardCountsDTO) FromAdminDashboardCounts(m *models.AdminDashboardCounts) {
	if m == nil {
		return
	}
	dto.TotalUsers = m.TotalUsers
	dto.TotalAssignments = m.TotalAssignments
	dto.TotalMaterials = m.TotalMaterials
}

// StudentDashboardStatsDTO holds aggregated statistics for a student's dashboard.
type StudentDashboardStatsDTO struct {
	TotalPoints            float64  `json:"total_points"`
	CompletedAssignments   int      `json:"completed_assignments"`
	MSLQScore              *float64 `json:"mslq_score"`
	AMSScore               *float64 `json:"ams_score"`
	VARKDominantStyle      *string  `json:"vark_dominant_style"`
	VARKLearningPreference *string  `json:"vark_learning_preference"`
}

// FromStudentDashboardStats converts a models.StudentDashboardStats to a StudentDashboardStatsDTO.
func (dto *StudentDashboardStatsDTO) FromStudentDashboardStats(m *models.StudentDashboardStats) {
	if m == nil {
		return
	}
	dto.TotalPoints = m.TotalPoints
	dto.CompletedAssignments = m.CompletedAssignments
	dto.MSLQScore = m.MSLQScore
	dto.AMSScore = m.AMSScore
	dto.VARKDominantStyle = m.VARKDominantStyle
	dto.VARKLearningPreference = m.VARKLearningPreference
}

// TeacherDashboardCountsDTO holds counts for the teacher dashboard.
type TeacherDashboardCountsDTO struct {
	MyAssignments int `json:"my_assignments"`
	MyMaterials   int `json:"my_materials"`
	TotalStudents int `json:"total_students"`
}

// FromTeacherDashboardCounts converts a models.TeacherDashboardCounts to a TeacherDashboardCountsDTO.
func (dto *TeacherDashboardCountsDTO) FromTeacherDashboardCounts(m *models.TeacherDashboardCounts) {
	if m == nil {
		return
	}
	dto.MyAssignments = m.MyAssignments
	dto.MyMaterials = m.MyMaterials
	dto.TotalStudents = m.TotalStudents
}

// RecentActivityDTO represents a recent activity entry.
type RecentActivityDTO struct {
	Action      string    `json:"action"`
	Description *string   `json:"description"`
	CreatedAt   time.Time `json:"created_at"` // Changed to time.Time
}

// FromActivityLog converts a models.ActivityLog to a RecentActivityDTO.
func (dto *RecentActivityDTO) FromActivityLog(m *models.ActivityLog) {
	if m == nil {
		return
	}
	dto.Action = m.Action
	dto.Description = m.Description
	dto.CreatedAt = m.CreatedAt
}

// AssignmentStatsDTO holds assignment-related statistics.
type AssignmentStatsDTO struct {
	TotalAssignments int     `json:"total_assignments"`
	AvgScore         float64 `json:"avg_score"`
	BestScore        float64 `json:"best_score"`
	LowestScore      float64 `json:"lowest_score"`
	HighScores       int     `json:"high_scores"`
	LateSubmissions  int     `json:"late_submissions"`
}

// FromAssignmentStats converts a models.AssignmentStats to an AssignmentStatsDTO.
func (dto *AssignmentStatsDTO) FromAssignmentStats(m *models.AssignmentStats) {
	if m == nil {
		return
	}
	dto.TotalAssignments = m.TotalAssignments
	dto.AvgScore = m.AvgScore
	dto.BestScore = m.BestScore
	dto.LowestScore = m.LowestScore
	dto.HighScores = m.HighScores
	dto.LateSubmissions = m.LateSubmissions
}

// QuestionnaireStatsDTO holds statistics for questionnaires.
type QuestionnaireStatsDTO struct {
	Type           string     `json:"type"`
	Name           string     `json:"name"`
	TotalCompleted int        `json:"total_completed"`
	AverageScore   *float64   `json:"average_score"`
	BestScore      *float64   `json:"best_score"`
	LowestScore    *float64   `json:"lowest_score"`
	LastCompleted  *time.Time `json:"last_completed"`
}

// FromQuestionnaireStat converts a models.QuestionnaireStat to a QuestionnaireStatsDTO.
func (dto *QuestionnaireStatsDTO) FromQuestionnaireStat(m *models.QuestionnaireStat) {
	if m == nil {
		return
	}
	dto.Type = m.Type
	dto.Name = m.Name
	dto.TotalCompleted = m.TotalCompleted
	dto.AverageScore = m.AverageScore
	dto.BestScore = m.BestScore
	dto.LowestScore = m.LowestScore
	dto.LastCompleted = m.LastCompleted
}
