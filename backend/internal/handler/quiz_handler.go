package handler

import (
	"net/http"
	"strconv"

	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/middleware"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"

	"github.com/gin-gonic/gin"
)

// QuizHandler handles HTTP requests for quizzes
type QuizHandler struct {
	quizService *services.QuizService
	authz       *services.AuthzService
}

// NewQuizHandler creates a new QuizHandler
func NewQuizHandler(quizService *services.QuizService, authz *services.AuthzService) *QuizHandler {
	return &QuizHandler{quizService: quizService, authz: authz}
}

// CreateQuiz handles the creation of a new quiz
// @Summary Create a new quiz
// @Description Creates a new quiz with the provided details.
// @Tags quizzes
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param quiz body dtos.CreateQuizRequestDTO true "Quiz details"
// @Success 201 {object} dtos.APIResponse{data=dtos.QuizDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /quizzes [post]
func (h *QuizHandler) CreateQuiz(c *gin.Context) {
	var req dtos.CreateQuizRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	// Authorization: admin allowed; teacher must own course
	role := middleware.GetRole(c)
	userID := middleware.GetUserID(c)
	if role == "guru" {
		if err := h.authz.CheckCourseOwner(c.Request.Context(), userID, req.CourseID); err != nil {
			if err == services.ErrForbidden {
				response.Error(c, http.StatusForbidden, "forbidden")
				return
			}
			response.Error(c, http.StatusInternalServerError, err.Error())
			return
		}
	}

	quiz, err := h.quizService.CreateQuiz(c.Request.Context(), req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Quiz created successfully", quiz)
}

// GetQuizByID retrieves a quiz by its ID
// @Summary Get quiz by ID
// @Description Retrieves a single quiz by its unique identifier.
// @Tags quizzes
// @Security BearerAuth
// @Produce json
// @Param id path int true "Quiz ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.QuizDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /quizzes/{id} [get]
func (h *QuizHandler) GetQuizByID(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
		return
	}

	quiz, err := h.quizService.GetQuizByID(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	if quiz.ID == 0 { // Assuming ID will be 0 if not found
		response.Error(c, http.StatusNotFound, "Quiz not found")
		return
	}

	response.Success(c, http.StatusOK, "Quiz retrieved successfully", quiz)
}

// GetQuizzes retrieves a list of quizzes
// @Summary Get all quizzes
// @Description Retrieves a list of all quizzes, with optional filtering by course ID.
// @Tags quizzes
// @Security BearerAuth
// @Produce json
// @Param course_id query int false "Filter by Course ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.ListQuizzesResponseDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /quizzes [get]
func (h *QuizHandler) GetQuizzes(c *gin.Context) {
	var courseIDFilter *int64
	if c.Query("course_id") != "" {
		id, err := strconv.ParseInt(c.Query("course_id"), 10, 64)
		if err != nil {
			response.Error(c, http.StatusBadRequest, "Invalid course ID filter")
			return
		}
		courseIDFilter = &id
	}

	// Get user ID and role from context (set by authentication middleware)
	userID := middleware.GetUserID(c)
	userRole := middleware.GetRole(c)

	quizzes, err := h.quizService.GetQuizzes(c.Request.Context(), userID, userRole, courseIDFilter)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Quizzes retrieved successfully", dtos.ListQuizzesResponseDTO{
		Quizzes: quizzes,
		Total:   len(quizzes),
	})
}

