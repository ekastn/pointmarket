package handler

import (
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"

	"github.com/gin-gonic/gin"
)

type AuthHandler struct {
	authService services.AuthService
}

func NewAuthHandler(authService services.AuthService) *AuthHandler {
	return &AuthHandler{authService: authService}
}

// Register registers a new user.
//
// @Tags auth
// @Accept json
// @Produce json
// @Param payload body dtos.RegisterRequest true "Register payload"
// @Success 201 {object} dtos.APIResponse{data=dtos.UserDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /auth/register [post]
func (h *AuthHandler) Register(c *gin.Context) {
	var registerDTO dtos.RegisterRequest
	if err := c.ShouldBindJSON(&registerDTO); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	user, err := h.authService.Register(c.Request.Context(), registerDTO)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	userDTO := dtos.UserDTO{
		ID:        int(user.ID),
		Email:     user.Email,
		Username:  user.Username,
		Role:      string(user.Role),
		Name:      user.DisplayName,
		CreatedAt: user.CreatedAt,
		UpdatedAt: user.UpdatedAt,
	}

	response.Success(c, http.StatusCreated, "User registered successfully", userDTO)
}

// Login authenticates a user and returns a JWT token.
//
// @Tags auth
// @Accept json
// @Produce json
// @Param payload body dtos.LoginRequest true "Login payload"
// @Success 200 {object} dtos.APIResponse{data=dtos.LoginResponse}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Router /auth/login [post]
func (h *AuthHandler) Login(c *gin.Context) {
	var loginDTO dtos.LoginRequest
	if err := c.ShouldBindJSON(&loginDTO); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	user, token, err := h.authService.Login(c.Request.Context(), loginDTO)
	if err != nil {
		response.Error(c, http.StatusUnauthorized, "Invalid credentials")
		return
	}

	userDTO := dtos.UserDTO{
		ID:        int(user.ID),
		Email:     user.Email,
		Username:  user.Username,
		Role:      string(user.Role),
		Name:      user.DisplayName,
		CreatedAt: user.CreatedAt,
		UpdatedAt: user.UpdatedAt,
	}

	loginResponse := dtos.LoginResponse{
		Token: token,
		User:  userDTO,
	}
	response.Success(c, http.StatusOK, "Login successful", loginResponse)
}
