package store

import (
	"database/sql"
	"pointmarket/backend/internal/models"

	"github.com/jmoiron/sqlx"
)

// QuizStore handles database operations for quizzes
type QuizStore struct {
	db *sqlx.DB
}

// NewQuizStore creates a new QuizStore
func NewQuizStore(db *sqlx.DB) *QuizStore {
	return &QuizStore{db: db}
}

// GetAllQuizzes retrieves all quizzes
func (s *QuizStore) GetAllQuizzes() ([]models.Quiz, error) {
	var quizzes []models.Quiz
	err := s.db.Select(&quizzes, "SELECT id, title, description, subject, teacher_id, points, duration, status, created_at, updated_at FROM quiz")
	if err != nil {
		return nil, err
	}
	return quizzes, nil
}

// CreateQuiz inserts a new quiz into the database
func (s *QuizStore) CreateQuiz(quiz *models.Quiz) error {
	query := `INSERT INTO quiz (title, description, subject, teacher_id, points, duration, status) VALUES (?, ?, ?, ?, ?, ?, ?)`
	result, err := s.db.Exec(query,
		quiz.Title,
		quiz.Description,
		quiz.Subject,
		quiz.TeacherID,
		quiz.Points,
		quiz.Duration,
		quiz.Status,
	)
	if err != nil {
		return err
	}
	id, err := result.LastInsertId()
	if err != nil {
		return err
	}
	quiz.ID = int(id)
	return nil
}

// GetQuizByID retrieves a quiz by its ID
func (s *QuizStore) GetQuizByID(id int) (*models.Quiz, error) {
	var quiz models.Quiz
	err := s.db.Get(&quiz, "SELECT id, title, description, subject, teacher_id, points, duration, status, created_at, updated_at FROM quiz WHERE id = ?", id)
	if err == sql.ErrNoRows {
		return nil, nil
	}
	if err != nil {
		return nil, err
	}
	return &quiz, nil
}

// UpdateQuiz updates an existing quiz in the database
func (s *QuizStore) UpdateQuiz(quiz *models.Quiz) error {
	query := `UPDATE quiz SET title = ?, description = ?, subject = ?, teacher_id = ?, points = ?, duration = ?, status = ? WHERE id = ?`
	_, err := s.db.Exec(query,
		quiz.Title,
		quiz.Description,
		quiz.Subject,
		quiz.TeacherID,
		quiz.Points,
		quiz.Duration,
		quiz.Status,
		quiz.ID,
	)
	return err
}

// DeleteQuiz deletes a quiz from the database by its ID
func (s *QuizStore) DeleteQuiz(id int) error {
	_, err := s.db.Exec("DELETE FROM quiz WHERE id = ?", id)
	return err
}

// ListQuizzes retrieves all quizzes, optionally filtered by teacher_id
func (s *QuizStore) ListQuizzes(teacherID *int) ([]models.Quiz, error) {
	var quizzes []models.Quiz
	query := "SELECT id, title, description, subject, teacher_id, points, duration, status, created_at, updated_at FROM quiz"
	args := []interface{}{} // Use interface{} for args

	if teacherID != nil {
		query += " WHERE teacher_id = ?"
		args = append(args, *teacherID)
	}

	err := s.db.Select(&quizzes, query, args...)
	if err != nil {
		return nil, err
	}
	return quizzes, nil
}