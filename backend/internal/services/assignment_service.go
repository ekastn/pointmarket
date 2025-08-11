package services

import (
	"context"
	"database/sql"
	"fmt"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
)

// AssignmentService provides business logic for assignments and student assignments
type AssignmentService struct {
	q gen.Querier
}

// NewAssignmentService creates a new AssignmentService
func NewAssignmentService(q gen.Querier) *AssignmentService {
	return &AssignmentService{q: q}
}

// CreateAssignment creates a new assignment
func (s *AssignmentService) CreateAssignment(ctx context.Context, req dtos.CreateAssignmentRequestDTO) (dtos.AssignmentDTO, error) {
	result, err := s.q.CreateAssignment(ctx, gen.CreateAssignmentParams{
		Title:        req.Title,
		Description:  sql.NullString{String: *req.Description, Valid: req.Description != nil},
		CourseID:     req.CourseID,
		RewardPoints: req.RewardPoints,
		DueDate:      sql.NullTime{Time: *req.DueDate, Valid: req.DueDate != nil},
		Status:       gen.NullAssignmentsStatus{AssignmentsStatus: gen.AssignmentsStatus(req.Status), Valid: req.Status != ""},
	})
	if err != nil {
		return dtos.AssignmentDTO{}, err
	}

	id, err := result.LastInsertId()
	if err != nil {
		return dtos.AssignmentDTO{}, err
	}

	assignment, err := s.q.GetAssignmentByID(ctx, id)
	if err != nil {
		return dtos.AssignmentDTO{}, err
	}

	var assignmentDTO dtos.AssignmentDTO
	assignmentDTO.FromAssignmentModel(assignment)
	return assignmentDTO, nil
}

// GetAssignmentByID retrieves a single assignment by its ID
func (s *AssignmentService) GetAssignmentByID(ctx context.Context, id int64) (dtos.AssignmentDTO, error) {
	assignment, err := s.q.GetAssignmentByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.AssignmentDTO{}, nil // Assignment not found
	}
	if err != nil {
		return dtos.AssignmentDTO{}, err
	}

	var assignmentDTO dtos.AssignmentDTO
	assignmentDTO.FromAssignmentModel(assignment)
	return assignmentDTO, nil
}

// GetAssignments retrieves a list of assignments based on filters
// This method returns general AssignmentDTOs, not student-specific ones.
func (s *AssignmentService) GetAssignments(ctx context.Context, userID int64, userRole string, courseIDFilter *int64) ([]dtos.AssignmentDTO, error) {
	var assignments []gen.Assignment
	var err error

	switch userRole {
	case "admin":
		if courseIDFilter != nil {
			// Admin filtering for assignments by course ID
			assignments, err = s.q.GetAssignmentsByCourseID(ctx, *courseIDFilter)
		} else {
			// Admin getting all assignments
			assignments, err = s.q.GetAssignments(ctx)
		}
	case "guru": // Teacher
		// Teachers get assignments for courses they own
		assignments, err = s.q.GetAssignmentsByOwnerID(ctx, userID)
	case "siswa": // Student
		// Students get all general assignments.
		// For student-specific assignment details (status, score), use GetStudentAssignmentsList.
		assignments, err = s.q.GetAssignments(ctx)
	default:
		return nil, fmt.Errorf("unsupported user role: %s", userRole)
	}

	if err != nil {
		return nil, err
	}

	var assignmentDTOs []dtos.AssignmentDTO
	for _, assignment := range assignments {
		var assignmentDTO dtos.AssignmentDTO
		assignmentDTO.FromAssignmentModel(assignment)
		assignmentDTOs = append(assignmentDTOs, assignmentDTO)
	}
	return assignmentDTOs, nil
}

