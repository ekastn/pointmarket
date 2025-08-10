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

	response.Success(c, http.StatusCreated, "User registered successfully", user)
}

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
		ID:       int(user.ID),
		Email:    user.Email,
		Username: user.Username,
		Role:     string(user.Role),
	}

	loginResponse := dtos.LoginResponse{
		Token: token,
		User:  userDTO,
	}
	response.Success(c, http.StatusOK, "Login successful", loginResponse)
}
