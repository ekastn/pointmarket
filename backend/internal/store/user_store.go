package store

import (
	"database/sql"
	"pointmarket/backend/internal/models"

	"github.com/jmoiron/sqlx"
)

// UserStore handles database operations for users
type UserStore struct {
	db *sqlx.DB
}

// NewUserStore creates a new UserStore
func NewUserStore(db *sqlx.DB) *UserStore {
	return &UserStore{db: db}
}

// GetUserByID retrieves a user by their ID
func (s *UserStore) GetUserByID(id uint) (*models.User, error) {
	var user models.User
	err := s.db.Get(&user, "SELECT id, username, password, name, email, role, avatar, created_at, updated_at, last_login FROM users WHERE id = ?", id)
	if err == sql.ErrNoRows {
		return nil, nil // User not found
	}
	if err != nil {
		return nil, err
	}
	return &user, nil
}

// CreateUser inserts a new user into the database
func (s *UserStore) CreateUser(user *models.User) error {
	query := `INSERT INTO users (username, password, name, email, role) VALUES (?, ?, ?, ?, ?)`
	result, err := s.db.Exec(query, user.Username, user.Password, user.Name, user.Email, user.Role)
	if err != nil {
		return err
	}
	id, err := result.LastInsertId()
	if err != nil {
		return err
	}
	user.ID = int(id)
	return nil
}


// GetUserByUsernameAndRole retrieves a user by username and role
func (s *UserStore) GetUserByUsernameAndRole(username, role string) (*models.User, error) {
	var user models.User
	err := s.db.Get(&user, "SELECT id, username, password, name, email, role, avatar, created_at, updated_at, last_login FROM users WHERE username = ? AND role = ?", username, role)
	if err == sql.ErrNoRows {
		return nil, nil // User not found
	}
	if err != nil {
		return nil, err
	}
	return &user, nil
}

// GetUserByUsername retrieves a user by username
func (s *UserStore) GetUserByUsername(username string) (*models.User, error) {
	var user models.User
	err := s.db.Get(&user, "SELECT id, username, password, name, email, role, avatar, created_at, updated_at, last_login FROM users WHERE username = ?", username)
	if err == sql.ErrNoRows {
		return nil, nil // User not found
	}
	if err != nil {
		return nil, err
	}
	return &user, nil
}

// GetStudentEvaluationStatus retrieves the weekly evaluation status for all students
func (s *UserStore) GetStudentEvaluationStatus(currentWeek, currentYear int) ([]models.StudentEvaluationStatus, error) {
	var statuses []models.StudentEvaluationStatus
	query := `
		SELECT 
			u.id as student_id,
			u.name as student_name,
			u.email as student_email,
			COUNT(CASE WHEN we.status = 'completed' THEN 1 END) as completed_this_week,
			COUNT(CASE WHEN we.status = 'pending' THEN 1 END) as pending_this_week,
			COUNT(CASE WHEN we.status = 'overdue' THEN 1 END) as overdue_this_week,
			AVG(CASE WHEN qr.week_number = ? AND qr.year = ? AND q.type = 'mslq' THEN qr.total_score END) as mslq_score_this_week,
			AVG(CASE WHEN qr.week_number = ? AND qr.year = ? AND q.type = 'ams' THEN qr.total_score END) as ams_score_this_week,
			MAX(qr.completed_at) as last_evaluation
		FROM users u
		LEFT JOIN weekly_evaluations we ON (u.id = we.student_id AND we.week_number = ? AND we.year = ?)
		LEFT JOIN questionnaire_results qr ON (u.id = qr.student_id AND qr.week_number = ? AND qr.year = ?)
		LEFT JOIN questionnaires q ON qr.questionnaire_id = q.id
		WHERE u.role = 'siswa'
		GROUP BY u.id, u.name, u.email
		ORDER BY u.name
	`
	err := s.db.Select(&statuses, query,
		currentWeek, currentYear,
		currentWeek, currentYear,
		currentWeek, currentYear,
		currentWeek, currentYear,
	)
	if err != nil {
		return nil, err
	}
	return statuses, nil
}

// GetWeeklyEvaluationOverview retrieves aggregated weekly progress for teachers
func (s *UserStore) GetWeeklyEvaluationOverview(weeks int) ([]models.WeeklyEvaluationOverview, error) {
	var overviews []models.WeeklyEvaluationOverview
	query := `
		SELECT 
			we.week_number,
			we.year,
			q.type as questionnaire_type,
			COUNT(CASE WHEN we.status = 'completed' THEN 1 END) as completed_count,
			COUNT(CASE WHEN we.status = 'pending' THEN 1 END) as pending_count,
			COUNT(CASE WHEN we.status = 'overdue' THEN 1 END) as overdue_count,
			COUNT(*) as total_count,
			ROUND(AVG(qr.total_score), 2) as average_score
		FROM weekly_evaluations we
		JOIN questionnaires q ON we.questionnaire_id = q.id
		LEFT JOIN questionnaire_results qr ON (
			qr.student_id = we.student_id 
			AND qr.questionnaire_id = we.questionnaire_id 
			AND qr.week_number = we.week_number 
			AND qr.year = we.year
		)
		WHERE ((we.year = YEAR(CURDATE()) AND we.week_number >= (WEEK(CURDATE(), 1) - ? + 1)) OR we.year > YEAR(CURDATE()))
		GROUP BY we.week_number, we.year, q.type
		ORDER BY we.year DESC, we.week_number DESC, q.type
		LIMIT ? * 2 -- 2 questionnaires per week
	`
	err := s.db.Select(&overviews, query, weeks, weeks)
	if err != nil {
		return nil, err
	}
	return overviews, nil
}