// UpdateAssignment updates an existing assignment
func (s *AssignmentService) UpdateAssignment(ctx context.Context, id int64, req dtos.UpdateAssignmentRequestDTO) (dtos.AssignmentDTO, error) {
	// Get existing assignment to apply partial updates
	existingAssignment, err := s.q.GetAssignmentByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.AssignmentDTO{}, nil // Assignment not found
	}
	if err != nil {
		return dtos.AssignmentDTO{}, err
	}

	// Apply updates
	title := existingAssignment.Title
	if req.Title != nil {
		title = *req.Title
	}

	description := existingAssignment.Description
	if req.Description != nil {
		description = sql.NullString{String: *req.Description, Valid: true}
	}

	courseID := existingAssignment.CourseID
	if req.CourseID != nil {
		courseID = *req.CourseID
	}

	rewardPoints := existingAssignment.RewardPoints
	if req.RewardPoints != nil {
		rewardPoints = *req.RewardPoints
	}

	dueDate := existingAssignment.DueDate
	if req.DueDate != nil {
		dueDate = sql.NullTime{Time: *req.DueDate, Valid: true}
	}

	status := existingAssignment.Status
	if req.Status != nil {
		status = gen.NullAssignmentsStatus{AssignmentsStatus: gen.AssignmentsStatus(*req.Status), Valid: *req.Status != ""}
	}

	err = s.q.UpdateAssignment(ctx, gen.UpdateAssignmentParams{
		Title:        title,
		Description:  description,
		CourseID:     courseID,
		RewardPoints: rewardPoints,
		DueDate:      dueDate,
		Status:       status,
		ID:           id,
	})
	if err != nil {
		return dtos.AssignmentDTO{}, err
	}

	updatedAssignment, err := s.q.GetAssignmentByID(ctx, id)
	if err != nil {
		return dtos.AssignmentDTO{}, err
	}

	var assignmentDTO dtos.AssignmentDTO
	assignmentDTO.FromAssignmentModel(updatedAssignment)
	return assignmentDTO, nil
}

// DeleteAssignment deletes an assignment by its ID
func (s *AssignmentService) DeleteAssignment(ctx context.Context, id int64) error {
	return s.q.DeleteAssignment(ctx, id)
}

// CreateStudentAssignment records a student starting an assignment
func (s *AssignmentService) CreateStudentAssignment(ctx context.Context, req dtos.CreateStudentAssignmentRequestDTO) (dtos.StudentAssignmentDTO, error) {
	result, err := s.q.CreateStudentAssignment(ctx, gen.CreateStudentAssignmentParams{
		StudentID:    req.StudentID,
		AssignmentID: req.AssignmentID,
		Status:       gen.NullStudentAssignmentsStatus{StudentAssignmentsStatus: gen.StudentAssignmentsStatus(req.Status), Valid: req.Status != ""},
		Submission:   sql.NullString{String: *req.Submission, Valid: req.Submission != nil},
	})
	if err != nil {
		return dtos.StudentAssignmentDTO{}, err
	}

	newlyCreatedID, err := result.LastInsertId()
	if err != nil {
		return dtos.StudentAssignmentDTO{}, err
	}

	// Fetch the created student assignment to return full details
	createdSA, err := s.q.GetStudentAssignmentByID(ctx, newlyCreatedID)
	if err != nil {
		return dtos.StudentAssignmentDTO{}, err
	}

	var studentAssignmentDTO dtos.StudentAssignmentDTO
	studentAssignmentDTO.FromStudentAssignmentModel(createdSA)
	return studentAssignmentDTO, nil
}

// GetStudentAssignmentByID retrieves a specific student's assignment record
func (s *AssignmentService) GetStudentAssignmentByID(ctx context.Context, id int64) (dtos.StudentAssignmentDTO, error) {
	studentAssignment, err := s.q.GetStudentAssignmentByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.StudentAssignmentDTO{}, nil // Not found
	}
	if err != nil {
		return dtos.StudentAssignmentDTO{}, err
	}

	var studentAssignmentDTO dtos.StudentAssignmentDTO
	studentAssignmentDTO.FromStudentAssignmentModel(studentAssignment)
	return studentAssignmentDTO, nil
}

