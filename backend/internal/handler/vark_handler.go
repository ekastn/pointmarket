package handler

import (
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/middleware"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"

	"github.com/gin-gonic/gin"
)

type VARKHandler struct {
	varkService services.VARKService
	nlpService  services.NLPService // Add nlpService field
}

// NewVARKHandler creates a new VARKHandler
func NewVARKHandler(varkService services.VARKService, nlpService services.NLPService) *VARKHandler {
	return &VARKHandler{varkService: varkService, nlpService: nlpService} // Initialize nlpService
}

func (h *VARKHandler) GetVARKQuestions(c *gin.Context) {
	questionnaire, questions, err := h.varkService.GetVARKQuestions()
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	var varkDTO dtos.QuestionnaireResponse
	varkDTO.FromQuestionnaire(questionnaire, questions)
	response.Success(c, http.StatusOK, "VARK questions retrieved successfully", varkDTO)
}

func (h *VARKHandler) SubmitVARK(c *gin.Context) {
	var submitDTO dtos.SubmitVARKRequest
	if err := c.ShouldBindJSON(&submitDTO); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	userID := middleware.GetUserID(c)
	result, nlpLearningPreference, nlpKeywords, nlpKeySentences, nlpTextStats, grammarScore, readabilityScore, sentimentScore, structureScore, complexityScore, err := h.varkService.SubmitVARK(submitDTO, userID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	var varkResultDTO dtos.VARKResultResponse
	varkResultDTO.FromVARKResult(result)

	combinedResponse := dtos.VARKNLPCombinedResponse{
		VARKResultResponse: varkResultDTO,
		LearningPreference: nlpLearningPreference,
		Keywords:           nlpKeywords,
		KeySentences:       nlpKeySentences,
		TextStats:          nlpTextStats,
		GrammarScore: dtos.ScoreDetail{
			Score: grammarScore,
			Label: getScoreLabel(grammarScore),
		},
		ReadabilityScore: dtos.ScoreDetail{
			Score: readabilityScore,
			Label: getScoreLabel(readabilityScore),
		},
		SentimentScore: dtos.ScoreDetail{
			Score: sentimentScore,
			Label: getScoreLabel(sentimentScore),
		},
		StructureScore: dtos.ScoreDetail{
			Score: structureScore,
			Label: getScoreLabel(structureScore),
		},
		ComplexityScore: dtos.ScoreDetail{
			Score: complexityScore,
			Label: getScoreLabel(complexityScore),
		},
	}

	response.Success(c, http.StatusCreated, "VARK assessment submitted successfully", combinedResponse)
}

func (h *VARKHandler) GetLatestVARKResult(c *gin.Context) {
	userID := middleware.GetUserID(c)
	result, err := h.varkService.GetLatestVARKResult(userID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	if result == nil {
		response.Error(c, http.StatusNotFound, "No VARK result found for this student")
		return
	}
	var resultDTO dtos.VARKResultResponse
	resultDTO.FromVARKResult(*result)
	response.Success(c, http.StatusOK, "Latest VARK result retrieved successfully", resultDTO)
}
