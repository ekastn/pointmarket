package store

import (
	"database/sql"
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/utils"
	"time"

	"github.com/jmoiron/sqlx"
)

type WeeklyEvaluationStore struct {
	db *sqlx.DB
}

func NewWeeklyEvaluationStore(db *sqlx.DB) *WeeklyEvaluationStore {
	return &WeeklyEvaluationStore{db: db}
}

// GetStudentEvaluationStatus retrieves the weekly evaluation status for all students for a given week and year.
func (s *WeeklyEvaluationStore) GetStudentEvaluationStatus(week, year int) ([]models.StudentEvaluationStatus, error) {
	var statuses []models.StudentEvaluationStatus
	query := `
        SELECT 
            u.id AS student_id,
            u.name AS student_name,
            u.email AS student_email,
            COALESCE(SUM(CASE WHEN we.status = 'completed' AND we.week_number = ? AND we.year = ? THEN 1 ELSE 0 END), 0) > 0 AS completed_this_week,
            COALESCE(SUM(CASE WHEN we.status = 'pending' AND we.week_number = ? AND we.year = ? THEN 1 ELSE 0 END), 0) > 0 AS pending_this_week,
            COALESCE(SUM(CASE WHEN we.status = 'overdue' AND we.week_number = ? AND we.year = ? THEN 1 ELSE 0 END), 0) > 0 AS overdue_this_week,
            (SELECT qr.total_score FROM questionnaire_results qr WHERE qr.student_id = u.id AND qr.questionnaire_id = 1 AND qr.week_number = ? AND qr.year = ? ORDER BY qr.completed_at DESC LIMIT 1) AS mslq_score_this_week,
            (SELECT qr.total_score FROM questionnaire_results qr WHERE qr.student_id = u.id AND qr.questionnaire_id = 2 AND qr.week_number = ? AND qr.year = ? ORDER BY qr.completed_at DESC LIMIT 1) AS ams_score_this_week,
            (SELECT MAX(we.completed_at) FROM weekly_evaluations we WHERE we.student_id = u.id) AS last_evaluation
        FROM users u
        LEFT JOIN weekly_evaluations we ON u.id = we.student_id
        WHERE u.role = 'siswa'
        GROUP BY u.id, u.name, u.email
        ORDER BY u.name;
    `
	err := s.db.Select(&statuses, query, week, year, week, year, week, year, week, year, week, year)
	if err != nil && err != sql.ErrNoRows {
		return nil, err
	}
	return statuses, nil
}

// GetWeeklyEvaluationOverview retrieves an overview of weekly evaluations for the last N weeks.
func (s *WeeklyEvaluationStore) GetWeeklyEvaluationOverview(weeks int) ([]models.WeeklyEvaluationOverview, error) {
	var overviews []models.WeeklyEvaluationOverview
	query := `
        SELECT 
            we.week_number,
            we.year,
            q.name AS questionnaire_type,
            COUNT(*) AS total_count,
            SUM(CASE WHEN we.status = 'completed' THEN 1 ELSE 0 END) AS completed_count,
            SUM(CASE WHEN we.status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
            SUM(CASE WHEN we.status = 'overdue' THEN 1 ELSE 0 END) AS overdue_count,
            AVG(qr.total_score) AS average_score
        FROM weekly_evaluations we
        JOIN questionnaires q ON we.questionnaire_id = q.id
        LEFT JOIN questionnaire_results qr ON we.student_id = qr.student_id AND we.questionnaire_id = qr.questionnaire_id AND we.week_number = qr.week_number AND we.year = qr.year
        WHERE we.week_number >= ? - ? AND we.year = ?
        GROUP BY we.week_number, we.year, q.name
        ORDER BY we.year DESC, we.week_number DESC;
    `
	// This is a simplified query. A more accurate query would calculate the week numbers correctly across years.
	// For now, we assume the query is for the current year.
	currentYear, currentWeek := utils.GetCurrentWeekAndYear()
	err := s.db.Select(&overviews, query, currentWeek, weeks, currentYear)
	if err != nil && err != sql.ErrNoRows {
		return nil, err
	}
	return overviews, nil
}

// GetWeeklyEvaluationProgressByStudentID retrieves the weekly evaluation progress for a specific student for the last N weeks.
func (s *WeeklyEvaluationStore) GetWeeklyEvaluationProgressByStudentID(studentID, weeks int) ([]models.WeeklyEvaluationProgress, error) {
	var progress []models.WeeklyEvaluationProgress
	query := `
        SELECT 
            we.year,
            we.week_number,
            q.name AS questionnaire_name,
            qr.total_score AS mslq_score, -- This assumes questionnaire_id 1 is MSLQ, 2 is AMS
            (SELECT total_score FROM questionnaire_results WHERE student_id = we.student_id AND questionnaire_id = 2 AND week_number = we.week_number AND year = we.year) as ams_score,
            we.status,
            we.due_date,
            we.completed_at
        FROM weekly_evaluations we
        JOIN questionnaires q ON we.questionnaire_id = q.id
        LEFT JOIN questionnaire_results qr ON we.student_id = qr.student_id AND we.week_number = qr.week_number AND we.year = qr.year AND q.id = qr.questionnaire_id
        WHERE we.student_id = ? AND we.week_number >= ? - ? AND we.year = ?
        ORDER BY we.year DESC, we.week_number DESC;
    `
	currentYear, currentWeek := utils.GetCurrentWeekAndYear()
	err := s.db.Select(&progress, query, studentID, currentWeek, weeks, currentYear)
	if err != nil && err != sql.ErrNoRows {
		return nil, err
	}
	return progress, nil
}

