package handler

import (
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"strconv"

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

// GetStudentEvaluationStatus handles fetching weekly evaluation status for all students
func (h *UserHandler) GetStudentEvaluationStatus(c *gin.Context) {
	statuses, err := h.userService.GetStudentEvaluationStatus()
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Student evaluation statuses retrieved successfully", statuses)
}

// GetWeeklyEvaluationOverview handles fetching aggregated weekly progress for teachers
func (h *UserHandler) GetWeeklyEvaluationOverview(c *gin.Context) {
	weeksStr := c.DefaultQuery("weeks", "4") // Default to 4 weeks
	weeks, err := strconv.Atoi(weeksStr)
	if err != nil || weeks <= 0 {
		response.Error(c, http.StatusBadRequest, "Invalid weeks parameter")
		return
	}

	overviews, err := h.userService.GetWeeklyEvaluationOverview(weeks)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Weekly evaluation overview retrieved successfully", overviews)
}
