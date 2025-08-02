package dtos

import (
	"pointmarket/backend/internal/models"
	"time"
)

// ==================
//     Requests
// ==================

type CreateQuizRequest struct {
	Title       string    `json:"title" binding:"required"`
	Description string    `json:"description"`
	DueDate     time.Time `json:"due_date"`
	Subject     string    `json:"subject" binding:"required"`
	Points      int       `json:"points" binding:"required"`
	Duration    int       `json:"duration"`
	TeacherID   int       `json:"teacher_id" binding:"required"`
}

// ==================
//     Responses
// ==================

type QuizResponse struct {
	ID          int        `json:"id"`
	Title       string     `json:"title"`
	Description *string    `json:"description"`
	DueDate     *time.Time `json:"due_date"`
	TeacherID   int        `json:"teacher_id"`
	Subject     string     `json:"subject"`
	Points      int        `json:"points"`
	Duration    *int       `json:"duration"`
	Status      string     `json:"status"`
	CreatedAt   time.Time  `json:"created_at"`
	UpdatedAt   time.Time  `json:"updated_at"`
}

type QuizListResponse struct {
	Quizzes []QuizResponse `json:"quizzes"`
}

// FromQuiz converts a models.Quiz to a QuizResponse DTO.
func (dto *QuizResponse) FromQuiz(quiz models.Quiz) {
	dto.ID = quiz.ID
	dto.Title = quiz.Title
	dto.Description = quiz.Description
	// dto.DueDate = quiz.DueDate // This is a pointer in the model, but not in the DTO
	dto.TeacherID = quiz.TeacherID
	dto.Subject = quiz.Subject
	dto.Points = quiz.Points
	dto.Duration = quiz.Duration
	dto.Status = quiz.Status
	dto.CreatedAt = quiz.CreatedAt
	dto.UpdatedAt = quiz.UpdatedAt
}

// FromQuizzes converts a slice of models.Quiz to a slice of QuizResponse DTOs.
func (dto *QuizListResponse) FromQuizzes(quizzes []models.Quiz) {
	dto.Quizzes = make([]QuizResponse, len(quizzes))
	for i, q := range quizzes {
		var quizDTO QuizResponse
		quizDTO.FromQuiz(q)
		dto.Quizzes[i] = quizDTO
	}
}