// GetStudentAssignmentsList retrieves all assignments for a specific student, including their progress.
func (s *AssignmentService) GetStudentAssignmentsList(ctx context.Context, studentID int64) ([]dtos.StudentAssignmentDTO, error) {
	studentAssignments, err := s.q.GetStudentAssignmentsByStudentID(ctx, studentID)
	if err != nil {
		return nil, err
	}

	var studentAssignmentDTOs []dtos.StudentAssignmentDTO
	for _, sa := range studentAssignments {
		var studentAssignmentDTO dtos.StudentAssignmentDTO
		studentAssignmentDTO.FromGetStudentAssignmentsByStudentIDRow(sa)
		studentAssignmentDTOs = append(studentAssignmentDTOs, studentAssignmentDTO)
	}
	return studentAssignmentDTOs, nil
}

// GetStudentAssignmentsByAssignmentID retrieves all student records for a specific assignment
func (s *AssignmentService) GetStudentAssignmentsByAssignmentID(ctx context.Context, assignmentID int64) ([]dtos.StudentAssignmentDTO, error) {
	studentAssignments, err := s.q.GetStudentAssignmentsByAssignmentID(ctx, assignmentID)
	if err != nil {
		return nil, err
	}

	var studentAssignmentDTOs []dtos.StudentAssignmentDTO
	for _, sa := range studentAssignments {
		var studentAssignmentDTO dtos.StudentAssignmentDTO
		studentAssignmentDTO.FromGetStudentAssignmentsByAssignmentIDRow(sa)
		studentAssignmentDTOs = append(studentAssignmentDTOs, studentAssignmentDTO)
	}
	return studentAssignmentDTOs, nil
}

// UpdateStudentAssignment updates a student's assignment record
func (s *AssignmentService) UpdateStudentAssignment(ctx context.Context, id int64, req dtos.UpdateStudentAssignmentRequestDTO) (dtos.StudentAssignmentDTO, error) {
	// Get existing student assignment to apply partial updates
	existingSA, err := s.q.GetStudentAssignmentByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.StudentAssignmentDTO{}, nil // Not found
	}
	if err != nil {
		return dtos.StudentAssignmentDTO{}, err
	}

	// Apply updates
	status := existingSA.Status
	if req.Status != nil {
		status = gen.NullStudentAssignmentsStatus{StudentAssignmentsStatus: gen.StudentAssignmentsStatus(*req.Status), Valid: *req.Status != ""}
	}

	score := existingSA.Score
	if req.Score != nil {
		score = req.Score
	}

	submission := existingSA.Submission
	if req.Submission != nil {
		submission = sql.NullString{String: *req.Submission, Valid: true}
	}

	submittedAt := existingSA.SubmittedAt
	if req.SubmittedAt != nil {
		submittedAt = sql.NullTime{Time: *req.SubmittedAt, Valid: true}
	}

	gradedAt := existingSA.GradedAt
	if req.GradedAt != nil {
		gradedAt = sql.NullTime{Time: *req.GradedAt, Valid: true}
	}

	err = s.q.UpdateStudentAssignment(ctx, gen.UpdateStudentAssignmentParams{
		Status:      status,
		Score:       score,
		Submission:  submission,
		SubmittedAt: submittedAt,
		GradedAt:    gradedAt,
		ID:          id,
	})
	if err != nil {
		return dtos.StudentAssignmentDTO{}, err
	}

	updatedSA, err := s.q.GetStudentAssignmentByID(ctx, id)
	if err != nil {
		return dtos.StudentAssignmentDTO{}, err
	}

	var studentAssignmentDTO dtos.StudentAssignmentDTO
	studentAssignmentDTO.FromStudentAssignmentModel(updatedSA)
	return studentAssignmentDTO, nil
}

// DeleteStudentAssignment deletes a student's assignment record
func (s *AssignmentService) DeleteStudentAssignment(ctx context.Context, id int64) error {
	return s.q.DeleteStudentAssignment(ctx, id)
}
