package handler

import (
	"encoding/json"
	"net/http"
	"strconv"

	"github.com/gin-gonic/gin"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
)

// Handler struct holds references to services
type Handler struct {
	authService      *services.AuthService
	assignmentService *services.AssignmentService
	quizService      *services.QuizService
	questionnaireService *services.QuestionnaireService
	varkService      *services.VARKService
	nlpService       *services.NLPService
}

// NewHandler creates a new Handler instance
func NewHandler(authService *services.AuthService, assignmentService *services.AssignmentService, quizService *services.QuizService, questionnaireService *services.QuestionnaireService, varkService *services.VARKService, nlpService *services.NLPService) *Handler {
	return &Handler{authService: authService, assignmentService: assignmentService, quizService: quizService, questionnaireService: questionnaireService, varkService: varkService, nlpService: nlpService}
}

// Login handles user login and returns a JWT token
func (h *Handler) Login(c *gin.Context) {
	var req dtos.LoginRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request payload")
		return
	}

	token, err := h.authService.Login(&req)
	if err != nil {
		response.Error(c, http.StatusUnauthorized, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Login successful", dtos.LoginResponse{Token: token})
}

// GetUserProfile handles fetching the authenticated user's profile
func (h *Handler) GetUserProfile(c *gin.Context) {
	username, exists := c.Get("username")
	if !exists {
		response.Error(c, http.StatusInternalServerError, "Username not found in context")
		return
	}

	user, err := h.authService.GetUserProfile(username.(string))
	if err != nil {
		response.Error(c, http.StatusNotFound, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "User profile fetched successfully", user)
}

// CreateAssignment handles the creation of a new assignment
func (h *Handler) CreateAssignment(c *gin.Context) {
	var req dtos.AssignmentRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request payload")
		return
	}

	assignment := &models.Assignment{
		Title:       req.Title,
		Description: req.Description,
		Subject:     req.Subject,
		TeacherID:   req.TeacherID,
		Points:      req.Points,
		DueDate:     req.DueDate,
		Status:      req.Status,
	}

	if err := h.assignmentService.CreateAssignment(assignment); err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Assignment created successfully", assignment)
}

// GetAssignment handles fetching an assignment by ID
func (h *Handler) GetAssignment(c *gin.Context) {
	id, err := strconv.Atoi(c.Param("id"))
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid assignment ID")
		return
	}

	assignment, err := h.assignmentService.GetAssignmentByID(id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	if assignment == nil {
		response.Error(c, http.StatusNotFound, "Assignment not found")
		return
	}

	response.Success(c, http.StatusOK, "Assignment fetched successfully", assignment)
}

// UpdateAssignment handles updating an existing assignment
func (h *Handler) UpdateAssignment(c *gin.Context) {
	id, err := strconv.Atoi(c.Param("id"))
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid assignment ID")
		return
	}

	var req dtos.AssignmentRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request payload")
		return
	}

	assignment := &models.Assignment{
		ID:          id,
		Title:       req.Title,
		Description: req.Description,
		Subject:     req.Subject,
		TeacherID:   req.TeacherID,
		Points:      req.Points,
		DueDate:     req.DueDate,
		Status:      req.Status,
	}

	if err := h.assignmentService.UpdateAssignment(assignment); err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Assignment updated successfully", assignment)
}

// DeleteAssignment handles deleting an assignment by ID
func (h *Handler) DeleteAssignment(c *gin.Context) {
	id, err := strconv.Atoi(c.Param("id"))
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid assignment ID")
		return
	}

	if err := h.assignmentService.DeleteAssignment(id); err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return	
	}

	response.Success(c, http.StatusOK, "Assignment deleted successfully", nil)
}

