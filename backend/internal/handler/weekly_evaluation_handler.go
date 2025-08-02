package handler

import (
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"strconv"

	"github.com/gin-gonic/gin"
)

type WeeklyEvaluationHandler struct {
	service *services.WeeklyEvaluationService
}

func NewWeeklyEvaluationHandler(service *services.WeeklyEvaluationService) *WeeklyEvaluationHandler {
	return &WeeklyEvaluationHandler{service: service}
}

func (h *WeeklyEvaluationHandler) GetStudentEvaluationStatus(c *gin.Context) {
	statuses, err := h.service.GetStudentEvaluationStatus()
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	var statusDTOs []dtos.StudentEvaluationStatusDTO
	for _, s := range statuses {
		statusDTOs = append(statusDTOs, dtos.ToStudentEvaluationStatusDTO(s))
	}

	response.Success(c, http.StatusOK, "Student evaluation statuses retrieved successfully", statusDTOs)
}

func (h *WeeklyEvaluationHandler) GetWeeklyEvaluationOverview(c *gin.Context) {
	weeksStr := c.DefaultQuery("weeks", "4")
	weeks, err := strconv.Atoi(weeksStr)
	if err != nil || weeks <= 0 {
		response.Error(c, http.StatusBadRequest, "Invalid weeks parameter")
		return
	}

	overviews, err := h.service.GetWeeklyEvaluationOverview(weeks)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	var overviewDTOs []dtos.WeeklyEvaluationOverviewDTO
	for _, o := range overviews {
		overviewDTOs = append(overviewDTOs, dtos.ToWeeklyEvaluationOverviewDTO(o))
	}

	response.Success(c, http.StatusOK, "Weekly evaluation overview retrieved successfully", overviewDTOs)
}

func (h *WeeklyEvaluationHandler) GetWeeklyEvaluationProgressByStudentID(c *gin.Context) {
	userID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User not found in context")
		return
	}

	weeksStr := c.DefaultQuery("weeks", "8")
	weeks, err := strconv.Atoi(weeksStr)
	if err != nil || weeks <= 0 {
		response.Error(c, http.StatusBadRequest, "Invalid weeks parameter")
		return
	}

	progress, err := h.service.GetWeeklyEvaluationProgressByStudentID(int(userID.(uint)), weeks)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	var progressDTOs []dtos.WeeklyEvaluationProgressDTO
	for _, p := range progress {
		progressDTOs = append(progressDTOs, dtos.ToWeeklyEvaluationProgressDTO(p))
	}

	response.Success(c, http.StatusOK, "Weekly evaluation progress retrieved successfully", progressDTOs)
}

func (h *WeeklyEvaluationHandler) GetPendingWeeklyEvaluationsByStudentID(c *gin.Context) {
	userID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User not found in context")
		return
	}

	pendingEvaluations, err := h.service.GetPendingWeeklyEvaluationsByStudentID(int(userID.(uint)))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	var pendingDTOs []dtos.PendingWeeklyEvaluationDTO
	for _, p := range pendingEvaluations {
		pendingDTOs = append(pendingDTOs, dtos.ToPendingWeeklyEvaluationDTO(p))
	}

	response.Success(c, http.StatusOK, "Pending weekly evaluations retrieved successfully", pendingDTOs)
}
