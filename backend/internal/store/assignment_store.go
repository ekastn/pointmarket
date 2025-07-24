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
