package handler

import (
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"

	"github.com/gin-gonic/gin"
)

type VARKHandler struct {
	varkService services.VARKService
}

func NewVARKHandler(varkService services.VARKService) *VARKHandler {
	return &VARKHandler{varkService: varkService}
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

	userID, _ := c.Get("userID")
	result, err := h.varkService.SubmitVARK(submitDTO, userID.(uint))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	var resultDTO dtos.VARKResultResponse
	resultDTO.FromVARKResult(result)
	response.Success(c, http.StatusCreated, "VARK assessment submitted successfully", resultDTO)
}

func (h *VARKHandler) GetLatestVARKResult(c *gin.Context) {
	userID, _ := c.Get("userID")
	result, err := h.varkService.GetLatestVARKResult(userID.(uint))
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