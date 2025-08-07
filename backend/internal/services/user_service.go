package services

import (
	"database/sql"
	"fmt"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store"

	"golang.org/x/crypto/bcrypt"
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

// CreateUser creates a new user
func (s *UserService) CreateUser(req dtos.CreateUserRequest) (*models.User, error) {
	// Hash the password
	hashedPassword, err := bcrypt.GenerateFromPassword([]byte(req.Password), bcrypt.DefaultCost)
	if err != nil {
		return nil, fmt.Errorf("failed to hash password: %w", err)
	}

	user := &models.User{
		Username: req.Username,
		Password: string(hashedPassword),
		Name:     req.Username, // Assuming name is same as username for now
		Email:    req.Email,
		Role:     req.Role,
	}

	err = s.userStore.CreateUser(user)
	if err != nil {
		return nil, err
	}

	return user, nil
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

// SearchUsers retrieves users based on search term and role
func (s *UserService) SearchUsers(search, role string) ([]models.User, error) {
	return s.userStore.SearchUsers(search, role)
}

// GetRoles retrieves a list of available user roles
func (s *UserService) GetRoles() []string {
	return s.userStore.GetRoles()
}

// GetAllUsers retrieves all users (kept for existing functionality, but SearchUsers is preferred for filtering)
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

// GetAssignmentStatsByStudentID retrieves assignment statistics for a student
func (s *UserService) GetAssignmentStatsByStudentID(studentID uint) (*models.AssignmentStats, error) {
	return s.userStore.GetAssignmentStatsByStudentID(int(studentID))
}

// GetRecentActivityByUserID retrieves recent activity for a user
func (s *UserService) GetRecentActivityByUserID(userID uint, limit int) ([]models.ActivityLog, error) {
	return s.userStore.GetRecentActivityByUserID(int(userID), limit)
}
