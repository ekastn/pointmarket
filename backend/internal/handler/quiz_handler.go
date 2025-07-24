package handler

import (
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"strconv"

	"github.com/gin-gonic/gin"
)

type QuizHandler struct {
	quizService services.QuizService
}

func NewQuizHandler(quizService services.QuizService) *QuizHandler {
	return &QuizHandler{quizService: quizService}
}

func (h *QuizHandler) GetAllQuizzes(c *gin.Context) {
	quizzes, err := h.quizService.GetAllQuizzes()
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	var quizListDTO dtos.QuizListResponse
	quizListDTO.FromQuizzes(quizzes)
	response.Success(c, http.StatusOK, "Quizzes retrieved successfully", quizListDTO)
}

func (h *QuizHandler) GetQuizByID(c *gin.Context) {
	id, _ := strconv.Atoi(c.Param("id"))
	quiz, err := h.quizService.GetQuizByID(uint(id))
	if err != nil {
		response.Error(c, http.StatusNotFound, "Quiz not found")
		return
	}
	var quizDTO dtos.QuizResponse
	quizDTO.FromQuiz(quiz)
	response.Success(c, http.StatusOK, "Quiz retrieved successfully", quizDTO)
}

func (h *QuizHandler) CreateQuiz(c *gin.Context) {
	var createDTO dtos.CreateQuizRequest
	if err := c.ShouldBindJSON(&createDTO); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	userID, _ := c.Get("userID")
	quiz, err := h.quizService.CreateQuiz(createDTO, userID.(uint))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	var quizDTO dtos.QuizResponse
	quizDTO.FromQuiz(quiz)
	response.Success(c, http.StatusCreated, "Quiz created successfully", quizDTO)
}

func (h *QuizHandler) UpdateQuiz(c *gin.Context) {
	id, _ := strconv.Atoi(c.Param("id"))
	var updateDTO dtos.CreateQuizRequest // Assuming CreateQuizRequest can be used for update
	if err := c.ShouldBindJSON(&updateDTO); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	quiz, err := h.quizService.UpdateQuiz(uint(id), updateDTO)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	var quizDTO dtos.QuizResponse
	quizDTO.FromQuiz(quiz)
	response.Success(c, http.StatusOK, "Quiz updated successfully", quizDTO)
}

func (h *QuizHandler) DeleteQuiz(c *gin.Context) {
	id, _ := strconv.Atoi(c.Param("id"))
	err := h.quizService.DeleteQuiz(uint(id))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Quiz deleted successfully", nil)
}