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

// GetAllQuestionnaires retrieves all active questionnaires (excluding VARK)
func (s *QuestionnaireStore) GetAllQuestionnaires() ([]models.Questionnaire, error) {
	var questionnaires []models.Questionnaire
	err := s.db.Select(&questionnaires, "SELECT id, type, name, description, total_questions, status, created_at FROM questionnaires WHERE status = 'active' AND type != 'vark' ORDER BY type")
	if err != nil {
		return nil, err
	}
	return questionnaires, nil
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

// GetQuestionsByQuestionnaireID retrieves all questions for a given questionnaire ID
func (s *QuestionnaireStore) GetQuestionsByQuestionnaireID(questionnaireID int) ([]models.QuestionnaireQuestion, error) {
	var questions []models.QuestionnaireQuestion
	err := s.db.Select(&questions, "SELECT id, questionnaire_id, question_number, question_text, subscale, reverse_scored, created_at FROM questionnaire_questions WHERE questionnaire_id = ? ORDER BY question_number", questionnaireID)
	if err != nil {
		return nil, err
	}
	return questions, nil
}

// CreateQuestionnaireResult saves a student's questionnaire result
func (s *QuestionnaireStore) CreateQuestionnaireResult(result *models.QuestionnaireResult) error {
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

// GetLatestQuestionnaireResultByQuestionnaireIDAndStudentID retrieves the latest result for a student and a specific questionnaire ID
func (s *QuestionnaireStore) GetLatestQuestionnaireResultByQuestionnaireIDAndStudentID(studentID int, questionnaireID int) (*models.QuestionnaireResult, error) {
	var result models.QuestionnaireResult
	query := `
		SELECT qr.id, qr.student_id, qr.questionnaire_id, qr.answers, qr.total_score, qr.subscale_scores, qr.completed_at, qr.week_number, qr.year,
			q.name as questionnaire_name, q.type as questionnaire_type, q.description as questionnaire_description
		FROM questionnaire_results qr
		JOIN questionnaires q ON qr.questionnaire_id = q.id
		WHERE qr.student_id = ? AND qr.questionnaire_id = ?
		ORDER BY qr.completed_at DESC
		LIMIT 1
	`
	err := s.db.Get(&result, query, studentID, questionnaireID)
	if err == sql.ErrNoRows {
		return nil, nil
	}
	if err != nil {
		return nil, err
	}
	return &result, nil
}

// GetQuestionnaireResultsByStudentID retrieves all questionnaire results for a given student
func (s *QuestionnaireStore) GetQuestionnaireResultsByStudentID(studentID int) ([]models.QuestionnaireResult, error) {
	var results []models.QuestionnaireResult
	query := `
		SELECT qr.id, qr.student_id, qr.questionnaire_id, qr.answers, qr.total_score, qr.subscale_scores, qr.completed_at, qr.week_number, qr.year,
			q.name as questionnaire_name, q.type as questionnaire_type, q.description as questionnaire_description
		FROM questionnaire_results qr
		JOIN questionnaires q ON qr.questionnaire_id = q.id
		WHERE qr.student_id = ? AND q.type IN ('mslq', 'ams')
		ORDER BY qr.completed_at DESC
	`
	err := s.db.Select(&results, query, studentID)
	if err != nil {
		return nil, err
	}
	return results, nil
}

// GetQuestionnaireStatsByStudentID retrieves statistics for questionnaires for a given student
func (s *QuestionnaireStore) GetQuestionnaireStatsByStudentID(studentID int) ([]models.QuestionnaireStat, error) {
	var stats []models.QuestionnaireStat
	query := `
		SELECT 
			q.type,
			q.name,
			COUNT(qr.id) as total_completed,
			AVG(qr.total_score) as average_score,
			MAX(qr.total_score) as best_score,
			MIN(qr.total_score) as lowest_score,
			MAX(qr.completed_at) as last_completed
		FROM questionnaires q
		LEFT JOIN questionnaire_results qr ON (q.id = qr.questionnaire_id AND qr.student_id = ?)
		WHERE q.status = 'active' AND q.type IN ('mslq', 'ams')
		GROUP BY q.id, q.type, q.name
		ORDER BY q.type
	`
	err := s.db.Select(&stats, query, studentID)
	if err != nil {
		return nil, err
	}
	return stats, nil
}
