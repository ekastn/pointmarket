package handler

import (
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"strconv"

	"github.com/gin-gonic/gin"
)

type QuestionnaireHandler struct {
	questionnaireService services.QuestionnaireService
}

func NewQuestionnaireHandler(questionnaireService services.QuestionnaireService) *QuestionnaireHandler {
	return &QuestionnaireHandler{questionnaireService: questionnaireService}
}

func (h *QuestionnaireHandler) GetAllQuestionnaires(c *gin.Context) {
	questionnaires, err := h.questionnaireService.GetAllQuestionnaires()
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Questionnaires retrieved successfully", questionnaires)
}

func (h *QuestionnaireHandler) GetQuestionnaireByID(c *gin.Context) {
	id, _ := strconv.Atoi(c.Param("id"))
	userID, _ := c.Get("userID")
	questionnaire, questions, recentResult, err := h.questionnaireService.GetQuestionnaireByID(uint(id), userID.(uint))
	if err != nil {
		response.Error(c, http.StatusNotFound, "Questionnaire not found")
		return
	}
	var questionnaireDTO dtos.QuestionnaireResponse
	questionnaireDTO.FromQuestionnaire(questionnaire, questions)
	questionnaireDTO.RecentResult = recentResult
	response.Success(c, http.StatusOK, "Questionnaire retrieved successfully", questionnaireDTO)
}

func (h *QuestionnaireHandler) SubmitQuestionnaire(c *gin.Context) {
	var submitDTO dtos.SubmitQuestionnaireRequest
	if err := c.ShouldBindJSON(&submitDTO); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	userID, _ := c.Get("userID")
	result, err := h.questionnaireService.SubmitQuestionnaire(submitDTO, userID.(uint))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusCreated, "Questionnaire submitted successfully", gin.H{"total_score": result.TotalScore})
}

func (h *QuestionnaireHandler) GetQuestionnaireHistory(c *gin.Context) {
	userID, _ := c.Get("userID")
	history, err := h.questionnaireService.GetQuestionnaireHistoryByStudentID(userID.(uint))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Questionnaire history retrieved successfully", history)
}

func (h *QuestionnaireHandler) GetQuestionnaireStats(c *gin.Context) {
	userID, _ := c.Get("userID")
	stats, err := h.questionnaireService.GetQuestionnaireStatsByStudentID(userID.(uint))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Questionnaire statistics retrieved successfully", stats)
}
