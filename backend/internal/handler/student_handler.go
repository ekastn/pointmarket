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

// GetPrograms lists all academic programs
func (h *StudentHandler) GetPrograms(c *gin.Context) {
	programs, err := h.studentService.ListPrograms(c.Request.Context())
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Programs retrieved successfully", programs)
}

// GetStudentByUserID fetches student info by user id
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

// UpsertStudentByUserID creates or updates a student record for the given user
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

// SearchStudents lists students with filters and pagination
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

// GetStudentDetailsByUserID fetches detailed student info by user ID, including questionnaire results.
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
