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
	analysis, learningPreference, err := h.nlpService.AnalyzeText(analyzeDTO, userID.(uint))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	// Map the analysis result to the new DTO for frontend consumption
	responseDTO := dtos.NLPAnalysisResponseDTO{
		Sentiment: dtos.ScoreDetail{
			Score: analysis.SentimentScore,
			Label: getScoreLabel(analysis.SentimentScore),
		},
		Complexity: dtos.ScoreDetail{
			Score: analysis.ComplexityScore,
			Label: getScoreLabel(analysis.ComplexityScore),
		},
		Coherence: dtos.ScoreDetail{
			Score: analysis.StructureScore, // Using structure score as coherence for now
			Label: getScoreLabel(analysis.StructureScore),
		},
		Keywords:     []string{}, // Placeholder, service does not provide yet
		KeySentences: []string{}, // Placeholder, service does not provide yet
		Stats: dtos.TextStats{
			WordCount:     analysis.WordCount,
			SentenceCount: analysis.SentenceCount,
			AvgWordLength: 0.0, // Placeholder, service does not provide yet
			ReadingTime:   0,   // Placeholder, service does not provide yet
		},
		LearningPreference: learningPreference,
	}

	response.Success(c, http.StatusOK, "Text analyzed successfully", responseDTO)
}

// getScoreLabel returns a label based on the score value
func getScoreLabel(score float64) string {
	if score >= 70 {
		return "High"
	} else if score >= 40 {
		return "Medium"
	}
	return "Low"
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