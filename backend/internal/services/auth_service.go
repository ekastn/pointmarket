package services

import (
	"errors"
	"pointmarket/backend/internal/auth"
	"pointmarket/backend/internal/config"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store"
	"pointmarket/backend/internal/utils"
)

// AuthService provides authentication related services
type AuthService struct {
	userStore *store.UserStore
	cfg       *config.Config
}

// NewAuthService creates a new AuthService
func NewAuthService(userStore *store.UserStore, cfg *config.Config) *AuthService {
	return &AuthService{userStore: userStore, cfg: cfg}
}

// Register creates a new user
func (s *AuthService) Register(req dtos.RegisterRequest) (models.User, error) {
	hashedPassword, err := utils.HashPassword(req.Password)
	if err != nil {
		return models.User{}, err
	}

	user := models.User{
		Username: req.Username,
		Password: hashedPassword,
		Name:     req.Name,
		Email:    req.Email,
		Role:     req.Role,
	}

	err = s.userStore.CreateUser(&user)
	return user, err
}

// Login authenticates a user and returns a JWT token
func (s *AuthService) Login(req dtos.LoginRequest) (models.User, string, error) {
	user, err := s.userStore.GetUserByUsername(req.Username)
	if err != nil {
		return models.User{}, "", err
	}
	if user == nil {
		return models.User{}, "", errors.New("invalid credentials")
	}

	err = utils.CheckPassword(req.Password, user.Password)
	if err != nil {
		return models.User{}, "", errors.New("invalid credentials")
	}

	token, err := auth.GenerateJWT(user.Username, user.Role, s.cfg)
	if err != nil {
		return models.User{}, "", err
	}

	return *user, token, nil
}

// GetUserProfile retrieves a user's profile by username
func (s *AuthService) GetUserProfile(username string) (*models.User, error) {
	user, err := s.userStore.GetUserByUsername(username)
	if err != nil {
		return nil, err
	}
	if user == nil {
		return nil, errors.New("user not found")
	}
	return user, nil
}
