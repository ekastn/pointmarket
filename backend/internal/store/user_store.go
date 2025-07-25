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

// GetStudentDashboardStats retrieves aggregated statistics for a student's dashboard
func (s *UserStore) GetStudentDashboardStats(studentID int) (*models.StudentDashboardStats, error) {
	var stats models.StudentDashboardStats
	query := `
		SELECT 
			COALESCE((SELECT SUM(sa.score) FROM student_assignments sa WHERE sa.student_id = u.id AND sa.status = 'completed') + 
					 (SELECT SUM(sq.score) FROM student_quiz sq WHERE sq.student_id = u.id AND sq.status = 'completed'), 0) as total_points,
			COALESCE((SELECT COUNT(*) FROM student_assignments sa WHERE sa.student_id = u.id AND sa.status = 'completed') + 
					 (SELECT COUNT(*) FROM student_quiz sq WHERE sq.student_id = u.id AND sq.status = 'completed'), 0) as completed_assignments,
			(SELECT qr.total_score FROM questionnaire_results qr JOIN questionnaires q ON qr.questionnaire_id = q.id WHERE qr.student_id = u.id AND q.type = 'mslq' ORDER BY qr.completed_at DESC LIMIT 1) as mslq_score,
			(SELECT qr.total_score FROM questionnaire_results qr JOIN questionnaires q ON qr.questionnaire_id = q.id WHERE qr.student_id = u.id AND q.type = 'ams' ORDER BY qr.completed_at DESC LIMIT 1) as ams_score,
			(SELECT vr.dominant_style FROM vark_results vr WHERE vr.student_id = u.id ORDER BY vr.completed_at DESC LIMIT 1) as vark_dominant_style,
			(SELECT vr.learning_preference FROM vark_results vr WHERE vr.student_id = u.id ORDER BY vr.completed_at DESC LIMIT 1) as vark_learning_preference
		FROM users u
		WHERE u.id = ? AND u.role = 'siswa'
	`
	err := s.db.Get(&stats, query, studentID)
	if err == sql.ErrNoRows {
		return nil, nil
	}
	if err != nil {
		return nil, err
	}
	return &stats, nil
}

// GetAdminDashboardCounts retrieves counts for admin dashboard
func (s *UserStore) GetAdminDashboardCounts() (*models.AdminDashboardCounts, error) {
	var counts models.AdminDashboardCounts
	query := `
		SELECT 
			(SELECT COUNT(*) FROM users) as total_users,
			(SELECT COUNT(*) FROM assignments) as total_assignments,
			(SELECT COUNT(*) FROM materials) as total_materials
	`
	err := s.db.Get(&counts, query)
	if err != nil {
		return nil, err
	}
	return &counts, nil
}

// GetTeacherDashboardCounts retrieves counts for teacher dashboard
func (s *UserStore) GetTeacherDashboardCounts(teacherID int) (*models.TeacherDashboardCounts, error) {
	var counts models.TeacherDashboardCounts
	query := `
		SELECT 
			(SELECT COUNT(*) FROM assignments WHERE teacher_id = ?) as my_assignments,
			(SELECT COUNT(*) FROM materials WHERE teacher_id = ?) as my_materials,
			(SELECT COUNT(*) FROM users WHERE role = 'siswa') as total_students
	`
	err := s.db.Get(&counts, query, teacherID, teacherID)
	if err != nil {
		return nil, err
	}
	return &counts, nil
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

// GetAssignmentStatsByStudentID retrieves assignment statistics for a student
func (s *UserStore) GetAssignmentStatsByStudentID(studentID int) (*models.AssignmentStats, error) {
	var stats models.AssignmentStats
	query := `
		SELECT 
			COUNT(*) as total_assignments,
			COALESCE(AVG(sa.score), 0) as avg_score,
			COALESCE(MAX(sa.score), 0) as best_score,
			COALESCE(MIN(sa.score), 0) as lowest_score,
			COUNT(CASE WHEN sa.score >= 80 THEN 1 END) as high_scores,
			COUNT(CASE WHEN sa.submitted_at > a.due_date THEN 1 END) as late_submissions
		FROM student_assignments sa
		JOIN assignments a ON sa.assignment_id = a.id
		WHERE sa.student_id = ? AND sa.status = 'completed'
	`
	err := s.db.Get(&stats, query, studentID)
	if err == sql.ErrNoRows {
		return &models.AssignmentStats{}, nil // Return empty stats if no rows
	}
	if err != nil {
		return nil, err
	}
	return &stats, nil
}

// GetRecentActivityByUserID retrieves recent activity for a user
func (s *UserStore) GetRecentActivityByUserID(userID int, limit int) ([]models.ActivityLog, error) {
	var activities []models.ActivityLog
	query := `
		SELECT id, user_id, action, description, ip_address, user_agent, created_at
		FROM activity_log
		WHERE user_id = ?
		ORDER BY created_at DESC
		LIMIT ?
	`
	err := s.db.Select(&activities, query, userID, limit)
	if err != nil {
		return nil, err
	}
	return activities, nil
}

// GetWeeklyEvaluationProgressByStudentID retrieves weekly evaluation progress for a student
func (s *UserStore) GetWeeklyEvaluationProgressByStudentID(studentID int, weeks int) ([]models.WeeklyEvaluationProgress, error) {
	var progress []models.WeeklyEvaluationProgress
	query := `
		SELECT 
			we.week_number,
			we.year,
			q.type as questionnaire_type,
			q.name as questionnaire_name,
			we.status,
			we.due_date,
			we.completed_at,
			qr.total_score as mslq_score, -- Assuming MSLQ score for now
			(SELECT qr2.total_score FROM questionnaire_results qr2 JOIN questionnaires q2 ON qr2.questionnaire_id = q2.id WHERE qr2.student_id = we.student_id AND q2.type = 'ams' AND qr2.week_number = we.week_number AND qr2.year = we.year ORDER BY qr2.completed_at DESC LIMIT 1) as ams_score
		FROM weekly_evaluations we
		JOIN questionnaires q ON we.questionnaire_id = q.id
		LEFT JOIN questionnaire_results qr ON (
			qr.student_id = we.student_id 
			AND qr.questionnaire_id = we.questionnaire_id 
			AND qr.week_number = we.week_number 
			AND qr.year = we.year
		)
		WHERE we.student_id = ? 
		AND q.type != 'vark'
		AND ((we.year = YEAR(CURDATE()) AND we.week_number >= (WEEK(CURDATE(), 1) - ? + 1)) OR we.year > YEAR(CURDATE()))
		ORDER BY we.year DESC, we.week_number DESC, q.type
		LIMIT ? * 2 -- 2 questionnaires per week
	`
	err := s.db.Select(&progress, query, studentID, weeks, weeks)
	if err != nil {
		return nil, err
	}
	return progress, nil
}