package services

import (
	"pointmarket/backend/internal/dtos"
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

// GetAllAssignments retrieves all assignments
func (s *AssignmentService) GetAllAssignments() ([]models.Assignment, error) {
	return s.assignmentStore.GetAllAssignments()
}

// CreateAssignment creates a new assignment
func (s *AssignmentService) CreateAssignment(req dtos.CreateAssignmentRequest, teacherID uint) (models.Assignment, error) {
	assignment := models.Assignment{
		Title:       req.Title,
		Description: &req.Description,
		DueDate:     &req.DueDate,
		Subject:     req.Subject,
		Points:      req.Points,
		TeacherID:   int(teacherID),
		Status:      "Published", // Default status
	}
	err := s.assignmentStore.CreateAssignment(&assignment)
	return assignment, err
}

// GetAssignmentByID retrieves an assignment by its ID
func (s *AssignmentService) GetAssignmentByID(id uint) (models.Assignment, error) {
	assignment, err := s.assignmentStore.GetAssignmentByID(int(id))
	if err != nil {
		return models.Assignment{}, err
	}
	return *assignment, err
}

// UpdateAssignment updates an existing assignment
func (s *AssignmentService) UpdateAssignment(id uint, req dtos.UpdateAssignmentRequest) (models.Assignment, error) {
	assignment, err := s.GetAssignmentByID(id)
	if err != nil {
		return models.Assignment{}, err
	}

	if req.Title != "" {
		assignment.Title = req.Title
	}
	if req.Description != "" {
		assignment.Description = &req.Description
	}
	if !req.DueDate.IsZero() {
		assignment.DueDate = &req.DueDate
	}
	if req.Subject != "" {
		assignment.Subject = req.Subject
	}
	if req.Points != 0 {
		assignment.Points = req.Points
	}

	err = s.assignmentStore.UpdateAssignment(&assignment)
	return assignment, err
}

// DeleteAssignment deletes an assignment by its ID
func (s *AssignmentService) DeleteAssignment(id uint) error {
	return s.assignmentStore.DeleteAssignment(int(id))
}