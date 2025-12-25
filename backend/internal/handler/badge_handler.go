package handler

import (
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"strconv"

	"github.com/gin-gonic/gin"
)

// BadgeHandler handles badge-related HTTP requests
type BadgeHandler struct {
	badgeService services.BadgeService
}

// NewBadgeHandler creates a new BadgeHandler
func NewBadgeHandler(badgeService services.BadgeService) *BadgeHandler {
	return &BadgeHandler{badgeService: badgeService}
}

// CreateBadge handles creating a new badge (Admin-only)
// @Summary Create badge
// @Tags badges
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param request body dtos.CreateBadgeRequestDTO true "Badge"
// @Success 201 {object} dtos.APIResponse{data=dtos.BadgeDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /badges [post]
func (h *BadgeHandler) CreateBadge(c *gin.Context) {
	var req dtos.CreateBadgeRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request body: "+err.Error())
		return
	}

	badge, err := h.badgeService.CreateBadge(c.Request.Context(), req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to create badge: "+err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Badge created successfully", badge)
}

// GetBadgeByID handles fetching a single badge by ID
// @Summary Get badge by ID
// @Tags badges
// @Security BearerAuth
// @Produce json
// @Param id path int true "Badge ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.BadgeDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /badges/{id} [get]
func (h *BadgeHandler) GetBadgeByID(c *gin.Context) {
	idParam := c.Param("id")
	id, err := strconv.ParseInt(idParam, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid badge ID")
		return
	}

	badge, err := h.badgeService.GetBadgeByID(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to retrieve badge: "+err.Error())
		return
	}

	if badge.ID == 0 {
		response.Error(c, http.StatusNotFound, "Badge not found")
		return
	}

	response.Success(c, http.StatusOK, "Badge retrieved successfully", badge)
}

// GetBadges handles fetching a list of all badges (Auth required)
// @Summary List badges
// @Description Default response is paginated list of badges. If `user_id` is provided, returns all badge awards for that user (admin-only).
// @Tags badges
// @Security BearerAuth
// @Produce json
// @Param page query int false "Page" default(1)
// @Param limit query int false "Limit" default(10)
// @Param search query string false "Search term"
// @Param user_id query int false "User ID (admin-only; returns awards for that user)"
// @Success 200 {object} dtos.PaginatedResponse{data=[]dtos.BadgeDTO}
// @Success 200 {object} dtos.APIResponse{data=dtos.ListUserBadgesResponseDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /badges [get]
func (h *BadgeHandler) GetBadges(c *gin.Context) {
	// Check for user_id query parameter for admin-specific filtering
	userIDStr := c.Query("user_id")
	if userIDStr != "" {
		userID, err := strconv.ParseInt(userIDStr, 10, 64)
		if err != nil {
			response.Error(c, http.StatusBadRequest, "Invalid user ID parameter")
			return
		}

		// Admin check: Only admin can query for other user's badges
		userRole, exists := c.Get("role")
		if !exists || userRole.(string) != "admin" {
			response.Error(c, http.StatusForbidden, "Forbidden: Only admins can query badges for specific users")
			return
		}

		userBadges, err := h.badgeService.GetUserBadgesByUserID(c.Request.Context(), userID)
		if err != nil {
			response.Error(c, http.StatusInternalServerError, "Failed to retrieve user badges: "+err.Error())
			return
		}
		response.Success(c, http.StatusOK, "User badges retrieved successfully", dtos.ListUserBadgesResponseDTO{
			UserBadges: userBadges,
			Total:      len(userBadges),
		})
		return
	}

	// If no user_id param, return all badges with pagination
	page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
	limit, _ := strconv.Atoi(c.DefaultQuery("limit", "10"))
	search := c.Query("search")

	badges, totalBadges, err := h.badgeService.GetBadges(c.Request.Context(), page, limit, search)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to retrieve badges: "+err.Error())
		return
	}

	response.Paginated(c, http.StatusOK, "Badges retrieved successfully", badges, totalBadges, page, limit)
}

// UpdateBadge handles updating an existing badge (Admin-only)
// @Summary Update badge
// @Tags badges
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param id path int true "Badge ID"
// @Param request body dtos.UpdateBadgeRequestDTO true "Badge"
// @Success 200 {object} dtos.APIResponse{data=dtos.BadgeDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /badges/{id} [put]
func (h *BadgeHandler) UpdateBadge(c *gin.Context) {
	idParam := c.Param("id")
	id, err := strconv.ParseInt(idParam, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid badge ID")
		return
	}

	var req dtos.UpdateBadgeRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request body: "+err.Error())
		return
	}

	updatedBadge, err := h.badgeService.UpdateBadge(c.Request.Context(), id, req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to update badge: "+err.Error())
		return
	}

	if updatedBadge.ID == 0 { // Assuming ID will be 0 if not found
		response.Error(c, http.StatusNotFound, "Badge not found")
		return
	}

	response.Success(c, http.StatusOK, "Badge updated successfully", updatedBadge)
}