// GetPendingWeeklyEvaluationsByStudentID retrieves all pending weekly evaluations for a specific student.
func (s *WeeklyEvaluationStore) GetPendingWeeklyEvaluationsByStudentID(studentID int) ([]models.WeeklyEvaluationProgress, error) {
	var pendingEvals []models.WeeklyEvaluationProgress
	query := `
        SELECT 
            we.year,
            we.week_number,
            q.name AS questionnaire_name,
            we.status,
            we.due_date
        FROM weekly_evaluations we
        JOIN questionnaires q ON we.questionnaire_id = q.id
        WHERE we.student_id = ? AND we.status = 'pending'
        ORDER BY we.due_date ASC;
    `
	err := s.db.Select(&pendingEvals, query, studentID)
	if err != nil && err != sql.ErrNoRows {
		return nil, err
	}
	return pendingEvals, nil
}

// GetAllActiveStudents retrieves all students with 'siswa' role.
func (s *WeeklyEvaluationStore) GetAllActiveStudents() ([]models.User, error) {
	var students []models.User
	err := s.db.Select(&students, "SELECT id, name, email FROM users WHERE role = 'siswa'")
	if err != nil {
		return nil, err
	}
	return students, nil
}

// GetQuestionnairesByType retrieves questionnaires by their type (e.g., 'mslq', 'ams').
func (s *WeeklyEvaluationStore) GetQuestionnairesByType(questionnaireType string) ([]models.Questionnaire, error) {
	var questionnaires []models.Questionnaire
	err := s.db.Select(&questionnaires, "SELECT id, type, name, description, total_questions, status FROM questionnaires WHERE type = ? AND status = 'active'", questionnaireType)
	if err != nil {
		return nil, err
	}
	return questionnaires, nil
}

// GetWeeklyEvaluationByStudentWeekYearAndQuestionnaire checks if a weekly evaluation already exists.
func (s *WeeklyEvaluationStore) GetWeeklyEvaluationByStudentWeekYearAndQuestionnaire(studentID, week, year, questionnaireID int) (*models.WeeklyEvaluationProgress, error) {
	var eval models.WeeklyEvaluationProgress
	query := `
		SELECT id, student_id, questionnaire_id, week_number, year, status, due_date, completed_at
		FROM weekly_evaluations
		WHERE student_id = ? AND week_number = ? AND year = ? AND questionnaire_id = ?
		LIMIT 1
	`
	err := s.db.Get(&eval, query, studentID, week, year, questionnaireID)
	if err == sql.ErrNoRows {
		return nil, nil // Not found
	}
	if err != nil {
		return nil, err
	}
	return &eval, nil
}

// CreateWeeklyEvaluation inserts a new weekly evaluation record.
func (s *WeeklyEvaluationStore) CreateWeeklyEvaluation(eval models.WeeklyEvaluationProgress) error {
	query := `
		INSERT INTO weekly_evaluations 
			(student_id, questionnaire_id, week_number, year, status, due_date, created_at, updated_at)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?)
	`
	_, err := s.db.Exec(query,
		eval.StudentID,
		eval.QuestionnaireID,
		eval.WeekNumber,
		eval.Year,
		eval.Status,
		eval.DueDate,
		eval.CreatedAt,
		eval.UpdatedAt,
	)
	return err
}

// UpdateWeeklyEvaluationStatus updates the status of a weekly evaluation.
func (s *WeeklyEvaluationStore) UpdateWeeklyEvaluationStatus(evaluationID int, newStatus string) error {
	query := `UPDATE weekly_evaluations SET status = ?, updated_at = ? WHERE id = ?`
	_, err := s.db.Exec(query, newStatus, time.Now(), evaluationID)
	return err
}

// GetPendingEvaluationsDueBefore retrieves pending evaluations that are due before a specific date.
func (s *WeeklyEvaluationStore) GetPendingEvaluationsDueBefore(date time.Time) ([]models.WeeklyEvaluationProgress, error) {
	var evaluations []models.WeeklyEvaluationProgress
	query := `
		SELECT id, student_id, questionnaire_id, week_number, year, status, due_date, completed_at
		FROM weekly_evaluations
		WHERE status = 'pending' AND due_date < ?
	`
	err := s.db.Select(&evaluations, query, date)
	if err != nil {
		return nil, err
	}
	return evaluations, nil
}

// UpdateWeeklyEvaluationStatusByStudentWeekYearAndQuestionnaire updates the status and completed_at timestamp
// of a specific weekly evaluation entry.
func (s *WeeklyEvaluationStore) UpdateWeeklyEvaluationStatusByStudentWeekYearAndQuestionnaire(
	studentID, week, year, questionnaireID int, newStatus string) error {
	query := `
		UPDATE weekly_evaluations
		SET status = ?, completed_at = ?, updated_at = ?
		WHERE student_id = ? AND week_number = ? AND year = ? AND questionnaire_id = ?
	`
	_, err := s.db.Exec(query, newStatus, time.Now(), time.Now(), studentID, week, year, questionnaireID)
	return err
}