// UpdateQuiz handles the update of an existing quiz
// @Summary Update a quiz
// @Description Updates an existing quiz identified by its ID.
// @Tags quizzes
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param id path int true "Quiz ID"
// @Param quiz body dtos.UpdateQuizRequestDTO true "Updated quiz details"
// @Success 200 {object} dtos.APIResponse{data=dtos.QuizDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /quizzes/{id} [put]
func (h *QuizHandler) UpdateQuiz(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
		return
	}

	var req dtos.UpdateQuizRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	// Authorization: admin allowed; teacher must own quiz
	role := middleware.GetRole(c)
	userID := middleware.GetUserID(c)
	if role == "guru" {
		if err := h.authz.CheckQuizOwner(c.Request.Context(), userID, id); err != nil {
			if err == services.ErrForbidden {
				response.Error(c, http.StatusForbidden, "forbidden")
				return
			}
			response.Error(c, http.StatusInternalServerError, err.Error())
			return
		}
	}

	quiz, err := h.quizService.UpdateQuiz(c.Request.Context(), id, req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	if quiz.ID == 0 { // Assuming ID will be 0 if not found
		response.Error(c, http.StatusNotFound, "Quiz not found")
		return
	}

	response.Success(c, http.StatusOK, "Quiz updated successfully", quiz)
}

// DeleteQuiz handles the deletion of a quiz
// @Summary Delete a quiz
// @Description Deletes a quiz by its ID.
// @Tags quizzes
// @Security BearerAuth
// @Produce json
// @Param id path int true "Quiz ID"
// @Success 204 {object} dtos.APIResponse{data=dtos.NullData}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /quizzes/{id} [delete]
func (h *QuizHandler) DeleteQuiz(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
		return
	}

	// Authorization
	role := middleware.GetRole(c)
	userID := middleware.GetUserID(c)
	if role == "guru" {
		if err := h.authz.CheckQuizOwner(c.Request.Context(), userID, id); err != nil {
			if err == services.ErrForbidden {
				response.Error(c, http.StatusForbidden, "forbidden")
				return
			}
			response.Error(c, http.StatusInternalServerError, err.Error())
			return
		}
	}

	err = h.quizService.DeleteQuiz(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusNoContent, "Quiz deleted successfully", nil)
}

// CreateQuizQuestion handles the creation of a new quiz question
// @Summary Create a new quiz question
// @Description Creates a new quiz question for a specific quiz.
// @Tags quizzes
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param id path int true "Quiz ID"
// @Param question body dtos.CreateQuizQuestionRequestDTO true "Quiz question details"
// @Success 201 {object} dtos.APIResponse{data=dtos.QuizQuestionDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 409 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /quizzes/{id}/questions [post]
func (h *QuizHandler) CreateQuizQuestion(c *gin.Context) {
	// Route: /quizzes/:id/questions → use :id as quiz_id
	quizID, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
		return
	}

	// Authorization: admin or owning teacher
	role := middleware.GetRole(c)
	userID := middleware.GetUserID(c)
	if role == "guru" {
		if err := h.authz.CheckQuizOwner(c.Request.Context(), userID, quizID); err != nil {
			if err == services.ErrForbidden {
				response.Error(c, http.StatusForbidden, "forbidden")
				return
			}
			response.Error(c, http.StatusInternalServerError, err.Error())
			return
		}
	}

	var req dtos.CreateQuizQuestionRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}
	req.QuizID = quizID // Ensure quizID from path is used

	question, err := h.quizService.CreateQuizQuestion(c.Request.Context(), req)
	if err != nil {
		if err == services.ErrDuplicateOrdinal {
			response.Error(c, http.StatusConflict, "duplicate ordinal for quiz question")
			return
		}
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Question created successfully", question)
}

