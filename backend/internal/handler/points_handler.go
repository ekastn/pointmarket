package handler

import (
	"net/http"
	"strconv"
	"strings"

	"pointmarket/backend/internal/middleware"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"pointmarket/backend/internal/store/gen"

	"github.com/gin-gonic/gin"
)

type PointsHandler struct {
	points *services.PointsService
	q      gen.Querier
}

func NewPointsHandler(points *services.PointsService, q gen.Querier) *PointsHandler {
	return &PointsHandler{points: points, q: q}
}

// GetUserStats handles GET /users/:id/stats
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
				response.Success(c, http.StatusOK, "OK", gin.H{
					"total_points": stats.TotalPoints,
					"updated_at":   stats.UpdatedAt,
				})
				return
			}
		}
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "OK", gin.H{
		"total_points": stats.TotalPoints,
		"updated_at":   stats.UpdatedAt,
	})
}

// AdjustUserStats handles POST /users/:id/stats with body { delta, reason?, reference_type?, reference_id? }
func (h *PointsHandler) AdjustUserStats(c *gin.Context) {
	idStr := c.Param("id")
	userID, err := strconv.ParseInt(idStr, 10, 64)
	if err != nil || userID <= 0 {
		response.Error(c, http.StatusBadRequest, "Invalid user ID")
		return
	}

	var req struct {
		Delta         int64   `json:"delta"`
		Reason        *string `json:"reason"`
		ReferenceType *string `json:"reference_type"`
		ReferenceID   *int64  `json:"reference_id"`
	}
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
		response.Success(c, http.StatusCreated, "Updated", gin.H{
			"total_points": total,
		})
		return
	}

	response.Success(c, http.StatusCreated, "Updated", gin.H{
		"total_points": stats.TotalPoints,
		"updated_at":   stats.UpdatedAt,
	})
}
