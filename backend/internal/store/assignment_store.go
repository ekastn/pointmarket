package store

import (
	"database/sql"
	"pointmarket/backend/internal/models"

	"github.com/jmoiron/sqlx"
)

// AssignmentStore handles database operations for assignments
type AssignmentStore struct {
	db *sqlx.DB
}

// NewAssignmentStore creates a new AssignmentStore
func NewAssignmentStore(db *sqlx.DB) *AssignmentStore {
	return &AssignmentStore{db: db}
}

// GetAllAssignments retrieves all assignments
func (s *AssignmentStore) GetAllAssignments() ([]models.Assignment, error) {
	var assignments []models.Assignment
	err := s.db.Select(&assignments, "SELECT id, title, description, subject, teacher_id, points, due_date, status, created_at, updated_at FROM assignments")
	if err != nil {
		return nil, err
	}
	return assignments, nil
}

// CreateAssignment inserts a new assignment into the database
func (s *AssignmentStore) CreateAssignment(assignment *models.Assignment) error {
	query := `INSERT INTO assignments (title, description, subject, teacher_id, points, due_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)`
	result, err := s.db.Exec(query,
		assignment.Title,
		assignment.Description,
		assignment.Subject,
		assignment.TeacherID,
		assignment.Points,
		assignment.DueDate,
		assignment.Status,
	)
	if err != nil {
		return err
	}
	id, err := result.LastInsertId()
	if err != nil {
		return err
	}
	assignment.ID = int(id)
	return nil
}

// GetAssignmentByID retrieves an assignment by its ID
func (s *AssignmentStore) GetAssignmentByID(id int) (*models.Assignment, error) {
	var assignment models.Assignment
	err := s.db.Get(&assignment, "SELECT id, title, description, subject, teacher_id, points, due_date, status, created_at, updated_at FROM assignments WHERE id = ?", id)
	if err == sql.ErrNoRows {
		return nil, nil
	}
	if err != nil {
		return nil, err
	}
	return &assignment, nil
}

// UpdateAssignment updates an existing assignment in the database
func (s *AssignmentStore) UpdateAssignment(assignment *models.Assignment) error {
	query := `UPDATE assignments SET title = ?, description = ?, subject = ?, teacher_id = ?, points = ?, due_date = ?, status = ? WHERE id = ?`
	_, err := s.db.Exec(query,
		assignment.Title,
		assignment.Description,
		assignment.Subject,
		assignment.TeacherID,
		assignment.Points,
		assignment.DueDate,
		assignment.Status,
		assignment.ID,
	)
	return err
}

// DeleteAssignment deletes an assignment from the database by its ID
func (s *AssignmentStore) DeleteAssignment(id int) error {
	_, err := s.db.Exec("DELETE FROM assignments WHERE id = ?", id)
	return err
}

// ListAssignments retrieves all assignments, optionally filtered by teacher_id
func (s *AssignmentStore) ListAssignments(teacherID *int) ([]models.Assignment, error) {
	var assignments []models.Assignment
	query := "SELECT id, title, description, subject, teacher_id, points, due_date, status, created_at, updated_at FROM assignments"
	args := []interface{}{} // Use interface{} for args

	if teacherID != nil {
		query += " WHERE teacher_id = ?"
		args = append(args, *teacherID)
	}

	err := s.db.Select(&assignments, query, args...)
	if err != nil {
		return nil, err
	}
	return assignments, nil
}

// GetStudentAssignment retrieves a student's specific assignment record
func (s *AssignmentStore) GetStudentAssignment(studentID, assignmentID int) (*models.StudentAssignment, error) {
	var sa models.StudentAssignment
	err := s.db.Get(&sa, "SELECT id, student_id, assignment_id, status, score, submission, submitted_at, graded_at, created_at, updated_at FROM student_assignments WHERE student_id = ? AND assignment_id = ?", studentID, assignmentID)
	if err == sql.ErrNoRows {
		return nil, nil
	}
	if err != nil {
		return nil, err
	}
	return &sa, nil
}

// CreateOrUpdateStudentAssignmentStatus creates or updates a student's assignment status
func (s *AssignmentStore) CreateOrUpdateStudentAssignmentStatus(studentID, assignmentID int, status string) error {
	// Check if a record already exists
	var existingID int
	err := s.db.Get(&existingID, "SELECT id FROM student_assignments WHERE student_id = ? AND assignment_id = ?", studentID, assignmentID)

	if err == sql.ErrNoRows {
		// No record, create a new one
		query := `INSERT INTO student_assignments (student_id, assignment_id, status) VALUES (?, ?, ?)`
		_, err = s.db.Exec(query, studentID, assignmentID, status)
	} else if err != nil {
		return err
	} else {
		// Record exists, update its status
		query := `UPDATE student_assignments SET status = ?, updated_at = NOW() WHERE id = ?`
		_, err = s.db.Exec(query, status, existingID)
	}
	return err
}

// SubmitStudentAssignment updates a student's assignment with score and submission content
func (s *AssignmentStore) SubmitStudentAssignment(studentID, assignmentID int, score float64, submission string) error {
	query := `UPDATE student_assignments SET status = ?, score = ?, submission = ?, submitted_at = NOW(), graded_at = NOW(), updated_at = NOW() WHERE student_id = ? AND assignment_id = ?`
	_, err := s.db.Exec(query, "completed", score, submission, studentID, assignmentID)
	return err
}
