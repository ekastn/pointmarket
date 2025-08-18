package services

import (
	"context"
	"database/sql"
	"fmt"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
	"pointmarket/backend/internal/utils"
)

type UserService struct {
	q gen.Querier
}

func NewUserService(q gen.Querier) *UserService {
	return &UserService{q: q}
}

func (s *UserService) GetUserByID(ctx context.Context, id int64) (gen.User, error) {
	return s.q.GetUserByID(ctx, id)
}

var ErrUserAlreadyExists = fmt.Errorf("user with this username or email already exists")

// CreateUser creates a new user
func (s *UserService) CreateUser(ctx context.Context, req dtos.CreateUserRequest) error {
	// Check if user with this email already exists
	_, err := s.q.GetUserByEmail(ctx, req.Email)
	if err == nil {
		return ErrUserAlreadyExists
	}
	if err != sql.ErrNoRows {
		return fmt.Errorf("failed to check existing email: %w", err)
	}

	// Check if user with this username already exists
	_, err = s.q.GetUserByUsername(ctx, req.Username)
	if err == nil {
		return ErrUserAlreadyExists
	}
	if err != sql.ErrNoRows {
		return fmt.Errorf("failed to check existing username: %w", err)
	}

	hashedPassword, err := utils.HashPassword(req.Password)
	if err != nil {
		return fmt.Errorf("failed to hash password: %w", err)
	}

	data := gen.CreateUserParams{
		Email:       req.Email,
		Username:    req.Username,
		Password:    hashedPassword,
		DisplayName: req.Name,
		Role:        gen.UsersRole(req.Role),
	}

	_, err = s.q.CreateUser(ctx, data)
	if err != nil {
		return err
	}

	return nil
}

// UpdateUserProfile updates a user's profile information
func (s *UserService) UpdateUserProfile(ctx context.Context, userID int64, req dtos.UpdateUserRequest) error {
	user, err := s.q.GetUserByID(ctx, userID)
	if err != nil {
		return err
	}

	if user.ID == 0 {
		return sql.ErrNoRows
	}

	data := gen.UpdateUserProfileParams{
		UserID: userID,
		AvatarUrl: sql.NullString{
			String: *req.AvatarURL,
		},
		Bio: sql.NullString{
			String: *req.Bio,
		},
	}

	return s.q.UpdateUserProfile(ctx, data)
}

// SearchUsers retrieves users based on search term and role with pagination
func (s *UserService) SearchUsers(ctx context.Context, search, role string, page, limit int) ([]gen.User, int64, error) {
	if page <= 0 {
		page = 1
	}
	if limit <= 0 {
		limit = 10
	}
	offset := (page - 1) * limit

	// Parameters for both searching and counting
	searchParams := gen.SearchUsersParams{
		Search: search,
		Role:   gen.UsersRole(role),
		Limit:  int32(limit),
		Offset: int32(offset),
	}

	countParams := gen.CountSearchedUsersParams{
		Search: search,
		Role:   gen.UsersRole(role),
	}

	// Get total count of users matching the filter
	totalUsers, err := s.q.CountSearchedUsers(ctx, countParams)
	if err != nil {
		return nil, 0, fmt.Errorf("failed to count users: %w", err)
	}

	// Get the paginated list of users
	users, err := s.q.SearchUsers(ctx, searchParams)
	if err != nil {
		return nil, 0, fmt.Errorf("failed to search users: %w", err)
	}

	return users, totalUsers, nil
}

// GetRoles retrieves a list of available user roles
func (s *UserService) GetRoles() []string {
	return []string{"siswa", "guru", "admin"}
}

// GetAllUsers retrieves all users
func (s *UserService) GetAllUsers(ctx context.Context) ([]gen.User, error) {
	return s.q.GetUsers(ctx)
}

// UpdateUserRole updates a user's role
func (s *UserService) UpdateUserRole(ctx context.Context, userID int64, role string) error {
	data := gen.UpdateUserRoleParams{
		ID:   userID,
		Role: gen.UsersRole(role),
	}
	return s.q.UpdateUserRole(ctx, data)
}

// DeleteUser deletes a user (sets role to 'inactive')
func (s *UserService) DeleteUser(ctx context.Context, userID int64) error {
	return s.q.DeleteUser(ctx, userID)
}

// UpdateUser updates a user's information
func (s *UserService) UpdateUser(ctx context.Context, userID int64, req dtos.UpdateUserRequest) error {
	data := gen.UpdateUserParams{
		ID:          userID,
		Username:    req.Username,
		DisplayName: req.Name,
		Email:       req.Email,
		Role:        gen.UsersRole(req.Role),
	}
	return s.q.UpdateUser(ctx, data)
}

// GetAllActiveStudents retrieves all active students (role 'siswa')
func (s *UserService) GetActiveStudents(ctx context.Context) ([]gen.GetActiveStudentsRow, error) {
	return s.q.GetActiveStudents(ctx)
}

// UpdateUserLearningStyle insert new record to user_learning_style table
func (s *UserService) UpdateUserLearningStyle(
	ctx context.Context,
	userID int64,
	prefType string,
	label string,
	score dtos.VARKScores,
) error {
	return s.q.CreateUserLearningStyle(ctx, gen.CreateUserLearningStyleParams{
		UserID:           userID,
		Type:             gen.UserLearningStylesType(prefType),
		Label:            label,
		ScoreVisual:      &score.Visual,
		ScoreAuditory:    &score.Auditory,
		ScoreReading:     &score.Reading,
		ScoreKinesthetic: &score.Kinesthetic,
	})
}

// GetLatestUserLearningStyle returns the latest learning style data for a user
func (s *UserService) GetLatestUserLearningStyle(ctx context.Context, userID int64) (gen.UserLearningStyle, error) {
	return s.q.GetLatestUserLearningStyle(ctx, userID)
}
