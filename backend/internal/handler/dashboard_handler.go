package handler

import (
	"net/http"

	"github.com/gin-gonic/gin"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
)

type DashboardHandler struct {
	dashboardService services.DashboardService
}

func NewDashboardHandler(dashboardService services.DashboardService) *DashboardHandler {
	return &DashboardHandler{dashboardService: dashboardService}
}

// GetComprehensiveDashboardData handles fetching all dashboard data for the authenticated user.
func (h *DashboardHandler) GetComprehensiveDashboardData(c *gin.Context) {
	userID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User ID not found in context")
		return
	}
	userRole, exists := c.Get("role")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User role not found in context")
		return
	}

	data, err := h.dashboardService.GetComprehensiveDashboardData(c.Request.Context(), userID.(uint), userRole.(string))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to retrieve comprehensive dashboard data: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Comprehensive dashboard data retrieved successfully", data)
}