// DeleteBadge handles deleting a badge by its ID (Admin-only)
// @Summary Delete badge
// @Tags badges
// @Security BearerAuth
// @Produce json
// @Param id path int true "Badge ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.NullData}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /badges/{id} [delete]
func (h *BadgeHandler) DeleteBadge(c *gin.Context) {
	idParam := c.Param("id")
	id, err := strconv.ParseInt(idParam, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid badge ID")
		return
	}

	err = h.badgeService.DeleteBadge(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to delete badge: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Badge deleted successfully", nil)
}

// AwardBadge handles awarding a specific badge to a user (Admin-only)
// @Summary Award badge
// @Tags badges
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param id path int true "Badge ID"
// @Param request body dtos.AwardBadgeRequestDTO true "Award"
// @Success 201 {object} dtos.APIResponse{data=dtos.NullData}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /badges/{id}/award [post]
func (h *BadgeHandler) AwardBadge(c *gin.Context) {
	badgeIDParam := c.Param("id")
	badgeID, err := strconv.ParseInt(badgeIDParam, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid badge ID")
		return
	}

	var req dtos.AwardBadgeRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request body: "+err.Error())
		return
	}

	// Ensure the badgeID from path matches the one in the request body if provided
	if req.BadgeID != 0 && req.BadgeID != badgeID {
		response.Error(c, http.StatusBadRequest, "Badge ID in path and body do not match")
		return
	}
	req.BadgeID = badgeID // Use ID from path

	err = h.badgeService.AwardBadgeToUser(c.Request.Context(), req.UserID, req.BadgeID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to award badge: "+err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Badge awarded successfully", nil)
}

// RevokeBadge handles revoking a specific badge from a user (Admin-only)
// @Summary Revoke badge
// @Tags badges
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param id path int true "Badge ID"
// @Param request body dtos.AwardBadgeRequestDTO true "Revoke"
// @Success 200 {object} dtos.APIResponse{data=dtos.NullData}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /badges/{id}/revoke [delete]
func (h *BadgeHandler) RevokeBadge(c *gin.Context) {
	badgeIDParam := c.Param("id")
	badgeID, err := strconv.ParseInt(badgeIDParam, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid badge ID")
		return
	}

	var req dtos.AwardBadgeRequestDTO // Reusing DTO, but only UserID and BadgeID are relevant
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request body: "+err.Error())
		return
	}

	// Ensure the badgeID from path matches the one in the request body if provided
	if req.BadgeID != 0 && req.BadgeID != badgeID {
		response.Error(c, http.StatusBadRequest, "Badge ID in path and body do not match")
		return
	}
	req.BadgeID = badgeID // Use ID from path

	err = h.badgeService.RevokeBadgeFromUser(c.Request.Context(), req.UserID, req.BadgeID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to revoke badge: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Badge revoked successfully", nil)
}

// GetUserOwnBadges handles fetching the authenticated user's own badges
// @Summary Get my badges
// @Tags badges
// @Security BearerAuth
// @Produce json
// @Success 200 {object} dtos.APIResponse{data=dtos.ListUserBadgesResponseDTO}
// @Failure 401 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /my-badges [get]
func (h *BadgeHandler) GetUserOwnBadges(c *gin.Context) {
	userID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User ID not found in context")
		return
	}

	userBadges, err := h.badgeService.GetUserBadgesByUserID(c.Request.Context(), userID.(int64))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to retrieve user badges: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "User badges retrieved successfully", dtos.ListUserBadgesResponseDTO{
		UserBadges: userBadges,
		Total:      len(userBadges),
	})
}

// GetAllUserBadges handles fetching all badge awards for admin view
// @Summary List badge awards
// @Tags badges
// @Security BearerAuth
// @Produce json
// @Param page query int false "Page" default(1)
// @Param limit query int false "Limit" default(10)
// @Param search query string false "Search term"
// @Success 200 {object} dtos.PaginatedResponse{data=[]dtos.UserBadgeDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /badges/awards [get]
func (h *BadgeHandler) GetAllUserBadges(c *gin.Context) {
	page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
	limit, _ := strconv.Atoi(c.DefaultQuery("limit", "10"))
	search := c.Query("search")

	awards, total, err := h.badgeService.GetAllUserBadges(c.Request.Context(), page, limit, search)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to retrieve badge awards: "+err.Error())
		return
	}

	response.Paginated(c, http.StatusOK, "Badge awards retrieved successfully", awards, total, page, limit)
}
