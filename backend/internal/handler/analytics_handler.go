package handler

import (
	"net/http"
	"strconv"

	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/middleware"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"

	"github.com/gin-gonic/gin"
)

var _ = dtos.APIResponse{}

// AnalyticsHandler exposes endpoints for dashboard analytics
type AnalyticsHandler struct {
	analytics *services.AnalyticsService
}

func NewAnalyticsHandler(analytics *services.AnalyticsService) *AnalyticsHandler {
	return &AnalyticsHandler{analytics: analytics}
}

// GetTeacherCourseInsights returns per-course AMS, MSLQ, and VARK averages for the logged-in teacher
// @Summary Get teacher course insights
// @Description Returns per-course AMS, MSLQ, and VARK averages for the logged-in teacher.
// @Tags analytics
// @Security BearerAuth
// @Produce json
// @Param limit query int false "Max number of courses" default(10)
// @Success 200 {object} dtos.APIResponse{data=[]dtos.CourseInsightsDTO}
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /teachers/course-insights [get]
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
