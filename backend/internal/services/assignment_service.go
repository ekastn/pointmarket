package services

import (
	"errors"
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store"
)

// AssignmentService provides business logic for assignments
type AssignmentService struct {
	assignmentStore *store.AssignmentStore
}

// NewAssignmentService creates a new AssignmentService
func NewAssignmentService(assignmentStore *store.AssignmentStore) *AssignmentService {
	return &AssignmentService{assignmentStore: assignmentStore}
}

// CreateAssignment creates a new assignment
func (s *AssignmentService) CreateAssignment(assignment *models.Assignment) error {
	if assignment.Title == "" || assignment.Subject == "" || assignment.TeacherID == 0 {
		return errors.New("title, subject, and teacher ID are required")
	}
	return s.assignmentStore.CreateAssignment(assignment)
}

// GetAssignmentByID retrieves an assignment by its ID
func (s *AssignmentService) GetAssignmentByID(id int) (*models.Assignment, error) {
	return s.assignmentStore.GetAssignmentByID(id)
}

// UpdateAssignment updates an existing assignment
func (s *AssignmentService) UpdateAssignment(assignment *models.Assignment) error {
	if assignment.ID == 0 {
		return errors.New("assignment ID is required for update")
	}
	return s.assignmentStore.UpdateAssignment(assignment)
}

// DeleteAssignment deletes an assignment by its ID
func (s *AssignmentService) DeleteAssignment(id int) error {
	return s.assignmentStore.DeleteAssignment(id)
}

// ListAssignments retrieves all assignments, optionally filtered by teacher ID
func (s *AssignmentService) ListAssignments(teacherID *int) ([]models.Assignment, error) {
	return s.assignmentStore.ListAssignments(teacherID)
}
