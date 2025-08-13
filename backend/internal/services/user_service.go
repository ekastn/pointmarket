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

// CreateUser creates a new user
func (s *UserService) CreateUser(ctx context.Context, req dtos.CreateUserRequest) error {
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

// SearchUsers retrieves users based on search term and role
func (s *UserService) SearchUsers(ctx context.Context, search, role string) ([]gen.User, error) {
	data := gen.SearchUsersParams{
		DisplayName: search,
		Username:    search,
		Email:       search,
		Role:        gen.UsersRole(role),
	}
	return s.q.SearchUsers(ctx, data)
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

// GetAllActiveStudents retrieves all active students (role 'siswa')
func (s *UserService) GetActiveStudents(ctx context.Context) ([]gen.GetActiveStudentsRow, error) {
	return s.q.GetActiveStudents(ctx)
}
