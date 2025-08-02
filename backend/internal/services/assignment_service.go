package services

import (
	"fmt"
	"math/rand"

	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store"
)

// AssignmentService provides business logic for assignments
type AssignmentService struct {
	assignmentStore *store.AssignmentStore
	userStore       *store.UserStore
}

// NewAssignmentService creates a new AssignmentService
func NewAssignmentService(assignmentStore *store.AssignmentStore, userStore *store.UserStore) *AssignmentService {
	return &AssignmentService{assignmentStore: assignmentStore, userStore: userStore}
}

// GetAllAssignments retrieves all assignments, optionally with student-specific details
func (s *AssignmentService) GetAllAssignments(studentID *uint) ([]dtos.AssignmentDetailResponse, error) {
	assignments, err := s.assignmentStore.GetAllAssignments()
	if err != nil {
		return nil, err
	}

	var detailedAssignments []dtos.AssignmentDetailResponse
	for _, assignment := range assignments {
		var studentAssignment *models.StudentAssignment
		if studentID != nil {
			studentAssignment, err = s.assignmentStore.GetStudentAssignment(int(*studentID), assignment.ID)
			if err != nil {
				return nil, fmt.Errorf("failed to get student assignment for assignment %d: %w", assignment.ID, err)
			}
		}

		teacher, err := s.userStore.GetUserByID(uint(assignment.TeacherID))
		if err != nil {
			return nil, fmt.Errorf("failed to get teacher for assignment %d: %w", assignment.ID, err)
		}
		teacherName := "Unknown Teacher"
		if teacher != nil {
			teacherName = teacher.Name
		}

		var detailedDTO dtos.AssignmentDetailResponse
		detailedDTO.FromAssignmentAndStudentAssignment(assignment, studentAssignment, teacherName)
		detailedAssignments = append(detailedAssignments, detailedDTO)
	}

	return detailedAssignments, nil
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
func (s *AssignmentService) GetAssignmentByID(id uint, studentID *uint) (dtos.AssignmentDetailResponse, error) {
	assignment, err := s.assignmentStore.GetAssignmentByID(int(id))
	if err != nil {
		return dtos.AssignmentDetailResponse{}, err
	}

	var studentAssignment *models.StudentAssignment
	if studentID != nil {
		studentAssignment, err = s.assignmentStore.GetStudentAssignment(int(*studentID), int(id))
		if err != nil {
			return dtos.AssignmentDetailResponse{}, fmt.Errorf("failed to get student assignment for assignment %d: %w", id, err)
		}
	}

	teacher, err := s.userStore.GetUserByID(uint(assignment.TeacherID))
	if err != nil {
		return dtos.AssignmentDetailResponse{}, fmt.Errorf("failed to get teacher for assignment %d: %w", assignment.ID, err)
	}
	teacherName := "Unknown Teacher"
	if teacher != nil {
		teacherName = teacher.Name
	}

	var detailedDTO dtos.AssignmentDetailResponse
	detailedDTO.FromAssignmentAndStudentAssignment(*assignment, studentAssignment, teacherName)
	return detailedDTO, nil
}

// StartStudentAssignment marks an assignment as in_progress for a student
func (s *AssignmentService) StartStudentAssignment(studentID, assignmentID uint) error {
	// Check if assignment exists
	_, err := s.assignmentStore.GetAssignmentByID(int(assignmentID))
	if err != nil {
		return fmt.Errorf("assignment not found: %w", err)
	}

	return s.assignmentStore.CreateOrUpdateStudentAssignmentStatus(int(studentID), int(assignmentID), "in_progress")
}

// SubmitStudentAssignment handles the submission of an assignment by a student
func (s *AssignmentService) SubmitStudentAssignment(studentID, assignmentID uint, submissionContent string) (float64, error) {
	// Check if assignment exists
	assignment, err := s.assignmentStore.GetAssignmentByID(int(assignmentID))
	if err != nil {
		return 0, fmt.Errorf("assignment not found: %w", err)
	}

	// Simulate scoring (70-95% of max points for demo)
	score := float64(rand.Intn(26)+70) * (float64(assignment.Points) / 100.0)

	err = s.assignmentStore.SubmitStudentAssignment(int(studentID), int(assignmentID), score, submissionContent)
	if err != nil {
		return 0, fmt.Errorf("failed to submit assignment: %w", err)
	}

	return score, nil
}

// UpdateAssignment updates an existing assignment
func (s *AssignmentService) UpdateAssignment(id uint, req dtos.UpdateAssignmentRequest) (models.Assignment, error) {
	assignment, err := s.GetAssignmentByID(id, nil) // Pass nil for studentID as this is a teacher/admin action
	if err != nil {
		return models.Assignment{}, err
	}

	// Convert AssignmentDetailResponse back to models.Assignment for store update
	originalAssignment := models.Assignment{
		ID:          assignment.ID,
		Title:       assignment.Title,
		Description: assignment.Description,
		Subject:     assignment.Subject,
		TeacherID:   assignment.TeacherID,
		Points:      assignment.Points,
		DueDate:     assignment.DueDate,
		Status:      assignment.Status,
		CreatedAt:   assignment.CreatedAt,
		UpdatedAt:   assignment.UpdatedAt,
	}

	if req.Title != "" {
		originalAssignment.Title = req.Title
	}
	if req.Description != "" {
		originalAssignment.Description = &req.Description
	}
	if !req.DueDate.IsZero() {
		originalAssignment.DueDate = &req.DueDate
	}
	if req.Subject != "" {
		originalAssignment.Subject = req.Subject
	}
	if req.Points != 0 {
		originalAssignment.Points = req.Points
	}

	err = s.assignmentStore.UpdateAssignment(&originalAssignment)
	return originalAssignment, err
}

// DeleteAssignment deletes an assignment by its ID
func (s *AssignmentService) DeleteAssignment(id uint) error {
	return s.assignmentStore.DeleteAssignment(int(id))
}
