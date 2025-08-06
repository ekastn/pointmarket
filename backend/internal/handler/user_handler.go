package handler

import (
	"database/sql"
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/middleware"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"pointmarket/backend/internal/utils"
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
	userID := middleware.GetUserID(c)

	user, err := h.userService.GetUserByID(userID)
	if err != nil {
		response.Error(c, http.StatusNotFound, "User not found")
		return
	}

	var userDTO dtos.UserDTO
	userDTO.FromUser(user)
	response.Success(c, http.StatusOK, "User profile retrieved successfully", userDTO)
}

// UpdateUserProfile handles updating a user's profile information
func (h *UserHandler) UpdateUserProfile(c *gin.Context) {
	userID := middleware.GetUserID(c)

	var req dtos.UpdateProfileRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	err := h.userService.UpdateUserProfile(userID, req)
	if err == sql.ErrNoRows {
		response.Error(c, http.StatusNotFound, "User not found")
		return
	}
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "User profile updated successfully", nil)
}

// GetAllUsers handles fetching all users (admin only)
func (h *UserHandler) GetAllUsers(c *gin.Context) {
	users, err := h.userService.GetAllUsers()
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	var userDTOs []dtos.UserDTO
	for _, user := range users {
		var userDTO dtos.UserDTO
		userDTO.FromUser(user)
		userDTOs = append(userDTOs, userDTO)
	}
	response.Success(c, http.StatusOK, "Users retrieved successfully", userDTOs)
}

// GetUserByID handles fetching a user by ID
func (h *UserHandler) GetUserByID(c *gin.Context) {
	id, ok := utils.GetIDFromParam(c, "id")
	if !ok {
		return
	}

	user, err := h.userService.GetUserByID(id)
	if err == sql.ErrNoRows {
		response.Error(c, http.StatusNotFound, "User not found")
		return
	}

	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	var userDTO dtos.UserDTO
	userDTO.FromUser(user)
	response.Success(c, http.StatusOK, "User retrieved successfully", userDTO)
}

// UpdateUserRole handles updating a user's role (admin only)
func (h *UserHandler) UpdateUserRole(c *gin.Context) {
	id, ok := utils.GetIDFromParam(c, "id")
	if !ok {
		return // Error response already sent by helper
	}

	var req struct {
		Role string `json:"role" binding:"required"`
	}

	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	if err := h.userService.UpdateUserRole(uint(id), req.Role); err != nil {
		if err == sql.ErrNoRows {
			response.Error(c, http.StatusNotFound, "User not found")
			return
		}
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "User role updated successfully", nil)
}

// DeleteUser handles deleting a user (admin only)
func (h *UserHandler) DeleteUser(c *gin.Context) {
	id, ok := utils.GetIDFromParam(c, "id")
	if !ok {
		return // Error response already sent by helper
	}
	err := h.userService.DeleteUser(uint(id))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "User deleted successfully", nil)
}

// GetStudentDashboardStats handles fetching aggregated statistics for a student's dashboard
func (h *UserHandler) GetStudentDashboardStats(c *gin.Context) {
	userID := middleware.GetUserID(c)

	stats, err := h.userService.GetStudentDashboardStats(userID)
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
	userID := middleware.GetUserID(c)

	counts, err := h.userService.GetTeacherDashboardCounts(userID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Teacher dashboard counts retrieved successfully", counts)
}

// GetAssignmentStatsByStudentID handles fetching assignment statistics for a student
func (h *UserHandler) GetAssignmentStatsByStudentID(c *gin.Context) {
	userID := middleware.GetUserID(c)

	stats, err := h.userService.GetAssignmentStatsByStudentID(userID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Assignment statistics retrieved successfully", stats)
}

// GetRecentActivityByUserID handles fetching recent activity for a user
func (h *UserHandler) GetRecentActivityByUserID(c *gin.Context) {
	userID := middleware.GetUserID(c)

	limitStr := c.DefaultQuery("limit", "10")
	limit, err := strconv.Atoi(limitStr)
	if err != nil || limit <= 0 {
		response.Error(c, http.StatusBadRequest, "Invalid limit parameter")
		return
	}

	activities, err := h.userService.GetRecentActivityByUserID(userID, limit)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Recent activity retrieved successfully", activities)
}
