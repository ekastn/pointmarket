package handler

import (
    "net/http"
    "strconv"

    "github.com/gin-gonic/gin"
    "pointmarket/backend/internal/middleware"
    "pointmarket/backend/internal/response"
    "pointmarket/backend/internal/services"
)

// AnalyticsHandler exposes endpoints for dashboard analytics
type AnalyticsHandler struct {
    analytics *services.AnalyticsService
}

func NewAnalyticsHandler(analytics *services.AnalyticsService) *AnalyticsHandler {
    return &AnalyticsHandler{analytics: analytics}
}

// GetTeacherCourseInsights returns per-course AMS, MSLQ, and VARK averages for the logged-in teacher
func (h *AnalyticsHandler) GetTeacherCourseInsights(c *gin.Context) {
    role := middleware.GetRole(c)
    if role != "guru" {
        response.Error(c, http.StatusForbidden, "forbidden")
        return
    }
    userID := middleware.GetUserID(c)
    limit := int32(10)
    if l := c.Query("limit"); l != "" {
        if v, err := strconv.ParseInt(l, 10, 32); err == nil && v > 0 {
            limit = int32(v)
        }
    }
    insights, err := h.analytics.GetTeacherCourseInsights(c.Request.Context(), userID, limit)
    if err != nil {
        response.Error(c, http.StatusInternalServerError, err.Error())
        return
    }
    response.Success(c, http.StatusOK, "teacher course insights retrieved", insights)
}

