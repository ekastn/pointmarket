package handler

import (
	"net/http"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"strconv"

	"github.com/gin-gonic/gin"
)

// WeeklyEvaluationHandler handles requests related to weekly evaluations
type WeeklyEvaluationHandler struct {
	weeklyEvaluationService *services.WeeklyEvaluationService
}

// NewWeeklyEvaluationHandler creates a new instance of WeeklyEvaluationHandler
func NewWeeklyEvaluationHandler(weeklyEvaluationService *services.WeeklyEvaluationService) *WeeklyEvaluationHandler {
	return &WeeklyEvaluationHandler{weeklyEvaluationService: weeklyEvaluationService}
}

// GetWeeklyEvaluations handles fetching weekly evaluations for students or teachers
func (h *WeeklyEvaluationHandler) GetWeeklyEvaluations(c *gin.Context) {
	userID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User not authenticated")
		return
	}

	role, exists := c.Get("role")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User role not found")
		return
	}

	view := c.Query("view")
	studentIDParam := c.Query("student_id")
	weeksParam := c.DefaultQuery("weeks", "8")

	parsedWeeks, err := strconv.ParseInt(weeksParam, 10, 32)
	if err != nil || parsedWeeks <= 0 {
		response.Error(c, http.StatusBadRequest, "Invalid 'weeks' parameter. Must be a positive integer.")
		return
	}

	numberOfWeeks := int32(parsedWeeks)

	if role == "siswa" {
		evaluations, err := h.weeklyEvaluationService.GetWeeklyEvaluationsByStudentID(
			c.Request.Context(),
			int64(userID.(uint)),
			numberOfWeeks,
		)
		if err != nil {
			response.Error(c, http.StatusInternalServerError, err.Error())
			return
		}
		response.Success(c, http.StatusOK, "Weekly evaluations retrieved successfully", evaluations)
	} else if role == "guru" {
		if view == "monitoring" {
			dashboardData, err := h.weeklyEvaluationService.GetWeeklyEvaluationsForTeacherDashboard(
				c.Request.Context(),
				numberOfWeeks,
			)
			if err != nil {
				response.Error(c, http.StatusInternalServerError, err.Error())
				return
			}
			response.Success(c, http.StatusOK, "Teacher monitoring dashboard data retrieved successfully", dashboardData)
		} else if studentIDParam != "" {
			// Teacher viewing specific student's history
			targetStudentID, err := strconv.ParseInt(studentIDParam, 10, 64)
			if err != nil {
				response.Error(c, http.StatusBadRequest, "Invalid student ID")
				return
			}
			evaluations, err := h.weeklyEvaluationService.GetWeeklyEvaluationsByStudentID(
				c.Request.Context(),
				targetStudentID,
				numberOfWeeks,
			)
			if err != nil {
				response.Error(c, http.StatusInternalServerError, err.Error())
				return
			}
			response.Success(c, http.StatusOK, "Student weekly evaluations retrieved successfully", evaluations)
		} else {
			response.Error(c, http.StatusBadRequest, "Invalid teacher view or missing student_id")
		}
	} else {
		response.Error(c, http.StatusForbidden, "Access denied")
	}
}

// InitializeWeeklyEvaluations handles the one-time initialization of weekly evaluations
func (h *WeeklyEvaluationHandler) InitializeWeeklyEvaluations(c *gin.Context) {
	err := h.weeklyEvaluationService.InitializeWeeklyEvaluations(c.Request.Context())
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Weekly evaluations initialized successfully", nil)
}