// ListAssignments handles listing all assignments, optionally filtered by teacher ID
func (h *Handler) ListAssignments(c *gin.Context) {
	var teacherID *int
	teacherIDStr := c.Query("teacher_id")
	if teacherIDStr != "" {
		id, err := strconv.Atoi(teacherIDStr)
		if err != nil {
			response.Error(c, http.StatusBadRequest, "Invalid teacher ID")
			return
		}
		teacherID = &id
	}

	assignments, err := h.assignmentService.ListAssignments(teacherID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Assignments fetched successfully", assignments)
}

// CreateQuiz handles the creation of a new quiz
func (h *Handler) CreateQuiz(c *gin.Context) {
	var req dtos.QuizRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request payload")
		return
	}

	quiz := &models.Quiz{
		Title:       req.Title,
		Description: req.Description,
		Subject:     req.Subject,
		TeacherID:   req.TeacherID,
		Points:      req.Points,
		Duration:    req.Duration,
		Status:      req.Status,
	}

	if err := h.quizService.CreateQuiz(quiz); err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Quiz created successfully", quiz)
}

// GetQuiz handles fetching a quiz by ID
func (h *Handler) GetQuiz(c *gin.Context) {
	id, err := strconv.Atoi(c.Param("id"))
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
		return
	}

	quiz, err := h.quizService.GetQuizByID(id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	if quiz == nil {
		response.Error(c, http.StatusNotFound, "Quiz not found")
		return
	}

	response.Success(c, http.StatusOK, "Quiz fetched successfully", quiz)
}

// UpdateQuiz handles updating an existing quiz
func (h *Handler) UpdateQuiz(c *gin.Context) {
	id, err := strconv.Atoi(c.Param("id"))
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
		return
	}

	var req dtos.QuizRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request payload")
		return
	}

	quiz := &models.Quiz{
		ID:          id,
		Title:       req.Title,
		Description: req.Description,
		Subject:     req.Subject,
		TeacherID:   req.TeacherID,
		Points:      req.Points,
		Duration:    req.Duration,
		Status:      req.Status,
	}

	if err := h.quizService.UpdateQuiz(quiz); err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Quiz updated successfully", quiz)
}

// DeleteQuiz handles deleting a quiz by ID
func (h *Handler) DeleteQuiz(c *gin.Context) {
	id, err := strconv.Atoi(c.Param("id"))
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid quiz ID")
		return
	}

	if err := h.quizService.DeleteQuiz(id); err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Quiz deleted successfully", nil)
}

// ListQuizzes handles listing all quizzes, optionally filtered by teacher ID
func (h *Handler) ListQuizzes(c *gin.Context) {
	var teacherID *int
	teacherIDStr := c.Query("teacher_id")
	if teacherIDStr != "" {
		id, err := strconv.Atoi(teacherIDStr)
		if err != nil {
			response.Error(c, http.StatusBadRequest, "Invalid teacher ID")
			return
		}
		teacherID = &id
	}

	quizzes, err := h.quizService.ListQuizzes(teacherID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Quizzes fetched successfully", quizzes)
}

// GetQuestionnaire handles fetching a questionnaire by ID with its questions
func (h *Handler) GetQuestionnaire(c *gin.Context) {
	id, err := strconv.Atoi(c.Param("id"))
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid questionnaire ID")
		return
	}

	questionnaire, questions, err := h.questionnaireService.GetQuestionnaire(id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Questionnaire fetched successfully", gin.H{
		"questionnaire": questionnaire,
		"questions":     questions,
	})
}

// SubmitQuestionnaire handles submitting a student's questionnaire answers
func (h *Handler) SubmitQuestionnaire(c *gin.Context) {
	studentID, exists := c.Get("user_id") // Assuming user_id is set in context by auth middleware
	if !exists {
		response.Error(c, http.StatusInternalServerError, "User ID not found in context")
		return
	}

	var req dtos.SubmitQuestionnaireRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request payload")
		return
	}

	err := h.questionnaireService.SubmitQuestionnaireResult(studentID.(int), req.QuestionnaireID, req.Answers, req.WeekNumber, req.Year)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Questionnaire submitted successfully", nil)
}

