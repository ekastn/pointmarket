package handler

import (
	"net/http"

	"pointmarket/backend/internal/config"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/middleware"
	"pointmarket/backend/internal/response"

	"github.com/gin-gonic/gin"
)

// ScoringHandler handles scoring-related HTTP requests
type ScoringHandler struct{}

// NewScoringHandler creates a new ScoringHandler
func NewScoringHandler() *ScoringHandler {
	return &ScoringHandler{}
}

// GetMultimodalThreshold handles getting the multimodal threshold
// @Summary Get multimodal threshold
// @Description Returns current multimodal threshold.
// @Tags scorings
// @Security BearerAuth
// @Produce json
// @Success 200 {object} dtos.APIResponse{data=dtos.MultimodalThresholdResponse}
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /scorings/multimodal [get]
func (h *ScoringHandler) GetMultimodalThreshold(c *gin.Context) {
	threshold := config.GetMultimodalThreshold()
	response.Success(c, http.StatusOK, "Multimodal threshold", dtos.MultimodalThresholdResponse{Threshold: threshold})
}

// UpdateMultimodalThreshold handles updating the multimodal threshold
// @Summary Update multimodal threshold
// @Description Updates current multimodal threshold.
// @Tags scorings
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param request body dtos.MultimodalThresholdRequest true "Threshold"
// @Success 200 {object} dtos.APIResponse{data=dtos.NullData}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /scorings/multimodal [put]
func (h *ScoringHandler) UpdateMultimodalThreshold(c *gin.Context) {
	var req dtos.MultimodalThresholdRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	role := middleware.GetRole(c)

	if role != "admin" {
		response.Error(c, http.StatusForbidden, "forbidden")
		return
	}

	config.SetMultimodalThreshold(req.Threshold)

	response.Success(c, http.StatusOK, "Multimodal threshold updated successfully", nil)
}
