package handler

import (
	"net/http"
	"pointmarket/backend/internal/config"
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

// multimodalThresholdResponse represents the request body for updating the multimodal threshold
type multimodalThresholdResponse struct {
	Threshold float64 `json:"threshold" binding:"required"`
}

// GetMultimodalThreshold handles getting the multimodal threshold
func (h *ScoringHandler) GetMultimodalThreshold(c *gin.Context) {
	threshold := config.GetMultimodalThreshold()
	response.Success(c, http.StatusOK, "Multimodal threshold", multimodalThresholdResponse{Threshold: threshold})
}

// UpdateMultimodalThreshold handles updating the multimodal threshold
func (h *ScoringHandler) UpdateMultimodalThreshold(c *gin.Context) {
	var req multimodalThresholdResponse
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	role := middleware.GetRole(c)

	if role != "admin" {
		response.Error(c, http.StatusUnauthorized, "Unauthorized")
		return
	}

	config.SetLikeThreshold(int(req.Threshold))

	response.Success(c, http.StatusOK, "Multimodal threshold updated successfully", nil)
}
