package services

import (
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store"
)

// QuizService provides business logic for quizzes
type QuizService struct {
	quizStore *store.QuizStore
}

// NewQuizService creates a new QuizService
func NewQuizService(quizStore *store.QuizStore) *QuizService {
	return &QuizService{quizStore: quizStore}
}

// GetAllQuizzes retrieves all quizzes
func (s *QuizService) GetAllQuizzes() ([]models.Quiz, error) {
	return s.quizStore.GetAllQuizzes()
}

// GetQuizByID retrieves a quiz by its ID
func (s *QuizService) GetQuizByID(id uint) (models.Quiz, error) {
	quiz, err := s.quizStore.GetQuizByID(int(id))
	if err != nil {
		return models.Quiz{}, err
	}
	return *quiz, err
}

// CreateQuiz creates a new quiz
func (s *QuizService) CreateQuiz(req dtos.CreateQuizRequest, teacherID uint) (models.Quiz, error) {
	quiz := models.Quiz{
		Title:       req.Title,
		Description: &req.Description,
		Subject:     req.Subject,
		Points:      req.Points,
		Duration:    &req.Duration,
		TeacherID:   int(teacherID),
		Status:      "Published", // Default status
	}
	err := s.quizStore.CreateQuiz(&quiz)
	return quiz, err
}

// UpdateQuiz updates an existing quiz
func (s *QuizService) UpdateQuiz(id uint, req dtos.CreateQuizRequest) (models.Quiz, error) {
	quiz, err := s.GetQuizByID(id)
	if err != nil {
		return models.Quiz{}, err
	}

	if req.Title != "" {
		quiz.Title = req.Title
	}
	if req.Description != "" {
		quiz.Description = &req.Description
	}
	if req.Subject != "" {
		quiz.Subject = req.Subject
	}
	if req.Points != 0 {
		quiz.Points = req.Points
	}
	if req.Duration != 0 {
		quiz.Duration = &req.Duration
	}

	err = s.quizStore.UpdateQuiz(&quiz)
	return quiz, err
}

// DeleteQuiz deletes a quiz by its ID
func (s *QuizService) DeleteQuiz(id uint) error {
	return s.quizStore.DeleteQuiz(int(id))
}
