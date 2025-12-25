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

// PredictText analyzes free-form student text.
// @Summary Analyze text
// @Description Runs text analysis and returns keywords, key sentences, learning style, and text stats.
// @Tags text-analyzer
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param request body dtos.TextAnalyzerRequest true "Text to analyze"
// @Success 201 {object} dtos.APIResponse{data=dtos.TextAnalyzerResponse}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /text-analyzer [post]
func (h *TextAnalysisHandler) PredictText(c *gin.Context) {
	var req dtos.TextAnalyzerRequest

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

	resp := dtos.TextAnalyzerResponse{
		Keywords:      keywords,
		KeySentences:  sentences,
		LearningStyle: dtos.StudentLearningStyle{Type: row.LearningPreferenceType, Label: row.LearningPreferenceLabel, Scores: *fusedScores},
		TextStats: dtos.TextAnalyzerTextStats{
			WordCount:         row.CountWords,
			SentenceCount:     row.CountSentences,
			AverageWordLength: row.AverageWordLength,
			ReadingTime:       row.ReadingTime,
			GrammarScore:      row.ScoreGrammar,
			ReadabilityScore:  row.ScoreReadability,
			SentimentScore:    row.ScoreSentiment,
			StructureScore:    row.ScoreStructure,
			ComplexityScore:   row.ScoreComplexity,
		},
	}

	response.Success(c, http.StatusCreated, "Text analyzed successfully", resp)
}
