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

// GetVARKQuestions retrieves all VARK questions with their options
func (s *VARKStore) GetVARKQuestions() ([]models.QuestionnaireQuestion, error) {
	var questions []models.QuestionnaireQuestion
	// Assuming VARK questionnaire_id is 3 as per your SQL schema
	query := `SELECT id, questionnaire_id, question_number, question_text, subscale FROM questionnaire_questions WHERE questionnaire_id = 3 ORDER BY question_number`
	err := s.db.Select(&questions, query)
	if err != nil {
		return nil, err
	}
	return questions, nil
}

// GetVARKAnswerOptions retrieves answer options for a specific VARK question
func (s *VARKStore) GetVARKAnswerOptions(questionID int) ([]models.VARKAnswerOption, error) {
	var options []models.VARKAnswerOption
	query := `SELECT id, question_id, option_letter, option_text, learning_style FROM vark_answer_options WHERE question_id = ? ORDER BY option_letter`
	err := s.db.Select(&options, query, questionID)
	if err != nil {
		return nil, err
	}
	return options, nil
}

// SaveVARKResult saves a student's VARK assessment result
func (s *VARKStore) SaveVARKResult(result *models.VARKResult) error {
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
