package services

import (
	"errors"
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

// CreateQuiz creates a new quiz
func (s *QuizService) CreateQuiz(quiz *models.Quiz) error {
	if quiz.Title == "" || quiz.Subject == "" || quiz.TeacherID == 0 {
		return errors.New("title, subject, and teacher ID are required")
	}
	return s.quizStore.CreateQuiz(quiz)
}

// GetQuizByID retrieves a quiz by its ID
func (s *QuizService) GetQuizByID(id int) (*models.Quiz, error) {
	return s.quizStore.GetQuizByID(id)
}

// UpdateQuiz updates an existing quiz
func (s *QuizService) UpdateQuiz(quiz *models.Quiz) error {
	if quiz.ID == 0 {
		return errors.New("quiz ID is required for update")
	}
	return s.quizStore.UpdateQuiz(quiz)
}

// DeleteQuiz deletes a quiz by its ID
func (s *QuizService) DeleteQuiz(id int) error {
	return s.quizStore.DeleteQuiz(id)
}

// ListQuizzes retrieves all quizzes, optionally filtered by teacher ID
func (s *QuizService) ListQuizzes(teacherID *int) ([]models.Quiz, error) {
	return s.quizStore.ListQuizzes(teacherID)
}
