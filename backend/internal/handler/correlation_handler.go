package handler

import (
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"

	"github.com/gin-gonic/gin"
)

type CorrelationHandler struct {
	correlationService services.CorrelationService
}

func NewCorrelationHandler(correlationService services.CorrelationService) *CorrelationHandler {
	return &CorrelationHandler{correlationService: correlationService}
}

func (h *CorrelationHandler) AnalyzeCorrelation(c *gin.Context) {
	var req dtos.CorrelationAnalysisRequest // Assuming a DTO for the request body
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	// For now, we'll use dummy data or data passed directly in the request.
	// In a more complete implementation, these would be fetched from the database
	// using the studentID from the authenticated user.
	// For simplicity, let's assume req contains the necessary scores.

	analysisResult, err := h.correlationService.AnalyzeAndRecommend(req.VARKScores, req.MSLQScore, req.AMSScore)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Correlation analysis successful", analysisResult)
}
