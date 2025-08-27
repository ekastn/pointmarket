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
}

// NewQuizHandler creates a new QuizHandler
func NewQuizHandler(quizService *services.QuizService) *QuizHandler {
	return &QuizHandler{quizService: quizService}
}

// CreateQuiz handles the creation of a new quiz
// @Summary Create a new quiz
// @Description Creates a new quiz with the provided details.
// @Tags Quizzes
// @Accept json
// @Produce json
// @Param quiz body dtos.CreateQuizRequestDTO true "Quiz details"
// @Success 201 {object} dtos.QuizDTO
// @Failure 400 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
// @Router /quizzes [post]
func (h *QuizHandler) CreateQuiz(c *gin.Context) {
	var req dtos.CreateQuizRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
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
// @Tags Quizzes
// @Produce json
// @Param id path int true "Quiz ID"
// @Success 200 {object} dtos.QuizDTO
// @Failure 404 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
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
// @Tags Quizzes
// @Produce json
// @Param course_id query int false "Filter by Course ID"
// @Success 200 {object} dtos.ListQuizzesResponseDTO
// @Failure 500 {object} dtos.ErrorResponse
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
// @Tags Quizzes
// @Accept json
// @Produce json
// @Param id path int true "Quiz ID"
// @Param quiz body dtos.UpdateQuizRequestDTO true "Updated quiz details"
// @Success 200 {object} dtos.QuizDTO
// @Failure 400 {object} dtos.ErrorResponse
// @Failure 404 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
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
// @Tags Quizzes
// @Produce json
// @Param id path int true "Quiz ID"
// @Success 204 "No Content"
// @Failure 400 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
// @Router /quizzes/{id} [delete]
func (h *QuizHandler) DeleteQuiz(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
		return
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
// @Tags Quiz Questions
// @Accept json
// @Produce json
// @Param quiz_id path int true "Quiz ID"
// @Param question body dtos.CreateQuizQuestionRequestDTO true "Quiz question details"
// @Success 201 {object} dtos.QuizQuestionDTO
// @Failure 400 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
// @Router /quizzes/{quiz_id}/questions [post]
func (h *QuizHandler) CreateQuizQuestion(c *gin.Context) {
	quizID, err := strconv.ParseInt(c.Param("quiz_id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
		return
	}

	var req dtos.CreateQuizQuestionRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}
	req.QuizID = quizID // Ensure quizID from path is used

	question, err := h.quizService.CreateQuizQuestion(c.Request.Context(), req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Question created successfully", question)
}

// GetQuizQuestionByID retrieves a quiz question by its ID
// @Summary Get quiz question by ID
// @Description Retrieves a single quiz question by its unique identifier.
// @Tags Quiz Questions
// @Produce json
// @Param quiz_id path int true "Quiz ID"
// @Param question_id path int true "Question ID"
// @Success 200 {object} dtos.QuizQuestionDTO
// @Failure 404 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
// @Router /quizzes/{quiz_id}/questions/{question_id} [get]
func (h *QuizHandler) GetQuizQuestionByID(c *gin.Context) {
	// quizID, err := strconv.ParseInt(c.Param("quiz_id"), 10, 64) // Not strictly needed for GetByID
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
// @Tags Quiz Questions
// @Produce json
// @Param quiz_id path int true "Quiz ID"
// @Success 200 {object} dtos.ListQuizQuestionsResponseDTO
// @Failure 500 {object} dtos.ErrorResponse
// @Router /quizzes/{quiz_id}/questions [get]
func (h *QuizHandler) GetQuizQuestionsByQuizID(c *gin.Context) {
	quizID, err := strconv.ParseInt(c.Param("quiz_id"), 10, 64)
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
// @Tags Quiz Questions
// @Accept json
// @Produce json
// @Param quiz_id path int true "Quiz ID"
// @Param question_id path int true "Question ID"
// @Param question body dtos.UpdateQuizQuestionRequestDTO true "Updated quiz question details"
// @Success 200 {object} dtos.QuizQuestionDTO
// @Failure 400 {object} dtos.ErrorResponse
// @Failure 404 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
// @Router /quizzes/{quiz_id}/questions/{question_id} [put]
func (h *QuizHandler) UpdateQuizQuestion(c *gin.Context) {
	// quizID, err := strconv.ParseInt(c.Param("quiz_id"), 10, 64) // Not strictly needed for UpdateByID
	// if err != nil {
	// 	response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
	// 	return
	// }

	questionID, err := strconv.ParseInt(c.Param("question_id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid question ID")
		return
	}

	var req dtos.UpdateQuizQuestionRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	question, err := h.quizService.UpdateQuizQuestion(c.Request.Context(), questionID, req)
	if err != nil {
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
// @Tags Quiz Questions
// @Produce json
// @Param quiz_id path int true "Quiz ID"
// @Param question_id path int true "Question ID"
// @Success 204 "No Content"
// @Failure 400 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
// @Router /quizzes/{quiz_id}/questions/{question_id} [delete]
func (h *QuizHandler) DeleteQuizQuestion(c *gin.Context) {
	// quizID, err := strconv.ParseInt(c.Param("quiz_id"), 10, 64) // Not strictly needed for DeleteByID
	// if err != nil {
	// 	response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
	// 	return
	// }

	questionID, err := strconv.ParseInt(c.Param("question_id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid question ID")
		return
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
// @Tags Student Quizzes
// @Accept json
// @Produce json
// @Param quiz_id path int true "Quiz ID"
// @Param studentQuiz body dtos.CreateStudentQuizRequestDTO true "Student Quiz details"
// @Success 201 {object} dtos.StudentQuizDTO
// @Failure 400 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
// @Router /quizzes/{quiz_id}/start [post]
func (h *QuizHandler) CreateStudentQuiz(c *gin.Context) {
	quizID, err := strconv.ParseInt(c.Param("quiz_id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
		return
	}

	var req dtos.CreateStudentQuizRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}
	req.QuizID = quizID // Ensure quizID from path is used

	studentQuiz, err := h.quizService.CreateStudentQuiz(c.Request.Context(), req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Student quiz created successfully", studentQuiz)
}

// GetStudentQuizByID retrieves a specific student's quiz record by ID
// @Summary Get student quiz by ID
// @Description Retrieves a specific student's quiz record by its unique identifier.
// @Tags Student Quizzes
// @Produce json
// @Param id path int true "Student Quiz ID"
// @Success 200 {object} dtos.StudentQuizDTO
// @Failure 404 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
// @Router /student-quizzes/{id} [get]
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
// @Tags Student Quizzes
// @Produce json
// @Param student_id path int true "Student ID"
// @Success 200 {object} dtos.ListStudentQuizzesResponseDTO
// @Failure 500 {object} dtos.ErrorResponse
// @Router /students/{student_id}/quizzes [get]
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
// @Summary Get student quizzes by quiz ID
// @Description Retrieves all student quiz records for a specific quiz.
// @Tags Student Quizzes
// @Produce json
// @Param quiz_id path int true "Quiz ID"
// @Success 200 {object} dtos.ListStudentQuizzesResponseDTO
// @Failure 500 {object} dtos.ErrorResponse
// @Router /quizzes/{quiz_id}/submissions [get]
func (h *QuizHandler) GetStudentQuizzesByQuizID(c *gin.Context) {
	quizID, err := strconv.ParseInt(c.Param("quiz_id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
		return
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
// @Summary Update a student's quiz record
// @Description Updates an existing student's quiz record identified by its ID.
// @Tags Student Quizzes
// @Accept json
// @Produce json
// @Param id path int true "Student Quiz ID"
// @Param studentQuiz body dtos.UpdateStudentQuizRequestDTO true "Updated student quiz details"
// @Success 200 {object} dtos.StudentQuizDTO
// @Failure 400 {object} dtos.ErrorResponse
// @Failure 404 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
// @Router /student-quizzes/{id} [put]
func (h *QuizHandler) UpdateStudentQuiz(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid student quiz ID")
		return
	}

	var req dtos.UpdateStudentQuizRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	studentQuiz, err := h.quizService.UpdateStudentQuiz(c.Request.Context(), id, req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	if studentQuiz.ID == 0 { // Assuming ID will be 0 if not found
		response.Error(c, http.StatusNotFound, "Student quiz not found")
		return
	}

	response.Success(c, http.StatusOK, "Student quiz updated successfully", studentQuiz)
}

// DeleteStudentQuiz handles the deletion of a student's quiz record
// @Summary Delete a student's quiz record
// @Description Deletes a student's quiz record by its ID.
// @Tags Student Quizzes
// @Produce json
// @Param id path int true "Student Quiz ID"
// @Success 204 "No Content"
// @Failure 400 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
// @Router /student-quizzes/{id} [delete]
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
