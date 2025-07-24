package handler

import (
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"

	"github.com/gin-gonic/gin"
)

type UserHandler struct {
	userService services.UserService
}

func NewUserHandler(userService services.UserService) *UserHandler {
	return &UserHandler{userService: userService}
}

func (h *UserHandler) GetUserProfile(c *gin.Context) {
	userID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User not found in context")
		return
	}

	user, err := h.userService.GetUserByID(userID.(uint))
	if err != nil {
		response.Error(c, http.StatusNotFound, "User not found")
		return
	}

	var userDTO dtos.UserDTO
	userDTO.FromUser(user)
	response.Success(c, http.StatusOK, "User profile retrieved successfully", userDTO)
}
