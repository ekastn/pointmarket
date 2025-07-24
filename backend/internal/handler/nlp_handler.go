package handler

import (
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"

	"github.com/gin-gonic/gin"
)

type NLPHandler struct {
	nlpService services.NLPService
}

func NewNLPHandler(nlpService services.NLPService) *NLPHandler {
	return &NLPHandler{nlpService: nlpService}
}

func (h *NLPHandler) AnalyzeText(c *gin.Context) {
	var analyzeDTO dtos.AnalyzeNLPRequest
	if err := c.ShouldBindJSON(&analyzeDTO); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	userID, _ := c.Get("userID")
	analysis, err := h.nlpService.AnalyzeText(analyzeDTO, userID.(uint))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	var analysisDTO dtos.AnalyzeNLPResponse
	analysisDTO.FromNLPAnalysis(analysis)
	response.Success(c, http.StatusOK, "Text analyzed successfully", analysisDTO)
}

func (h *NLPHandler) GetNLPStats(c *gin.Context) {
	userID, _ := c.Get("userID")
	stats, err := h.nlpService.GetNLPStats(userID.(uint))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	if stats == nil {
		response.Success(c, http.StatusOK, "No NLP statistics found for this student", nil)
		return
	}
	var statsDTO dtos.NLPStatsResponse
	statsDTO.FromNLPStats(*stats)
	response.Success(c, http.StatusOK, "NLP statistics retrieved successfully", statsDTO)
}