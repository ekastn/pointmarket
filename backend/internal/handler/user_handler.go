package handler

import (
	"database/sql"
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/middleware"
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

// CreateUser handles creating a new user
func (h *UserHandler) CreateUser(c *gin.Context) {
	userID := middleware.GetUserID(c)

	var req dtos.CreateUserRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	user, err := h.userService.GetUserByID(c.Request.Context(), int64(userID))
	if err != nil {
		response.Error(c, http.StatusNotFound, "User not found")
		return
	}

	userDTO := dtos.UserDTO{
		ID:       int(user.ID),
		Email:    user.Email,
		Username: user.Username,
		Role:     string(user.Role),
		Name:     user.DisplayName,
	}
	response.Success(c, http.StatusOK, "User profile retrieved successfully", userDTO)
}

// UpdateUserProfile handles updating a user's profile information
func (h *UserHandler) UpdateUserProfile(c *gin.Context) {
	userID := middleware.GetUserID(c)

	var req dtos.UpdateUserRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	err := h.userService.UpdateUserProfile(c.Request.Context(), int64(userID), req)
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

// GetAllUsers handles fetching all users (admin only), now with search and role filters
func (h *UserHandler) GetAllUsers(c *gin.Context) {
	search := c.Query("search")
	role := c.Query("role")

	users, err := h.userService.SearchUsers(c.Request.Context(), search, role)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	var userDTOs []dtos.UserDTO
	for _, user := range users {
		userDTO := dtos.UserDTO{
			ID:       int(user.ID),
			Email:    user.Email,
			Username: user.Username,
			Role:     string(user.Role),
            Name:     user.DisplayName,
		}
		userDTOs = append(userDTOs, userDTO)
	}

	response.Success(c, http.StatusOK, "Users retrieved successfully", userDTOs)
}

// GetUserByID handles fetching a user by ID
func (h *UserHandler) GetUserByID(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid user ID")
		return
	}

	user, err := h.userService.GetUserByID(c.Request.Context(), id)
	if err == sql.ErrNoRows {
		response.Error(c, http.StatusNotFound, "User not found")
		return
	}

	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	userDTO := dtos.UserDTO{
		ID:       int(user.ID),
		Email:    user.Email,
		Username: user.Username,
		Role:     string(user.Role),
        Name:     user.DisplayName,
	}
	response.Success(c, http.StatusOK, "User retrieved successfully", userDTO)
}

// GetRoles handles fetching all available user roles
func (h *UserHandler) GetRoles(c *gin.Context) {
	roles := h.userService.GetRoles()
	response.Success(c, http.StatusOK, "Roles retrieved successfully", roles)
}

// UpdateUserRole handles updating a user's role (admin only)
func (h *UserHandler) UpdateUserRole(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid user ID")
		return
	}

	var req struct {
		Role string `json:"role" binding:"required"`
	}

	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	err = h.userService.UpdateUserRole(c.Request.Context(), id, req.Role)
	if err == sql.ErrNoRows {
		response.Error(c, http.StatusNotFound, "User not found")
		return
	}
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "User role updated successfully", nil)
}

// DeleteUser handles deleting a user (sets role to 'inactive')
func (h *UserHandler) DeleteUser(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid user ID")
		return
	}
	err = h.userService.DeleteUser(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "User deleted successfully", nil)
}
