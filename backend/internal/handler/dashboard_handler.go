package handler

import (
	"net/http"

	"pointmarket/backend/internal/middleware"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"

	"github.com/gin-gonic/gin"
)

type DashboardHandler struct {
	dashboardService services.DashboardService
}

func NewDashboardHandler(dashboardService services.DashboardService) *DashboardHandler {
	return &DashboardHandler{dashboardService: dashboardService}
}

// GetDashboardData handles fetching all dashboard data for the authenticated user.
func (h *DashboardHandler) GetDashboardData(c *gin.Context) {
	userID := middleware.GetUserID(c)
	userRole := middleware.GetRole(c)

	data, err := h.dashboardService.GetDashboardData(c.Request.Context(), userID, userRole)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to retrieve dashboard data: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "dashboard data retrieved successfully", data)
}
