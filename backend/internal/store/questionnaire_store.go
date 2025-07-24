package store

import (
	"database/sql"
	"pointmarket/backend/internal/models"

	"github.com/jmoiron/sqlx"
)

// QuestionnaireStore handles database operations for questionnaires
type QuestionnaireStore struct {
	db *sqlx.DB
}

// NewQuestionnaireStore creates a new QuestionnaireStore
func NewQuestionnaireStore(db *sqlx.DB) *QuestionnaireStore {
	return &QuestionnaireStore{db: db}
}

// GetQuestionnaireByID retrieves a questionnaire by its ID
func (s *QuestionnaireStore) GetQuestionnaireByID(id int) (*models.Questionnaire, error) {
	var q models.Questionnaire
	err := s.db.Get(&q, "SELECT id, type, name, description, total_questions, status, created_at FROM questionnaires WHERE id = ?", id)
	if err == sql.ErrNoRows {
		return nil, nil
	}
	if err != nil {
		return nil, err
	}
	return &q, nil
}

// GetQuestionnaireQuestions retrieves all questions for a given questionnaire ID
func (s *QuestionnaireStore) GetQuestionnaireQuestions(questionnaireID int) ([]models.QuestionnaireQuestion, error) {
	var questions []models.QuestionnaireQuestion
	err := s.db.Select(&questions, "SELECT id, questionnaire_id, question_number, question_text, subscale, reverse_scored, created_at FROM questionnaire_questions WHERE questionnaire_id = ? ORDER BY question_number", questionnaireID)
	if err != nil {
		return nil, err
	}
	return questions, nil
}

// SaveQuestionnaireResult saves a student's questionnaire result
func (s *QuestionnaireStore) SaveQuestionnaireResult(result *models.QuestionnaireResult) error {
	query := `INSERT INTO questionnaire_results (student_id, questionnaire_id, answers, total_score, subscale_scores, completed_at, week_number, year) VALUES (?, ?, ?, ?, ?, ?, ?, ?)`
	_, err := s.db.Exec(query,
		result.StudentID,
		result.QuestionnaireID,
		result.Answers,
		result.TotalScore,
		result.SubscaleScores,
		result.CompletedAt,
		result.WeekNumber,
		result.Year,
	)
	return err
}

// GetLatestQuestionnaireResult retrieves the latest result for a student and questionnaire type
func (s *QuestionnaireStore) GetLatestQuestionnaireResult(studentID int, qType string) (*models.QuestionnaireResult, error) {
	var result models.QuestionnaireResult
	query := `
		SELECT qr.id, qr.student_id, qr.questionnaire_id, qr.answers, qr.total_score, qr.subscale_scores, qr.completed_at, qr.week_number, qr.year
		FROM questionnaire_results qr
		JOIN questionnaires q ON qr.questionnaire_id = q.id
		WHERE qr.student_id = ? AND q.type = ?
		ORDER BY qr.completed_at DESC
		LIMIT 1
	`
	err := s.db.Get(&result, query, studentID, qType)
	if err == sql.ErrNoRows {
		return nil, nil
	}
	if err != nil {
		return nil, err
	}
	return &result, nil
}
