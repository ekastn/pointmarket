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

// CreateUser handles creating a new user.
//
// @Tags users
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param payload body dtos.CreateUserRequest true "Create user payload"
// @Success 201 {object} dtos.APIResponse{data=dtos.NullData}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 409 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /users [post]
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

// UpdateUserProfile handles updating a user's profile information.
//
// @Tags profile
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param payload body dtos.UpdateProfileRequest true "Update profile payload"
// @Success 200 {object} dtos.APIResponse{data=dtos.NullData}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /profile/ [put]
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

// GetUserProfile handles fetching the current user's profile (users + user_profiles).
//
// @Tags profile
// @Security BearerAuth
// @Produce json
// @Success 200 {object} dtos.APIResponse{data=dtos.ProfileResponse}
// @Failure 401 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /profile/ [get]
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

// ChangePassword allows the current authenticated user to change their password.
//
// @Tags profile
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param payload body dtos.ChangePasswordRequest true "Change password payload"
// @Success 200 {object} dtos.APIResponse{data=dtos.NullData}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /profile/password [put]
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

// GetAllUsers handles fetching all users with pagination, search, and role filters.
//
// @Tags users
// @Security BearerAuth
// @Produce json
// @Param search query string false "Search term"
// @Param role query string false "Role filter"
// @Param page query int false "Page" default(1)
// @Param limit query int false "Limit" default(10)
// @Success 200 {object} dtos.PaginatedResponse{data=[]dtos.UserDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /users [get]
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

// GetUserByID handles fetching a user by ID.
//
// @Tags users
// @Security BearerAuth
// @Produce json
// @Param id path int true "User ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.UserDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /users/{id} [get]
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

// GetRoles handles fetching all available user roles.
//
// @Tags users
// @Security BearerAuth
// @Produce json
// @Success 200 {object} dtos.APIResponse{data=[]string}
// @Failure 401 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /roles [get]
func (h *UserHandler) GetRoles(c *gin.Context) {
	roles := h.userService.GetRoles()
	response.Success(c, http.StatusOK, "Roles retrieved successfully", roles)
}

// UpdateUserRole handles updating a user's role (admin only).
//
// @Tags users
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param id path int true "User ID"
// @Param payload body dtos.UpdateUserRoleRequest true "Update user role payload"
// @Success 200 {object} dtos.APIResponse{data=dtos.NullData}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /users/{id}/role [put]
func (h *UserHandler) UpdateUserRole(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid user ID")
		return
	}

	var req dtos.UpdateUserRoleRequest
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

// DeleteUser handles deleting a user (sets role to 'inactive').
//
// @Tags users
// @Security BearerAuth
// @Produce json
// @Param id path int true "User ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.NullData}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /users/{id} [delete]
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

// UpdateUser handles updating a user's information (admin only).
//
// @Tags users
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param id path int true "User ID"
// @Param payload body dtos.UpdateUserRequest true "Update user payload"
// @Success 200 {object} dtos.APIResponse{data=dtos.NullData}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /users/{id} [put]
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
//
// @Tags profile
// @Security BearerAuth
// @Accept multipart/form-data
// @Produce json
// @Param file formData file true "Avatar file"
// @Success 200 {object} dtos.APIResponse{data=dtos.AvatarUploadResponse}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /profile/avatar [patch]
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
	response.Success(c, http.StatusOK, "Avatar updated", dtos.AvatarUploadResponse{AvatarURL: publicURL})
}

// GetUserDetails handles fetching detailed user information for the admin panel.
//
// @Tags users
// @Security BearerAuth
// @Produce json
// @Param id path int true "User ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.UserDetailsDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /users/{id}/details [get]
func (h *UserHandler) GetUserDetails(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid user ID")
		return
	}

	details, err := h.userService.GetUserDetails(c.Request.Context(), id)
	if err == sql.ErrNoRows {
		response.Error(c, http.StatusNotFound, "User not found")
		return
	}
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "User details retrieved successfully", details)
}
