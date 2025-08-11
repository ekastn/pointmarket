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

	// If no user_id param, return all badges
	badges, err := h.badgeService.GetBadges(c.Request.Context())
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to retrieve badges: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Badges retrieved successfully", dtos.ListBadgesResponseDTO{
		Badges: badges,
		Total:  len(badges),
	})
}

// UpdateBadge handles updating an existing badge (Admin-only)
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