// GetQuizQuestionByID retrieves a quiz question by its ID
// @Summary Get quiz question by ID
// @Description Retrieves a single quiz question by its unique identifier.
// @Tags quizzes
// @Security BearerAuth
// @Produce json
// @Param id path int true "Quiz ID"
// @Param question_id path int true "Question ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.QuizQuestionDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /quizzes/{id}/questions/{question_id} [get]
func (h *QuizHandler) GetQuizQuestionByID(c *gin.Context) {
	// quizID, err := strconv.ParseInt(c.Param("id"), 10, 64) // Not strictly needed for GetByID
	// if err != nil {
	// 	response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
	// 	return
	// }

	questionID, err := strconv.ParseInt(c.Param("question_id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid question ID")
		return
	}

	question, err := h.quizService.GetQuizQuestionByID(c.Request.Context(), questionID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	if question.ID == 0 { // Assuming ID will be 0 if not found
		response.Error(c, http.StatusNotFound, "Quiz question not found")
		return
	}

	response.Success(c, http.StatusOK, "Quiz question retrieved successfully", question)
}

// GetQuizQuestionsByQuizID retrieves all questions for a specific quiz
// @Summary Get quiz questions by quiz ID
// @Description Retrieves all questions for a specific quiz.
// @Tags quizzes
// @Security BearerAuth
// @Produce json
// @Param id path int true "Quiz ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.ListQuizQuestionsResponseDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /quizzes/{id}/questions [get]
func (h *QuizHandler) GetQuizQuestionsByQuizID(c *gin.Context) {
	// Route: /quizzes/:id/questions → use :id as quiz_id
	quizID, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
		return
	}

	questions, err := h.quizService.GetQuizQuestionsByQuizID(c.Request.Context(), quizID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Quiz questions retrieved successfully", questions)
}

// UpdateQuizQuestion handles the update of an existing quiz question
// @Summary Update a quiz question
// @Description Updates an existing quiz question identified by its ID.
// @Tags quizzes
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param id path int true "Quiz ID"
// @Param question_id path int true "Question ID"
// @Param question body dtos.UpdateQuizQuestionRequestDTO true "Updated quiz question details"
// @Success 200 {object} dtos.APIResponse{data=dtos.QuizQuestionDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 409 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /quizzes/{id}/questions/{question_id} [put]
func (h *QuizHandler) UpdateQuizQuestion(c *gin.Context) {
	// quizID, err := strconv.ParseInt(c.Param("id"), 10, 64) // Not strictly needed for UpdateByID
	// if err != nil {
	// 	response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
	// 	return
	// }

	questionID, err := strconv.ParseInt(c.Param("question_id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid question ID")
		return
	}

	// Authorization: admin or owning teacher based on quiz_id
	role := middleware.GetRole(c)
	userID := middleware.GetUserID(c)
	if role == "guru" {
		quizID, _ := strconv.ParseInt(c.Param("id"), 10, 64)
		if quizID > 0 {
			if err := h.authz.CheckQuizOwner(c.Request.Context(), userID, quizID); err != nil {
				if err == services.ErrForbidden {
					response.Error(c, http.StatusForbidden, "forbidden")
					return
				}
				response.Error(c, http.StatusInternalServerError, err.Error())
				return
			}
		}
	}

	var req dtos.UpdateQuizQuestionRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	question, err := h.quizService.UpdateQuizQuestion(c.Request.Context(), questionID, req)
	if err != nil {
		if err == services.ErrDuplicateOrdinal {
			response.Error(c, http.StatusConflict, "duplicate ordinal for quiz question")
			return
		}
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	if question.ID == 0 { // Assuming ID will be 0 if not found
		response.Error(c, http.StatusNotFound, "Quiz question not found")
		return
	}

	response.Success(c, http.StatusOK, "Quiz question updated successfully", question)
}

// DeleteQuizQuestion handles the deletion of a quiz question
// @Summary Delete a quiz question
// @Description Deletes a quiz question by its ID.
// @Tags quizzes
// @Security BearerAuth
// @Produce json
// @Param id path int true "Quiz ID"
// @Param question_id path int true "Question ID"
// @Success 204 {object} dtos.APIResponse{data=dtos.NullData}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /quizzes/{id}/questions/{question_id} [delete]
func (h *QuizHandler) DeleteQuizQuestion(c *gin.Context) {
	// quizID, err := strconv.ParseInt(c.Param("id"), 10, 64) // Not strictly needed for DeleteByID
	// if err != nil {
	// 	response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
	// 	return
	// }

	questionID, err := strconv.ParseInt(c.Param("question_id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid question ID")
		return
	}

	// Authorization: admin or owning teacher based on quiz_id
	role := middleware.GetRole(c)
	userID := middleware.GetUserID(c)
	if role == "guru" {
		quizID, _ := strconv.ParseInt(c.Param("id"), 10, 64)
		if quizID > 0 {
			if err := h.authz.CheckQuizOwner(c.Request.Context(), userID, quizID); err != nil {
				if err == services.ErrForbidden {
					response.Error(c, http.StatusForbidden, "forbidden")
					return
				}
				response.Error(c, http.StatusInternalServerError, err.Error())
				return
			}
		}
	}

	err = h.quizService.DeleteQuizQuestion(c.Request.Context(), questionID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusNoContent, "Quiz question deleted successfully", nil)
}

// CreateStudentQuiz handles recording a student starting a quiz
// @Summary Record student starting a quiz
// @Description Records that a student has started a specific quiz.
// @Tags quizzes
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param id path int true "Quiz ID"
// @Param studentQuiz body dtos.CreateStudentQuizRequestDTO false "Optional payload; quiz_id and student_id are derived"
// @Success 201 {object} dtos.APIResponse{data=dtos.StudentQuizDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 409 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /quizzes/{id}/start [post]
func (h *QuizHandler) CreateStudentQuiz(c *gin.Context) {
	quizID, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
		return
	}

	var req dtos.CreateStudentQuizRequestDTO
	_ = c.ShouldBindJSON(&req)
	// Derive student from JWT; ignore any provided student_id
	req.StudentID = middleware.GetUserID(c)
	req.QuizID = quizID // Ensure quizID from path is used
	if req.Status == "" {
		req.Status = "in_progress"
	}

	studentQuiz, err := h.quizService.CreateStudentQuiz(c.Request.Context(), req)
	if err != nil {
		if err == services.ErrAlreadyStarted {
			response.Error(c, http.StatusConflict, "quiz already started")
			return
		}
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Student quiz created successfully", studentQuiz)
}

// GetStudentQuizByID retrieves a specific student's quiz record by ID.
//
// Deprecated: this handler remains for backward compatibility but is not routed
// in `cmd/api/main.go`.
func (h *QuizHandler) GetStudentQuizByID(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid student quiz ID")
		return
	}

	studentQuiz, err := h.quizService.GetStudentQuizByID(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	if studentQuiz.ID == 0 { // Assuming ID will be 0 if not found
		response.Error(c, http.StatusNotFound, "Student quiz not found")
		return
	}

	response.Success(c, http.StatusOK, "Student quiz retrieved successfully", studentQuiz)
}

// GetStudentQuizzesList retrieves a list of student quizzes for a specific student
// @Summary Get student quizzes list
// @Description Retrieves a list of all quizzes for a specific student, including their progress.
// @Tags quizzes
// @Security BearerAuth
// @Produce json
// @Param user_id path int true "User ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.ListStudentQuizzesResponseDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /students/{user_id}/quizzes [get]
func (h *QuizHandler) GetStudentQuizzesList(c *gin.Context) {
	studentID, err := strconv.ParseInt(c.Param("user_id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid student ID")
		return
	}

	studentQuizzes, err := h.quizService.GetStudentQuizzesByStudentID(c.Request.Context(), studentID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Student quizzes retrieved successfully", dtos.ListStudentQuizzesResponseDTO{
		StudentQuizzes: studentQuizzes,
		Total:          len(studentQuizzes),
	})
}

// GetStudentQuizzesByQuizID retrieves all student records for a specific quiz
// @Summary Get quiz submissions
// @Description Retrieves all student quiz records for a specific quiz.
// @Tags quizzes
// @Security BearerAuth
// @Produce json
// @Param id path int true "Quiz ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.ListStudentQuizzesResponseDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /quizzes/{id}/submissions [get]
func (h *QuizHandler) GetStudentQuizzesByQuizID(c *gin.Context) {
	quizID, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
		return
	}

	// Authorization for teacher
	role := middleware.GetRole(c)
	userID := middleware.GetUserID(c)
	if role == "guru" {
		if err := h.authz.CheckQuizOwner(c.Request.Context(), userID, quizID); err != nil {
			if err == services.ErrForbidden {
				response.Error(c, http.StatusForbidden, "forbidden")
				return
			}
			response.Error(c, http.StatusInternalServerError, err.Error())
			return
		}
	}

	studentQuizzes, err := h.quizService.GetStudentQuizzesByQuizID(c.Request.Context(), quizID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Student quizzes retrieved successfully", dtos.ListStudentQuizzesResponseDTO{
		StudentQuizzes: studentQuizzes,
		Total:          len(studentQuizzes),
	})
}

