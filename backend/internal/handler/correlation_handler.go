package handler

import (
	"net/http"

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
	userID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User not authenticated")
		return
	}

	analysisResult, err := h.correlationService.GetCorrelationAnalysisForStudent(userID.(uint))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Correlation analysis successful", analysisResult)
}
