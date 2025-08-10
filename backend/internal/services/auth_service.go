package services

import (
	"context"
	"errors"
	"pointmarket/backend/internal/auth"
	"pointmarket/backend/internal/config"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store"
	"pointmarket/backend/internal/store/gen"
	"pointmarket/backend/internal/utils"
)

// AuthService provides authentication related services
type AuthService struct {
	userStore *store.UserStore
	q         gen.Querier
	cfg       *config.Config
}

// NewAuthService creates a new AuthService
func NewAuthService(userStore *store.UserStore, cfg *config.Config, q gen.Querier) *AuthService {
	return &AuthService{userStore: userStore, cfg: cfg, q: q}
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
	user, err := s.q.GetUserByUsername(ctx, req.Username)
	if err != nil {
		return gen.User{}, "", err
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
