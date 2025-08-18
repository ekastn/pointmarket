package handler

import (
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/middleware"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"

	"github.com/gin-gonic/gin"
)

type TextAnalysisHandler struct {
	textAnalyzerService *services.TextAnalyzerService
}

func NewTextAnalysisHandler(textAnalyzerService *services.TextAnalyzerService) *TextAnalysisHandler {
	return &TextAnalysisHandler{
		textAnalyzerService: textAnalyzerService,
	}
}

func (h *TextAnalysisHandler) PredictText(c *gin.Context) {
	var req struct {
		Text string `json:"text"`
	}

	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	userID := middleware.GetUserID(c)
	row, fusedScores, keywords, sentences, err := h.textAnalyzerService.Predict(
		c.Request.Context(),
		req.Text,
		userID,
		dtos.VARKScores{},
	)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	resp := gin.H{
		"keywords":      keywords,
		"key_sentences": sentences,
		"learning_style": dtos.StudentLearningStyle{
			Type:   row.LearningPreferenceType,
			Label:  row.LearningPreferenceLabel,
			Scores: *fusedScores,
		},
		"text_stats": gin.H{
			"word_count":          row.CountWords,
			"sentence_count":      row.CountSentences,
			"average_word_length": row.AverageWordLength,
			"reading_time":        row.ReadingTime,
			"grammar_score":       row.ScoreGrammar,
			"readability_score":   row.ScoreReadability,
			"sentiment_score":     row.ScoreSentiment,
			"structure_score":     row.ScoreSentiment,
			"complexity_score":    row.ScoreComplexity,
		},
	}

	response.Success(c, http.StatusCreated, "Questionnaire submitted successfully", resp)
}