// GetVARKAssessment handles fetching VARK questions and options
func (h *Handler) GetVARKAssessment(c *gin.Context) {
	questions, optionsMap, err := h.varkService.GetVARKAssessment()
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	// Transform data to a more suitable format for DTO
	varkQuestions := []dtos.VARKQuestion{}
	for _, q := range questions {
		dtoOptions := []dtos.VARKAnswerOption{}
		for _, opt := range optionsMap[q.ID] {
			dtoOptions = append(dtoOptions, dtos.VARKAnswerOption{
				ID:          opt.ID,
				OptionLetter: opt.OptionLetter,
				OptionText:  opt.OptionText,
				LearningStyle: opt.LearningStyle,
			})
		}
		varkQuestions = append(varkQuestions, dtos.VARKQuestion{
			ID:           q.ID,
			QuestionNumber: q.QuestionNumber,
			QuestionText: q.QuestionText,
			Options:      dtoOptions,
		})
	}

	response.Success(c, http.StatusOK, "VARK assessment fetched successfully", varkQuestions)
}

// SubmitVARK handles submitting a student's VARK assessment answers
func (h *Handler) SubmitVARK(c *gin.Context) {
	studentID, exists := c.Get("user_id") // Assuming user_id is set in context by auth middleware
	if !exists {
		response.Error(c, http.StatusInternalServerError, "User ID not found in context")
		return
	}

	var req dtos.SubmitVARKRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request payload")
		return
	}

	result, err := h.varkService.SubmitVARKResult(studentID.(int), req.Answers)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "VARK assessment submitted successfully", result)
}

// GetLatestVARKResult handles fetching the latest VARK result for the authenticated student
func (h *Handler) GetLatestVARKResult(c *gin.Context) {
	studentID, exists := c.Get("user_id") // Assuming user_id is set in context by auth middleware
	if !exists {
		response.Error(c, http.StatusInternalServerError, "User ID not found in context")
		return
	}

	result, err := h.varkService.GetLatestVARKResult(studentID.(int))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	if result == nil {
		response.Error(c, http.StatusNotFound, "No VARK result found for this student")
		return
	}

	response.Success(c, http.StatusOK, "Latest VARK result fetched successfully", result)
}

// AnalyzeText handles NLP text analysis
func (h *Handler) AnalyzeText(c *gin.Context) {
	studentID, exists := c.Get("user_id")
	if !exists {
		response.Error(c, http.StatusInternalServerError, "User ID not found in context")
		return
	}

	var req dtos.AnalyzeTextRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request payload")
		return
	}

	result, err := h.nlpService.AnalyzeText(studentID.(int), req.Text, req.ContextType, req.AssignmentID, req.QuizID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	// Unmarshal JSON strings back to slices for response DTO
	var feedback []string
	if result.Feedback != nil {
		json.Unmarshal([]byte(*result.Feedback), &feedback)
	}
	var personalizedFeedback []string
	if result.PersonalizedFeedback != nil {
		json.Unmarshal([]byte(*result.PersonalizedFeedback), &personalizedFeedback)
	}

	response.Success(c, http.StatusOK, "Text analyzed successfully", dtos.NLPAnalysisResponse{
		TotalScore:       result.TotalScore,
		GrammarScore:     result.GrammarScore,
		KeywordScore:     result.KeywordScore,
		StructureScore:   result.StructureScore,
		ReadabilityScore: result.ReadabilityScore,
		SentimentScore:   result.SentimentScore,
		ComplexityScore:  result.ComplexityScore,
		Feedback:         feedback,
		PersonalizedFeedback: personalizedFeedback,
	})
}

// GetNLPStats handles fetching NLP statistics for the authenticated student
func (h *Handler) GetNLPStats(c *gin.Context) {
	studentID, exists := c.Get("user_id")
	if !exists {
		response.Error(c, http.StatusInternalServerError, "User ID not found in context")
		return
	}

	stats, err := h.nlpService.GetOverallNLPStats(studentID.(int))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	if stats == nil {
		response.Success(c, http.StatusOK, "No NLP statistics found for this student", dtos.NLPStatsResponse{})
		return
	}

	response.Success(c, http.StatusOK, "NLP statistics fetched successfully", dtos.NLPStatsResponse{
		TotalAnalyses:      stats.TotalAnalyses,
		AverageScore:       stats.AverageScore,
		BestScore:          stats.BestScore,
		GrammarImprovement: stats.GrammarImprovement,
		KeywordImprovement: stats.KeywordImprovement,
		StructureImprovement: stats.StructureImprovement,
	})
}
