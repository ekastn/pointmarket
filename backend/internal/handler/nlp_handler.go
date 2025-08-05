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
	analysis, learningPreference, keywords, keySentences, textStats, err := h.nlpService.AnalyzeText(analyzeDTO, userID.(uint))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	// Map the analysis result to the response DTO for frontend consumption
	responseDTO := dtos.NLPAnalysisResponseDTO{
		Scores: dtos.VARKScores{
			Visual:      learningPreference.Combined.Visual,
			Aural:       learningPreference.Combined.Aural,
			ReadWrite:   learningPreference.Combined.ReadWrite,
			Kinesthetic: learningPreference.Combined.Kinesthetic,
		},
		Keywords:     keywords,
		KeySentences: keySentences,
		TextStats:    textStats,
		GrammarScore: dtos.ScoreDetail{
			Score: analysis.GrammarScore,
			Label: getScoreLabel(analysis.GrammarScore),
		},
		ReadabilityScore: dtos.ScoreDetail{
			Score: analysis.ReadabilityScore,
			Label: getScoreLabel(analysis.ReadabilityScore),
		},
		SentimentScore: dtos.ScoreDetail{
			Score: analysis.SentimentScore,
			Label: getScoreLabel(analysis.SentimentScore),
		},
		StructureScore: dtos.ScoreDetail{
			Score: analysis.StructureScore,
			Label: getScoreLabel(analysis.StructureScore),
		},
		ComplexityScore: dtos.ScoreDetail{
			Score: analysis.ComplexityScore,
			Label: getScoreLabel(analysis.ComplexityScore),
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

func (h *NLPHandler) GetLatestTextAnalysisSnapshot(c *gin.Context) {
	userID, _ := c.Get("userID")
	snapshot, err := h.nlpService.GetLatestTextAnalysisSnapshot(userID.(int))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	if snapshot == nil {
		response.Success(c, http.StatusNotFound, "No text analysis snapshot found for this student", nil)
		return
	}
	response.Success(c, http.StatusOK, "Latest text analysis snapshot retrieved successfully", snapshot)
}
