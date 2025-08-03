package handler

import (
	"fmt"
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

	var questionnaireDTOs []dtos.QuestionnaireListDTO
	for _, q := range questionnaires {
		questionnaireDTOs = append(questionnaireDTOs, dtos.QuestionnaireListDTO{
			ID:             q.ID,
			Type:           q.Type,
			Name:           q.Name,
			Description:    q.Description,
			TotalQuestions: q.TotalQuestions,
			CreatedAt:      q.CreatedAt,
		})
	}
	response.Success(c, http.StatusOK, "Questionnaires retrieved successfully", questionnaireDTOs)
}

func (h *QuestionnaireHandler) GetQuestionnaireByID(c *gin.Context) {
	id, _ := strconv.Atoi(c.Param("id"))
	userID, _ := c.Get("userID")
	questionnaireModel, questionsDTOs, recentResultDTO, err := h.questionnaireService.GetQuestionnaireByID(uint(id), userID.(uint))
	if err != nil {
		response.Error(c, http.StatusNotFound, "Questionnaire not found")
		return
	}

	var questionnaireDTO dtos.QuestionnaireResponse
	questionnaireDTO.FromQuestionnaire(questionnaireModel, questionsDTOs)
	questionnaireDTO.RecentResult = recentResultDTO

	detailResponse := dtos.QuestionnaireDetailResponse{
		Questionnaire: questionnaireDTO,
		Questions:     questionsDTOs,
		RecentResult:  recentResultDTO,
	}

	response.Success(c, http.StatusOK, "Questionnaire retrieved successfully", detailResponse)
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

// GetQuestionnaireHistory handles fetching questionnaire history for a student
func (h *QuestionnaireHandler) GetQuestionnaireHistory(c *gin.Context) {
	userID, _ := c.Get("userID")
	results, err := h.questionnaireService.GetQuestionnaireHistoryByStudentID(userID.(uint))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	var historyResponses []dtos.QuestionnaireHistoryResponse
	for _, r := range results {
		historyResponses = append(historyResponses, dtos.QuestionnaireHistoryResponse{
			ID:                       r.ID,
			StudentID:                r.StudentID,
			QuestionnaireID:          r.QuestionnaireID,
			TotalScore:               r.TotalScore,
			CompletedAt:              r.CompletedAt,
			WeekNumber:               r.WeekNumber,
			Year:                     r.Year,
			QuestionnaireName:        r.QuestionnaireName,
			QuestionnaireType:        r.QuestionnaireType,
			QuestionnaireDescription: r.QuestionnaireDescription,
		})
	}

	response.Success(c, http.StatusOK, "Questionnaire history retrieved successfully", historyResponses)
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

// GetLatestQuestionnaireResultByType handles fetching the latest questionnaire result for a student by type
func (h *QuestionnaireHandler) GetLatestQuestionnaireResultByType(c *gin.Context) {
	userID, _ := c.Get("userID")
	qType := c.Query("type")

	if qType == "" {
		response.Error(c, http.StatusBadRequest, "Questionnaire type is required")
		return
	}

	result, err := h.questionnaireService.GetLatestQuestionnaireResultByType(userID.(uint), qType)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	if result == nil {
		response.Error(c, http.StatusNotFound, fmt.Sprintf("No %s questionnaire result found for this student", qType))
		return
	}

	response.Success(c, http.StatusOK, fmt.Sprintf("Latest %s questionnaire result retrieved successfully", qType), result)
}
