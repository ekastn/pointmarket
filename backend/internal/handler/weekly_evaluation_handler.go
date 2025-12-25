package handler

import (
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/middleware"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"strconv"

	"github.com/gin-gonic/gin"
)

var _ = dtos.APIResponse{}

// WeeklyEvaluationHandler handles requests related to weekly evaluations
type WeeklyEvaluationHandler struct {
	weeklyEvaluationService *services.WeeklyEvaluationService
	scheduler               *services.SchedulerManager
}

// NewWeeklyEvaluationHandler creates a new instance of WeeklyEvaluationHandler
func NewWeeklyEvaluationHandler(weeklyEvaluationService *services.WeeklyEvaluationService, scheduler *services.SchedulerManager) *WeeklyEvaluationHandler {
	return &WeeklyEvaluationHandler{weeklyEvaluationService: weeklyEvaluationService, scheduler: scheduler}
}

// GetWeeklyEvaluations godoc
// @Summary List weekly evaluations
// @Description Returns weekly evaluations for the current user; teachers can query monitoring view
// @Tags weekly-evaluations
// @Produce json
// @Security BearerAuth
// @Param weeks query int false "Number of weeks" default(8)
// @Param view query string false "Teacher view" Enums(monitoring)
// @Param student_id query int false "Target student user ID (teacher only)"
// @Success 200 {object} dtos.APIResponse{data=[]dtos.WeeklyEvaluationDetailDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /weekly-evaluations [get]
//
// GetWeeklyEvaluations handles fetching weekly evaluations for students or teachers
func (h *WeeklyEvaluationHandler) GetWeeklyEvaluations(c *gin.Context) {
	userID := middleware.GetUserID(c)
	role := middleware.GetRole(c)

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
			userID,
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

// InitializeWeeklyEvaluations godoc
// @Summary Initialize weekly evaluations
// @Description Initializes weekly evaluation rows (admin-only)
// @Tags weekly-evaluations
// @Produce json
// @Security BearerAuth
// @Success 200 {object} dtos.APIResponse
// @Failure 500 {object} dtos.APIError
// @Router /weekly-evaluations/initialize [post]
//
// InitializeWeeklyEvaluations handles the one-time initialization of weekly evaluations
func (h *WeeklyEvaluationHandler) InitializeWeeklyEvaluations(c *gin.Context) {
	err := h.weeklyEvaluationService.InitializeWeeklyEvaluations(c.Request.Context())
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Weekly evaluations initialized successfully", nil)
}

// SchedulerStatus godoc
// @Summary Scheduler status
// @Description Returns current scheduler status (admin-only)
// @Tags weekly-evaluations
// @Produce json
// @Security BearerAuth
// @Success 200 {object} dtos.APIResponse{data=services.SchedulerStatusDTO}
// @Failure 500 {object} dtos.APIError
// @Router /weekly-evaluations/status [get]
//
// SchedulerStatus returns current scheduler status (admin only)
func (h *WeeklyEvaluationHandler) SchedulerStatus(c *gin.Context) {
	st := h.scheduler.Status()
	response.Success(c, http.StatusOK, "Scheduler status", st)
}

// SchedulerStart godoc
// @Summary Scheduler start
// @Description Starts the in-process scheduler loop (admin-only)
// @Tags weekly-evaluations
// @Produce json
// @Security BearerAuth
// @Success 200 {object} dtos.APIResponse{data=dtos.SchedulerNextRunDTO}
// @Failure 500 {object} dtos.APIError
// @Router /weekly-evaluations/start [post]
//
// SchedulerStart starts the in-process scheduler loop (admin only)
func (h *WeeklyEvaluationHandler) SchedulerStart(c *gin.Context) {
	if err := h.scheduler.Start(); err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	st := h.scheduler.Status()
	response.Success(c, http.StatusOK, "Scheduler started", dtos.SchedulerNextRunDTO{NextRun: st.NextRun})
}

// SchedulerStop godoc
// @Summary Scheduler stop
// @Description Stops the in-process scheduler loop (admin-only)
// @Tags weekly-evaluations
// @Produce json
// @Security BearerAuth
// @Success 200 {object} dtos.APIResponse
// @Failure 500 {object} dtos.APIError
// @Router /weekly-evaluations/stop [post]
//
// SchedulerStop stops the in-process scheduler loop (admin only)
func (h *WeeklyEvaluationHandler) SchedulerStop(c *gin.Context) {
	if err := h.scheduler.Stop(); err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Scheduler stopped", nil)
}
