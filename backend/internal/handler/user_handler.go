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

// GetStudentDashboardStats handles fetching aggregated statistics for a student's dashboard
func (h *UserHandler) GetStudentDashboardStats(c *gin.Context) {
	userID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User not found in context")
		return
	}

	stats, err := h.userService.GetStudentDashboardStats(userID.(uint))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Student dashboard statistics retrieved successfully", stats)
}

// GetAdminDashboardCounts handles fetching counts for admin dashboard
func (h *UserHandler) GetAdminDashboardCounts(c *gin.Context) {
	counts, err := h.userService.GetAdminDashboardCounts()
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Admin dashboard counts retrieved successfully", counts)
}

// GetTeacherDashboardCounts handles fetching counts for teacher dashboard
func (h *UserHandler) GetTeacherDashboardCounts(c *gin.Context) {
	userID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User not found in context")
		return
	}

	counts, err := h.userService.GetTeacherDashboardCounts(userID.(uint))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Teacher dashboard counts retrieved successfully", counts)
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

// GetAssignmentStatsByStudentID handles fetching assignment statistics for a student
func (h *UserHandler) GetAssignmentStatsByStudentID(c *gin.Context) {
	userID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User not found in context")
		return
	}

	stats, err := h.userService.GetAssignmentStatsByStudentID(userID.(uint))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Assignment statistics retrieved successfully", stats)
}

// GetRecentActivityByUserID handles fetching recent activity for a user
func (h *UserHandler) GetRecentActivityByUserID(c *gin.Context) {
	userID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User not found in context")
		return
	}

	limitStr := c.DefaultQuery("limit", "10")
	limit, err := strconv.Atoi(limitStr)
	if err != nil || limit <= 0 {
		response.Error(c, http.StatusBadRequest, "Invalid limit parameter")
		return
	}

	activities, err := h.userService.GetRecentActivityByUserID(userID.(uint), limit)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Recent activity retrieved successfully", activities)
}

// GetWeeklyEvaluationProgressByStudentID handles fetching weekly evaluation progress for a student
func (h *UserHandler) GetWeeklyEvaluationProgressByStudentID(c *gin.Context) {
	userID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User not found in context")
		return
	}

	weeksStr := c.DefaultQuery("weeks", "8")
	weeks, err := strconv.Atoi(weeksStr)
	if err != nil || weeks <= 0 {
		response.Error(c, http.StatusBadRequest, "Invalid weeks parameter")
		return
	}

	progress, err := h.userService.GetWeeklyEvaluationProgressByStudentID(userID.(uint), weeks)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return	}
	response.Success(c, http.StatusOK, "Weekly evaluation progress retrieved successfully", progress)
}
