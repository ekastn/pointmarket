package services

import (
	"database/sql"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store"
	"time"
)

type UserService struct {
	userStore *store.UserStore
}

func NewUserService(userStore *store.UserStore) *UserService {
	return &UserService{userStore: userStore}
}

func (s *UserService) GetUserByID(id uint) (models.User, error) {
	user, err := s.userStore.GetUserByID(id)
	if err != nil {
		return models.User{}, err
	}
	return *user, nil
}

// UpdateUserProfile updates a user's profile information
func (s *UserService) UpdateUserProfile(userID uint, req dtos.UpdateProfileRequest) error {
	user, err := s.userStore.GetUserByID(userID)
	if err != nil {
		return err
	}
	if user == nil {
		return sql.ErrNoRows // User not found
	}

	user.Name = req.Name
	user.Email = req.Email
	user.Avatar = req.Avatar

	return s.userStore.UpdateUser(user)
}

// GetAllUsers retrieves all users
func (s *UserService) GetAllUsers() ([]models.User, error) {
	return s.userStore.GetAllUsers()
}

// UpdateUserRole updates a user's role
func (s *UserService) UpdateUserRole(userID uint, role string) error {
	return s.userStore.UpdateUserRole(int(userID), role)
}

// DeleteUser deletes a user (sets role to 'inactive')
func (s *UserService) DeleteUser(userID uint) error {
	return s.userStore.DeleteUser(int(userID))
}

// GetStudentDashboardStats retrieves aggregated statistics for a student's dashboard
func (s *UserService) GetStudentDashboardStats(studentID uint) (*models.StudentDashboardStats, error) {
	return s.userStore.GetStudentDashboardStats(int(studentID))
}

// GetAdminDashboardCounts retrieves counts for admin dashboard
func (s *UserService) GetAdminDashboardCounts() (*models.AdminDashboardCounts, error) {
	return s.userStore.GetAdminDashboardCounts()
}

// GetTeacherDashboardCounts retrieves counts for teacher dashboard
func (s *UserService) GetTeacherDashboardCounts(teacherID uint) (*models.TeacherDashboardCounts, error) {
	return s.userStore.GetTeacherDashboardCounts(int(teacherID))
}

// GetStudentEvaluationStatus retrieves the weekly evaluation status for all students
func (s *UserService) GetStudentEvaluationStatus() ([]models.StudentEvaluationStatus, error) {
	// Get current week and year
	_, week := time.Now().ISOWeek()
	year := time.Now().Year()
	return s.userStore.GetStudentEvaluationStatus(week, year)
}

// GetWeeklyEvaluationOverview retrieves aggregated weekly progress for teachers
func (s *UserService) GetWeeklyEvaluationOverview(weeks int) ([]models.WeeklyEvaluationOverview, error) {
	return s.userStore.GetWeeklyEvaluationOverview(weeks)
}

// GetAssignmentStatsByStudentID retrieves assignment statistics for a student
func (s *UserService) GetAssignmentStatsByStudentID(studentID uint) (*models.AssignmentStats, error) {
	return s.userStore.GetAssignmentStatsByStudentID(int(studentID))
}

// GetRecentActivityByUserID retrieves recent activity for a user
func (s *UserService) GetRecentActivityByUserID(userID uint, limit int) ([]models.ActivityLog, error) {
	return s.userStore.GetRecentActivityByUserID(int(userID), limit)
}

// GetWeeklyEvaluationProgressByStudentID retrieves weekly evaluation progress for a student
func (s *UserService) GetWeeklyEvaluationProgressByStudentID(studentID uint, weeks int) ([]models.WeeklyEvaluationProgress, error) {
	return s.userStore.GetWeeklyEvaluationProgressByStudentID(int(studentID), weeks)
}