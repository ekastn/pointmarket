package handler

import (
	"database/sql"
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"strconv"

	"github.com/gin-gonic/gin"
)

type StudentHandler struct {
	studentService *services.StudentService
}

func NewStudentHandler(studentService *services.StudentService) *StudentHandler {
	return &StudentHandler{studentService: studentService}
}

// GetPrograms godoc
// @Summary List academic programs
// @Description Lists all academic programs (auth required)
// @Tags programs
// @Produce json
// @Security BearerAuth
// @Success 200 {object} dtos.APIResponse{data=[]dtos.ProgramDTO}
// @Failure 500 {object} dtos.APIError
// @Router /programs [get]
//
// GetPrograms lists all academic programs
func (h *StudentHandler) GetPrograms(c *gin.Context) {
	programs, err := h.studentService.ListPrograms(c.Request.Context())
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Programs retrieved successfully", programs)
}

// GetStudentByUserID godoc
// @Summary Get student by user ID
// @Description Fetches a student record by user ID (admin-only)
// @Tags students
// @Produce json
// @Security BearerAuth
// @Param user_id path int true "User ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.StudentDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /students/{user_id} [get]
func (h *StudentHandler) GetStudentByUserID(c *gin.Context) {
	uidStr := c.Param("user_id")
	uid, err := strconv.ParseInt(uidStr, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid user_id")
		return
	}
	st, err := h.studentService.GetByUserID(c.Request.Context(), uid)
	if err != nil {
		if err == sql.ErrNoRows {
			response.Error(c, http.StatusNotFound, "Student not found")
			return
		}
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Student retrieved successfully", st)
}

// UpsertStudentByUserID godoc
// @Summary Create or update student
// @Description Creates or updates a student record for the given user ID (admin-only)
// @Tags students
// @Accept json
// @Produce json
// @Security BearerAuth
// @Param user_id path int true "User ID"
// @Param request body dtos.UpsertStudentRequest true "Student payload"
// @Success 200 {object} dtos.APIResponse
// @Failure 400 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /students/{user_id} [put]
func (h *StudentHandler) UpsertStudentByUserID(c *gin.Context) {
	uidStr := c.Param("user_id")
	uid, err := strconv.ParseInt(uidStr, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid user_id")
		return
	}
	var req dtos.UpsertStudentRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}
	if err := h.studentService.Upsert(c.Request.Context(), uid, req); err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Student saved successfully", nil)
}

// SearchStudents godoc
// @Summary Search students
// @Description Lists students with filters and pagination (admin-only)
// @Tags students
// @Produce json
// @Security BearerAuth
// @Param search query string false "Search query"
// @Param status query string false "Student status"
// @Param program_id query int false "Program ID"
// @Param cohort_year query int false "Cohort year"
// @Param page query int false "Page" default(1)
// @Param limit query int false "Limit" default(10)
// @Success 200 {object} dtos.PaginatedResponse{data=[]dtos.StudentListItem}
// @Failure 500 {object} dtos.APIError
// @Router /students [get]
func (h *StudentHandler) SearchStudents(c *gin.Context) {
	var req dtos.StudentSearchRequest
	// Defaults
	req.Search = c.DefaultQuery("search", "")
	req.Status = c.DefaultQuery("status", "")
	if v := c.Query("program_id"); v != "" {
		if id, err := strconv.ParseInt(v, 10, 64); err == nil {
			req.ProgramID = &id
		}
	}
	if v := c.Query("cohort_year"); v != "" {
		if yr, err := strconv.ParseInt(v, 10, 32); err == nil {
			y := int32(yr)
			req.CohortYear = &y
		}
	}
	if p, err := strconv.Atoi(c.DefaultQuery("page", "1")); err == nil {
		req.Page = p
	} else {
		req.Page = 1
	}
	if l, err := strconv.Atoi(c.DefaultQuery("limit", "10")); err == nil {
		req.Limit = l
	} else {
		req.Limit = 10
	}

	list, total, err := h.studentService.Search(c.Request.Context(), req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Paginated(c, http.StatusOK, "Students retrieved successfully", list, total, req.Page, req.Limit)
}

// GetStudentDetailsByUserID godoc
// @Summary Get student details
// @Description Fetches detailed student info, including latest questionnaire results (admin-only)
// @Tags students
// @Produce json
// @Security BearerAuth
// @Param user_id path int true "User ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.StudentDetailsDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /students/{user_id}/details [get]
func (h *StudentHandler) GetStudentDetailsByUserID(c *gin.Context) {
	uidStr := c.Param("user_id")
	uid, err := strconv.ParseInt(uidStr, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid user ID")
		return
	}

	studentDetails, err := h.studentService.GetStudentDetailsByUserID(c.Request.Context(), uid)
	if err != nil {
		if err == sql.ErrNoRows {
			response.Error(c, http.StatusNotFound, "Student details not found")
			return
		}
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Student details retrieved successfully", studentDetails)
}
