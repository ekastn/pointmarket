package handler

import (
	"net/http"
	"strconv"
	"strings"
	"time"

	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/middleware"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"pointmarket/backend/internal/store/gen"

	"github.com/gin-gonic/gin"
)

var _ = dtos.APIResponse{}

type PointsHandler struct {
	points *services.PointsService
	q      gen.Querier
}

func NewPointsHandler(points *services.PointsService, q gen.Querier) *PointsHandler {
	return &PointsHandler{points: points, q: q}
}

// GetUserStats godoc
// @Summary Get user points stats
// @Description Reads current total points and updated_at (admin-only)
// @Tags users
// @Produce json
// @Security BearerAuth
// @Param id path int true "User ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.UserStatsResponse}
// @Failure 400 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /users/{id}/stats [get]
func (h *PointsHandler) GetUserStats(c *gin.Context) {
	idStr := c.Param("id")
	userID, err := strconv.ParseInt(idStr, 10, 64)
	if err != nil || userID <= 0 {
		response.Error(c, http.StatusBadRequest, "Invalid user ID")
		return
	}

	// Try fetch stats; initialize if missing
	stats, err := h.q.GetUserStats(c.Request.Context(), userID)
	if err != nil {
		// Initialize and retry
		if _, ierr := h.points.GetOrInitTotal(c.Request.Context(), userID); ierr == nil {
			if stats, err = h.q.GetUserStats(c.Request.Context(), userID); err == nil {
				var updatedAt *time.Time
				if stats.UpdatedAt.Valid {
					t := stats.UpdatedAt.Time
					updatedAt = &t
				}
				response.Success(c, http.StatusOK, "OK", dtos.UserStatsResponse{
					TotalPoints: stats.TotalPoints,
					UpdatedAt:   updatedAt,
				})
				return
			}
		}
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	var updatedAt *time.Time
	if stats.UpdatedAt.Valid {
		t := stats.UpdatedAt.Time
		updatedAt = &t
	}
	response.Success(c, http.StatusOK, "OK", dtos.UserStatsResponse{
		TotalPoints: stats.TotalPoints,
		UpdatedAt:   updatedAt,
	})
}

// AdjustUserStats godoc
// @Summary Adjust user points
// @Description Adds or deducts points from a user (admin-only)
// @Tags users
// @Accept json
// @Produce json
// @Security BearerAuth
// @Param id path int true "User ID"
// @Param request body dtos.AdjustUserStatsRequest true "Adjustment payload"
// @Success 201 {object} dtos.APIResponse{data=dtos.UserStatsResponse}
// @Failure 400 {object} dtos.APIError
// @Failure 409 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /users/{id}/stats [post]
func (h *PointsHandler) AdjustUserStats(c *gin.Context) {
	idStr := c.Param("id")
	userID, err := strconv.ParseInt(idStr, 10, 64)
	if err != nil || userID <= 0 {
		response.Error(c, http.StatusBadRequest, "Invalid user ID")
		return
	}

	var req dtos.AdjustUserStatsRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}
	if req.Delta == 0 {
		response.Error(c, http.StatusBadRequest, "delta must be non-zero")
		return
	}

	reason := ""
	if req.Reason != nil {
		reason = strings.TrimSpace(*req.Reason)
	}

	refID := middleware.GetUserID(c)
	refType := "admin"

	var total int64
	if req.Delta > 0 {
		total, err = h.points.Add(c.Request.Context(), userID, req.Delta, reason, refType, &refID)
	} else {
		total, err = h.points.Deduct(c.Request.Context(), userID, -req.Delta, reason, refType, &refID)
	}
	if err != nil {
		switch err {
		case services.ErrInvalidAmount:
			response.Error(c, http.StatusBadRequest, err.Error())
			return
		case services.ErrInsufficientPoints:
			response.Error(c, http.StatusConflict, err.Error())
			return
		default:
			response.Error(c, http.StatusInternalServerError, err.Error())
			return
		}
	}

	// Fetch updated_at for completeness
	stats, err := h.q.GetUserStats(c.Request.Context(), userID)
	if err != nil {
		// Still return total if updated_at cannot be read
		response.Success(c, http.StatusCreated, "Updated", dtos.UserStatsResponse{
			TotalPoints: total,
		})
		return
	}

	var updatedAt *time.Time
	if stats.UpdatedAt.Valid {
		t := stats.UpdatedAt.Time
		updatedAt = &t
	}
	response.Success(c, http.StatusCreated, "Updated", dtos.UserStatsResponse{
		TotalPoints: stats.TotalPoints,
		UpdatedAt:   updatedAt,
	})
}
