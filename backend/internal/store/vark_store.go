package store

import (
	"database/sql"
	"pointmarket/backend/internal/models"

	"github.com/jmoiron/sqlx"
)

// VARKStore handles database operations for VARK assessments
type VARKStore struct {
	db *sqlx.DB
}

// NewVARKStore creates a new VARKStore
func NewVARKStore(db *sqlx.DB) *VARKStore {
	return &VARKStore{db: db}
}

// GetVARKQuestionnaire retrieves the VARK questionnaire
func (s *VARKStore) GetVARKQuestionnaire() (*models.Questionnaire, error) {
	var q models.Questionnaire
	err := s.db.Get(&q, "SELECT id, type, name, description, total_questions, status, created_at FROM questionnaires WHERE type = 'VARK'")
	if err == sql.ErrNoRows {
		return nil, nil
	}
	if err != nil {
		return nil, err
	}
	return &q, nil
}

// GetQuestionsByQuestionnaireID retrieves all questions for a given questionnaire ID
func (s *VARKStore) GetQuestionsByQuestionnaireID(questionnaireID int) ([]models.QuestionnaireQuestion, error) {
	var questions []models.QuestionnaireQuestion
	err := s.db.Select(&questions, "SELECT id, questionnaire_id, question_number, question_text, subscale, reverse_scored, created_at FROM questionnaire_questions WHERE questionnaire_id = ? ORDER BY question_number", questionnaireID)
	if err != nil {
		return nil, err
	}
	return questions, nil
}

// GetVARKAnswerOptionsByQuestionnaireID retrieves all VARK answer options for a given questionnaire ID
func (s *VARKStore) GetVARKAnswerOptionsByQuestionnaireID(questionnaireID int) ([]models.VARKAnswerOption, error) {
	var options []models.VARKAnswerOption
	query := `SELECT id, question_id, option_letter, option_text, learning_style FROM vark_answer_options WHERE question_id IN (SELECT id FROM questionnaire_questions WHERE questionnaire_id = ?)`
	err := s.db.Select(&options, query, questionnaireID)
	if err != nil {
		return nil, err
	}
	return options, nil
}

// CreateVARKResult saves a student's VARK assessment result
func (s *VARKStore) CreateVARKResult(result *models.VARKResult) error {
	query := `INSERT INTO vark_results (student_id, visual_score, auditory_score, reading_score, kinesthetic_score, dominant_style, learning_preference, answers, completed_at, week_number, year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`
	_, err := s.db.Exec(query,
		result.StudentID,
		result.VisualScore,
		result.AuditoryScore,
		result.ReadingScore,
		result.KinestheticScore,
		result.DominantStyle,
		result.LearningPreference,
		result.Answers,
		result.CompletedAt,
		result.WeekNumber,
		result.Year,
	)
	return err
}

// GetLatestVARKResult retrieves the latest VARK result for a student
func (s *VARKStore) GetLatestVARKResult(studentID int) (*models.VARKResult, error) {
	var result models.VARKResult
	query := `SELECT id, student_id, visual_score, auditory_score, reading_score, kinesthetic_score, dominant_style, learning_preference, answers, completed_at, week_number, year FROM vark_results WHERE student_id = ? ORDER BY completed_at DESC LIMIT 1`
	err := s.db.Get(&result, query, studentID)
	if err == sql.ErrNoRows {
		return nil, nil
	}
	if err != nil {
		return nil, err
	}
	return &result, nil
}