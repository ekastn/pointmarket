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
	userService    services.UserService
	studentService *services.StudentService
	maxBodyBytes   int64
}

func NewUserHandler(userService services.UserService, studentService *services.StudentService, maxAvatarMB int) *UserHandler {
	maxBytes := int64(maxAvatarMB)
	if maxBytes <= 0 {
		maxBytes = 5
	}
	maxBytes = maxBytes*1024*1024 + 512*1024 // add a small overhead
	return &UserHandler{userService: userService, studentService: studentService, maxBodyBytes: maxBytes}
}

// CreateUser handles creating a new user
func (h *UserHandler) CreateUser(c *gin.Context) {
	var req dtos.CreateUserRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	err := h.userService.CreateUser(c.Request.Context(), req)
	if err == services.ErrUserAlreadyExists {
		response.Error(c, http.StatusConflict, err.Error())
		return
	}
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "User created successfully", nil)
}

// UpdateUserProfile handles updating a user's profile information
func (h *UserHandler) UpdateUserProfile(c *gin.Context) {
	userID := middleware.GetUserID(c)

	var req dtos.UpdateProfileRequest
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

// GetUserProfile handles fetching the current user's profile (users + user_profiles)
func (h *UserHandler) GetUserProfile(c *gin.Context) {
	userID := middleware.GetUserID(c)
	prof, err := h.userService.GetUserProfile(c.Request.Context(), int64(userID))
	if err != nil {
		if err == sql.ErrNoRows {
			response.Error(c, http.StatusNotFound, "User not found")
			return
		}
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	// Attach student fragment for siswa if available
	if h.studentService != nil {
		if role := middleware.GetRole(c); role == "siswa" {
			if st, err := h.studentService.GetByUserID(c.Request.Context(), int64(userID)); err == nil && st != nil {
				prof.Student = st
			}
		}
	}
	response.Success(c, http.StatusOK, "Profile retrieved successfully", prof)
}

// ChangePassword allows the current authenticated user to change their password
func (h *UserHandler) ChangePassword(c *gin.Context) {
	userID := middleware.GetUserID(c)

	var req dtos.ChangePasswordRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	if err := h.userService.ChangePassword(c.Request.Context(), int64(userID), req); err != nil {
		// Map common errors to appropriate status codes
		switch err.Error() {
		case "invalid current password":
			response.Error(c, http.StatusUnauthorized, "Invalid current password")
			return
		case "passwords do not match", "password must be at least 8 characters", "password must contain letters and numbers", "new password must be different from current password":
			response.Error(c, http.StatusBadRequest, err.Error())
			return
		default:
			response.Error(c, http.StatusInternalServerError, err.Error())
			return
		}
	}

	response.Success(c, http.StatusOK, "Password changed successfully", nil)
}

// GetAllUsers handles fetching all users with pagination, search, and role filters
func (h *UserHandler) GetAllUsers(c *gin.Context) {
	search := c.Query("search")
	role := c.Query("role")
	page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
	limit, _ := strconv.Atoi(c.DefaultQuery("limit", "10"))

	users, totalRecords, err := h.userService.SearchUsers(c.Request.Context(), search, role, page, limit)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	var userDTOs []dtos.UserDTO
	for _, user := range users {
		userDTO := dtos.UserDTO{
			ID:        int(user.ID),
			Email:     user.Email,
			Username:  user.Username,
			Role:      string(user.Role),
			Name:      user.DisplayName,
			CreatedAt: user.CreatedAt,
			UpdatedAt: user.UpdatedAt,
		}
		userDTOs = append(userDTOs, userDTO)
	}

	response.Paginated(c, http.StatusOK, "Users retrieved successfully", userDTOs, totalRecords, page, limit)
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

// UpdateUser handles updating a user's information (admin only)
func (h *UserHandler) UpdateUser(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid user ID")
		return
	}

	var req dtos.UpdateUserRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	err = h.userService.UpdateUser(c.Request.Context(), id, req)
	if err == sql.ErrNoRows {
		response.Error(c, http.StatusNotFound, "User not found")
		return
	}
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "User updated successfully", nil)
}

// PatchUserAvatar handles updating the current user's avatar image via multipart upload.
// Method: PATCH /profile/avatar
func (h *UserHandler) PatchUserAvatar(c *gin.Context) {
	userID := middleware.GetUserID(c)
	// Enforce a hard cap on the request body to fail fast on huge uploads
	if h.maxBodyBytes > 0 {
		c.Request.Body = http.MaxBytesReader(c.Writer, c.Request.Body, h.maxBodyBytes)
	}
	file, err := c.FormFile("file")
	if err != nil {
		response.Error(c, http.StatusBadRequest, "missing file")
		return
	}
	f, err := file.Open()
	if err != nil {
		response.Error(c, http.StatusBadRequest, "invalid file")
		return
	}
	defer f.Close()

	publicURL, err := h.userService.UploadUserAvatar(c.Request.Context(), int64(userID), f)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Avatar updated", gin.H{"avatar_url": publicURL})
}
