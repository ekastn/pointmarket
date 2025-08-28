package services

import (
	"context"
	"database/sql"
	"errors"
	"pointmarket/backend/internal/auth"
	"pointmarket/backend/internal/config"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
	"pointmarket/backend/internal/utils"
)

// AuthService provides authentication related services
type AuthService struct {
	q   gen.Querier
	cfg *config.Config
}

// NewAuthService creates a new AuthService
func NewAuthService(cfg *config.Config, q gen.Querier) *AuthService {
	return &AuthService{cfg: cfg, q: q}
}

// Register creates a new user
func (s *AuthService) Register(ctx context.Context, req dtos.RegisterRequest) (gen.User, error) {
	hashedPassword, err := utils.HashPassword(req.Password)
	if err != nil {
		return gen.User{}, err
	}

	data := gen.CreateUserParams{
		Email:       req.Email,
		Username:    req.Username,
		Password:    hashedPassword,
		DisplayName: req.Name,
		Role:        gen.UsersRole(req.Role),
	}

	res, err := s.q.CreateUser(ctx, data)
	if err != nil {
		return gen.User{}, err
	}

	userID, err := res.LastInsertId()
	if err != nil {
		return gen.User{}, err
	}

	return s.q.GetUserByID(ctx, userID)
}

// Login authenticates a user and returns a JWT token
func (s *AuthService) Login(ctx context.Context, req dtos.LoginRequest) (gen.User, string, error) {
	// Try username first
	identifier := req.Username
	user, err := s.q.GetUserByUsername(ctx, identifier)
	if err != nil {
		if err == sql.ErrNoRows {
			// Fallback: try resolving as NIM (students.student_id)
			if st, err2 := s.q.GetStudentByStudentID(ctx, identifier); err2 == nil {
				if u2, err3 := s.q.GetUserByID(ctx, st.UserID); err3 == nil {
					user = u2
				} else {
					return gen.User{}, "", errors.New("invalid credentials")
				}
			} else {
				return gen.User{}, "", errors.New("invalid credentials")
			}
		} else {
			return gen.User{}, "", errors.New("invalid credentials")
		}
	}

	err = utils.CheckPassword(req.Password, user.Password)
	if err != nil {
		return gen.User{}, "", errors.New("invalid credentials")
	}

	token, err := auth.GenerateJWT(user.Username, string(user.Role), s.cfg)
	if err != nil {
		return gen.User{}, "", err
	}

	return user, token, nil
}
