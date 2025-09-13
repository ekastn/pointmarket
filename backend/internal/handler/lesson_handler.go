package handler

import (
	"errors"
	"github.com/gin-gonic/gin"
	mysql "github.com/go-sql-driver/mysql"
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/middleware"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"pointmarket/backend/internal/store/gen"
	"strconv"
)

// LessonHandler handles lesson-related HTTP requests
type LessonHandler struct {
	lessonService *services.LessonService
	q             gen.Querier
}

func NewLessonHandler(lessonService *services.LessonService, q gen.Querier) *LessonHandler {
	return &LessonHandler{lessonService: lessonService, q: q}
}

// CreateLesson handles creating a new lesson (admin-only per routes)
func (h *LessonHandler) CreateLesson(c *gin.Context) {
	var req dtos.CreateLessonRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request body: "+err.Error())
		return
	}

	// Authorization: admin or course owner (guru)
	role := middleware.GetRole(c)
	userID := middleware.GetUserID(c)
	if role != "admin" {
		course, err := h.q.GetCourseByID(c.Request.Context(), req.CourseID)
		if err != nil {
			response.Error(c, http.StatusInternalServerError, "Failed to fetch course for authorization: "+err.Error())
			return
		}
		if course.OwnerID != userID || role != "guru" {
			response.Error(c, http.StatusForbidden, "Forbidden: Only course owner (teacher) or admin can manage lessons")
			return
		}
	}
	lesson, err := h.lessonService.CreateLesson(c.Request.Context(), req)
	if err != nil {
		var me *mysql.MySQLError
		if errors.As(err, &me) && me.Number == 1062 {
			response.Error(c, http.StatusConflict, "A lesson with the same ordinal already exists for this course")
			return
		}
		response.Error(c, http.StatusInternalServerError, "Failed to create lesson: "+err.Error())
		return
	}
	response.Success(c, http.StatusCreated, "Lesson created successfully", lesson)
}

// GetLessonByID returns a lesson by id
func (h *LessonHandler) GetLessonByID(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid lesson ID")
		return
	}
	lesson, err := h.lessonService.GetLessonByID(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	if lesson.ID == 0 {
		response.Error(c, http.StatusNotFound, "Lesson not found")
		return
	}
	response.Success(c, http.StatusOK, "Lesson retrieved successfully", lesson)
}

// GetLessons lists lessons filtered by course_id
func (h *LessonHandler) GetLessons(c *gin.Context) {
	courseIDStr := c.Query("course_id")
	if courseIDStr == "" {
		response.Error(c, http.StatusBadRequest, "course_id is required")
		return
	}
	courseID, err := strconv.ParseInt(courseIDStr, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid course_id")
		return
	}
	page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
	limit, _ := strconv.Atoi(c.DefaultQuery("limit", "20"))
	lessons, total, err := h.lessonService.GetLessonsByCourse(c.Request.Context(), courseID, page, limit)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to retrieve lessons: "+err.Error())
		return
	}
	response.Paginated(c, http.StatusOK, "Lessons retrieved successfully", lessons, total, page, limit)
}

// UpdateLesson updates a lesson
func (h *LessonHandler) UpdateLesson(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid lesson ID")
		return
	}
	var req dtos.UpdateLessonRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request body: "+err.Error())
		return
	}
	// Authorization: admin or course owner
	role := middleware.GetRole(c)
	userID := middleware.GetUserID(c)
	existing, err := h.lessonService.GetLessonByID(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	if existing.ID == 0 {
		response.Error(c, http.StatusNotFound, "Lesson not found")
		return
	}
	if role != "admin" {
		course, err := h.q.GetCourseByID(c.Request.Context(), existing.CourseID)
		if err != nil {
			response.Error(c, http.StatusInternalServerError, "Failed to fetch course for authorization: "+err.Error())
			return
		}
		if course.OwnerID != userID || role != "guru" {
			response.Error(c, http.StatusForbidden, "Forbidden: Only course owner (teacher) or admin can manage lessons")
			return
		}
	}
	lesson, err := h.lessonService.UpdateLesson(c.Request.Context(), id, req)
	if err != nil {
		var me *mysql.MySQLError
		if errors.As(err, &me) && me.Number == 1062 {
			response.Error(c, http.StatusConflict, "A lesson with the same ordinal already exists for this course")
			return
		}
		response.Error(c, http.StatusInternalServerError, "Failed to update lesson: "+err.Error())
		return
	}
	if lesson.ID == 0 {
		response.Error(c, http.StatusNotFound, "Lesson not found")
		return
	}
	response.Success(c, http.StatusOK, "Lesson updated successfully", lesson)
}

// DeleteLesson deletes a lesson
func (h *LessonHandler) DeleteLesson(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid lesson ID")
		return
	}
	// Authorization: admin or course owner
	role := middleware.GetRole(c)
	userID := middleware.GetUserID(c)
	existing, err := h.lessonService.GetLessonByID(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	if existing.ID == 0 {
		response.Error(c, http.StatusNotFound, "Lesson not found")
		return
	}
	if role != "admin" {
		course, err := h.q.GetCourseByID(c.Request.Context(), existing.CourseID)
		if err != nil {
			response.Error(c, http.StatusInternalServerError, "Failed to fetch course for authorization: "+err.Error())
			return
		}
		if course.OwnerID != userID || role != "guru" {
			response.Error(c, http.StatusForbidden, "Forbidden: Only course owner (teacher) or admin can manage lessons")
			return
		}
	}
	if err := h.lessonService.DeleteLesson(c.Request.Context(), id); err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to delete lesson: "+err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Lesson deleted successfully", nil)
}
