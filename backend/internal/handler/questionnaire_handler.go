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

func (h *QuestionnaireHandler) GetQuestionnaireByID(c *gin.Context) {
	id, _ := strconv.Atoi(c.Param("id"))
	questionnaire, questions, err := h.questionnaireService.GetQuestionnaireByID(uint(id))
	if err != nil {
		response.Error(c, http.StatusNotFound, "Questionnaire not found")
		return
	}
	var questionnaireDTO dtos.QuestionnaireResponse
	questionnaireDTO.FromQuestionnaire(questionnaire, questions)
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
	response.Success(c, http.StatusCreated, "Questionnaire submitted successfully", result)
}
