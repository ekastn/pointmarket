package services

import (
	"context"
	"database/sql"
	"errors"
	"fmt"
	"log"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
	"pointmarket/backend/internal/utils"
	"time"

	mysql "github.com/go-sql-driver/mysql"
)

// AssignmentService provides business logic for assignments and student assignments
type AssignmentService struct {
	q            gen.Querier
	points       *PointsService
	studentService *StudentService
}

// NewAssignmentService creates a new AssignmentService
func NewAssignmentService(q gen.Querier, ps *PointsService, ss *StudentService) *AssignmentService {
	return &AssignmentService{q: q, points: ps, studentService: ss}
}

// CreateAssignment creates a new assignment
func (s *AssignmentService) CreateAssignment(ctx context.Context, req dtos.CreateAssignmentRequestDTO) (dtos.AssignmentDTO, error) {
	result, err := s.q.CreateAssignment(ctx, gen.CreateAssignmentParams{
		Title:        req.Title,
		Description:  utils.NullString(req.Description),
		CourseID:     req.CourseID,
		RewardPoints: req.RewardPoints,
		DueDate:      utils.NullTime(req.DueDate),
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
	var assignments []gen.GetAssignmentsRow
	var err error

	switch userRole {
	case "admin":
		if courseIDFilter != nil {
			adminAssignments, adminErr := s.q.GetAssignmentsByCourseID(ctx, *courseIDFilter)
			if adminErr != nil {
				return nil, adminErr
			}
			assignments = make([]gen.GetAssignmentsRow, len(adminAssignments))
			for i, a := range adminAssignments {
				assignments[i] = gen.GetAssignmentsRow{
					ID: a.ID, Title: a.Title, Description: a.Description, CourseID: a.CourseID,
					RewardPoints: a.RewardPoints, DueDate: a.DueDate, Status: a.Status,
					CreatedAt: a.CreatedAt, UpdatedAt: a.UpdatedAt, CourseTitle: a.CourseTitle,
				}
			}
		} else {
			assignments, err = s.q.GetAssignments(ctx)
		}
	case "guru": // Teacher
		teacherAssignments, teacherErr := s.q.GetAssignmentsByOwnerID(ctx, userID)
		if teacherErr != nil {
			return nil, teacherErr
		}
		assignments = make([]gen.GetAssignmentsRow, len(teacherAssignments))
		for i, a := range teacherAssignments {
			assignments[i] = gen.GetAssignmentsRow{
				ID: a.ID, Title: a.Title, Description: a.Description, CourseID: a.CourseID,
				RewardPoints: a.RewardPoints, DueDate: a.DueDate, Status: a.Status,
				CreatedAt: a.CreatedAt, UpdatedAt: a.UpdatedAt, CourseTitle: a.CourseTitle,
			}
		}
	case "siswa": // Student
		assignments, err = s.q.GetAssignments(ctx)
	default:
		return nil, fmt.Errorf("unsupported user role: %s", userRole)
	}

	if err != nil { // Catch errors from admin/student paths
		return nil, err
	}

	var assignmentDTOs []dtos.AssignmentDTO
	now := time.Now()
	for _, assignment := range assignments {
		var assignmentDTO dtos.AssignmentDTO
		assignmentDTO.FromGetAssignmentsRow(assignment)

		if userRole == "siswa" {
			if !s.isAssignmentVisible(now, gen.Assignment{
				ID: assignment.ID, Title: assignment.Title, Description: assignment.Description,
				CourseID: assignment.CourseID, RewardPoints: assignment.RewardPoints,
				DueDate: assignment.DueDate, Status: assignment.Status,
				CreatedAt: assignment.CreatedAt, UpdatedAt: assignment.UpdatedAt,
			}) {
				continue
			}
		}
		assignmentDTOs = append(assignmentDTOs, assignmentDTO)
	}
	return assignmentDTOs, nil
}

// isAssignmentVisible determines if an assignment is visible to students
func (s *AssignmentService) isAssignmentVisible(now time.Time, a gen.Assignment) bool {
	// Minimal rule: status must be published; due_date ignored for M1
	if a.Status.Valid && string(a.Status.AssignmentsStatus) == "published" {
		return true
	}
	return false
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
	// Idempotency: check if already started
	if existing, err := s.q.GetStudentAssignmentByIDs(ctx, gen.GetStudentAssignmentByIDsParams{UserID: req.StudentID, AssignmentID: req.AssignmentID}); err == nil && existing.ID != 0 {
		return dtos.StudentAssignmentDTO{}, ErrAlreadyStarted
	}

	result, err := s.q.CreateStudentAssignment(ctx, gen.CreateStudentAssignmentParams{
		UserID:       req.StudentID,
		AssignmentID: req.AssignmentID,
		Status:       gen.NullStudentAssignmentsStatus{StudentAssignmentsStatus: gen.StudentAssignmentsStatus(req.Status), Valid: req.Status != ""},
		Submission:   sql.NullString{String: "", Valid: false},
	})
	if err != nil {
		var me *mysql.MySQLError
		if errors.As(err, &me) && me.Number == 1062 {
			return dtos.StudentAssignmentDTO{}, ErrAlreadyStarted
		}
		return dtos.StudentAssignmentDTO{}, err
	}

	newlyCreatedID, err := result.LastInsertId()
	if err != nil {
		return dtos.StudentAssignmentDTO{}, err
	}

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

	feedback := existingSA.Feedback
	if req.Feedback != nil {
		feedback = sql.NullString{String: *req.Feedback, Valid: true}
	}

	graderUserID := existingSA.GraderUserID
	if req.GraderUserID != nil {
		graderUserID = sql.NullInt64{Int64: *req.GraderUserID, Valid: true}
	}

	err = s.q.UpdateStudentAssignment(ctx, gen.UpdateStudentAssignmentParams{
		Status:       status,
		Score:        score,
		Submission:   submission,
		Feedback:     feedback,
		SubmittedAt:  submittedAt,
		GradedAt:     gradedAt,
		GraderUserID: graderUserID,
		ID:           id,
	})
	if err != nil {
		return dtos.StudentAssignmentDTO{}, err
	}

	updatedSA, err := s.q.GetStudentAssignmentByID(ctx, id)
	if err != nil {
		return dtos.StudentAssignmentDTO{}, err
	}

	// Award points on transition to completed
	if s.points != nil {
		prevCompleted := existingSA.Status.Valid && string(existingSA.Status.StudentAssignmentsStatus) == "completed"
		nowCompleted := updatedSA.Status.Valid && string(updatedSA.Status.StudentAssignmentsStatus) == "completed"
		if !prevCompleted && nowCompleted {
			// Load assignment to get reward points
			asg, err2 := s.q.GetAssignmentByID(ctx, updatedSA.AssignmentID)
			if err2 == nil && asg.RewardPoints > 0 {
				refID := id
				// Resolve userID from student_id
				if stRow, err3 := s.q.GetStudentByStudentID(ctx, updatedSA.StudentID); err3 == nil {
					if _, err4 := s.points.Add(ctx, stRow.UserID, int64(asg.RewardPoints), "assignment_completed", "assignment", &refID); err4 != nil {
						log.Printf("points award failed: context=assignment_completed user_id=%d ref_id=%d error=%v", stRow.UserID, refID, err4)
					}
				} else if err3 != nil {
					log.Printf("points award skipped: cannot resolve user_id from student_id=%s error=%v", updatedSA.StudentID, err3)
				}
			}
		}
	}

	// Recalculate academic score if the assignment is graded
	if updatedSA.Status.Valid && string(updatedSA.Status.StudentAssignmentsStatus) == "graded" {
		if stRow, err := s.q.GetStudentByStudentID(ctx, updatedSA.StudentID); err == nil {
			if err := s.studentService.recalculateAcademicScore(ctx, stRow.UserID); err != nil {
				log.Printf("ERROR: could not recalculate academic score for user %d: %v", stRow.UserID, err)
			}
		}
	}

	var studentAssignmentDTO dtos.StudentAssignmentDTO
	studentAssignmentDTO.FromStudentAssignmentModel(updatedSA)
	return studentAssignmentDTO, nil
}

// DeleteStudentAssignment deletes a student's assignment record
func (s *AssignmentService) DeleteStudentAssignment(ctx context.Context, id int64) error {
	return s.q.DeleteStudentAssignment(ctx, id)
}

// SubmitOwnAssignment updates the current user's assignment record by assignment ID
func (s *AssignmentService) SubmitOwnAssignment(ctx context.Context, userID, assignmentID int64, req dtos.UpdateStudentAssignmentRequestDTO) (dtos.StudentAssignmentDTO, error) {
	sa, err := s.q.GetStudentAssignmentByIDs(ctx, gen.GetStudentAssignmentByIDsParams{UserID: userID, AssignmentID: assignmentID})
	if err == sql.ErrNoRows {
		return dtos.StudentAssignmentDTO{}, nil
	}
	if err != nil {
		return dtos.StudentAssignmentDTO{}, err
	}
	// Enforce completed status and submitted_at if not provided
	status := sa.Status
	if req.Status != nil {
		status = gen.NullStudentAssignmentsStatus{StudentAssignmentsStatus: gen.StudentAssignmentsStatus(*req.Status), Valid: *req.Status != ""}
	} else {
		status = gen.NullStudentAssignmentsStatus{StudentAssignmentsStatus: gen.StudentAssignmentsStatus("completed"), Valid: true}
	}
	submittedAt := sa.SubmittedAt
	if req.SubmittedAt != nil {
		submittedAt = sql.NullTime{Time: *req.SubmittedAt, Valid: true}
	} else {
		submittedAt = sql.NullTime{Time: time.Now(), Valid: true}
	}
	submission := sa.Submission
	if req.Submission != nil {
		submission = sql.NullString{String: *req.Submission, Valid: true}
	}

	// Reuse UpdateStudentAssignment to centralize awarding logic
	// Build payload with available values
	st := string(status.StudentAssignmentsStatus)
	payload := dtos.UpdateStudentAssignmentRequestDTO{Status: &st}
	// carry score
	payload.Score = sa.Score
	if submission.Valid {
		sub := submission.String
		payload.Submission = &sub
	}
	if submittedAt.Valid {
		t := submittedAt.Time
		payload.SubmittedAt = &t
	}
	return s.UpdateStudentAssignment(ctx, sa.ID, payload)
}