// UpdateStudentQuiz handles the update of a student's quiz record
// @Summary Submit a quiz or grade a submission
// @Description Students submit via `/quizzes/{id}/submit`; teachers/admin grade via `/quizzes/{id}/submissions/{student_quiz_id}`.
// @Tags quizzes
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param id path int true "Quiz ID"
// @Param student_quiz_id path int false "Student Quiz ID (grading route only)"
// @Param studentQuiz body dtos.UpdateStudentQuizRequestDTO true "Student quiz update"
// @Success 200 {object} dtos.APIResponse{data=dtos.StudentQuizDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /quizzes/{id}/submit [post]
// @Router /quizzes/{id}/submissions/{student_quiz_id} [put]
func (h *QuizHandler) UpdateStudentQuiz(c *gin.Context) {
	// Two route patterns call this handler:
	// 1) POST /quizzes/:id/submit            → :id is quiz_id (student self-submit)
	// 2) PUT /quizzes/:id/submissions/:student_quiz_id → grading by admin/teacher

	if sqid := c.Param("student_quiz_id"); sqid != "" {
		studentQuizID, err := strconv.ParseInt(sqid, 10, 64)
		if err != nil {
			response.Error(c, http.StatusBadRequest, "Invalid student quiz ID")
			return
		}
		var req dtos.UpdateStudentQuizRequestDTO
		if err := c.ShouldBindJSON(&req); err != nil {
			response.Error(c, http.StatusBadRequest, err.Error())
			return
		}
		studentQuiz, err := h.quizService.UpdateStudentQuiz(c.Request.Context(), studentQuizID, req)
		if err != nil {
			response.Error(c, http.StatusInternalServerError, err.Error())
			return
		}
		if studentQuiz.ID == 0 {
			response.Error(c, http.StatusNotFound, "Student quiz not found")
			return
		}
		response.Success(c, http.StatusOK, "Student quiz updated successfully", studentQuiz)
		return
	}

	// Student submit path by quiz ID
	quizID, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
		return
	}
	userID := middleware.GetUserID(c)

	var req dtos.UpdateStudentQuizRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}
	// Default to completed on submit
	completed := "completed"
	if req.Status == nil {
		req.Status = &completed
	}

	studentQuiz, err := h.quizService.SubmitOwnQuiz(c.Request.Context(), userID, quizID, req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	if studentQuiz.ID == 0 {
		response.Error(c, http.StatusNotFound, "Student quiz not found")
		return
	}
	response.Success(c, http.StatusOK, "Quiz submitted successfully", studentQuiz)
}

// DeleteStudentQuiz handles the deletion of a student's quiz record
// @Summary Delete a quiz submission
// @Description Deletes a student's quiz record by its ID.
// @Tags quizzes
// @Security BearerAuth
// @Produce json
// @Param id path int true "Quiz ID"
// @Param student_quiz_id path int true "Student Quiz ID"
// @Success 204 {object} dtos.APIResponse{data=dtos.NullData}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /quizzes/{id}/submissions/{student_quiz_id} [delete]
func (h *QuizHandler) DeleteStudentQuiz(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid student quiz ID")
		return
	}

	err = h.quizService.DeleteStudentQuiz(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusNoContent, "Student quiz deleted successfully", nil)
}
