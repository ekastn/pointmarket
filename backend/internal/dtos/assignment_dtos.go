package dtos

import (
	"pointmarket/backend/internal/models"
	"time"
)

// ==================
//     Requests
// ==================

type CreateAssignmentRequest struct {
	Title       string    `json:"title" binding:"required"`
	Description string    `json:"description" binding:"required"`
	DueDate     time.Time `json:"due_date" binding:"required"`
	Subject     string    `json:"subject" binding:"required"`
	Points      int       `json:"points" binding:"required"`
	TeacherID   int       `json:"teacher_id" binding:"required"`
}

type UpdateAssignmentRequest struct {
	Title       string    `json:"title"`
	Description string    `json:"description"`
	DueDate     time.Time `json:"due_date"`
	Subject     string    `json:"subject"`
	Points      int       `json:"points"`
}

// ==================
//     Responses
// ==================

type AssignmentResponse struct {
	ID          int        `json:"id"`
	Title       string     `json:"title"`
	Description *string    `json:"description"`
	DueDate     *time.Time `json:"due_date"`
	TeacherID   int        `json:"teacher_id"`
	Subject     string     `json:"subject"`
	Points      int        `json:"points"`
	Status      string     `json:"status"`
	CreatedAt   time.Time  `json:"created_at"`
	UpdatedAt   time.Time  `json:"updated_at"`
}

// StudentAssignmentResponse represents a student's specific assignment record for API responses
type StudentAssignmentResponse struct {
	Status      string     `json:"student_status"`
	Score       *float64   `json:"score"`
	SubmittedAt *time.Time `json:"submitted_at"`
	GradedAt    *time.Time `json:"graded_at"`
}

// AssignmentDetailResponse combines Assignment and StudentAssignment data for a comprehensive view
type AssignmentDetailResponse struct {
	ID            int        `json:"id"`
	Title         string     `json:"title"`
	Description   *string    `json:"description"`
	Subject       string     `json:"subject"`
	TeacherID     int        `json:"teacher_id"`
	Points        int        `json:"points"`
	DueDate       *time.Time `json:"due_date"`
	Status        string     `json:"status"` // Assignment status (e.g., Published)
	CreatedAt     time.Time  `json:"created_at"`
	UpdatedAt     time.Time  `json:"updated_at"`
	StudentStatus string     `json:"student_status"` // Student's status for this assignment
	Score         *float64   `json:"score"`
	SubmittedAt   *time.Time `json:"submitted_at"`
	GradedAt      *time.Time `json:"graded_at"`
	TeacherName   string     `json:"teacher_name"`
	UrgencyStatus string     `json:"urgency_status"`
	DaysRemaining int        `json:"days_remaining"`
}

type AssignmentListResponse struct {
	Assignments []AssignmentResponse `json:"assignments"`
}

// FromAssignment converts a models.Assignment to a AssignmentResponse DTO.
func (dto *AssignmentResponse) FromAssignment(assignment models.Assignment) {
	dto.ID = assignment.ID
	dto.Title = assignment.Title
	dto.Description = assignment.Description
	dto.DueDate = assignment.DueDate
	dto.TeacherID = assignment.TeacherID
	dto.Subject = assignment.Subject
	dto.Points = assignment.Points
	dto.Status = assignment.Status
	dto.CreatedAt = assignment.CreatedAt
	dto.UpdatedAt = assignment.UpdatedAt
}

// FromAssignmentAndStudentAssignment converts models.Assignment and models.StudentAssignment to AssignmentDetailResponse
func (dto *AssignmentDetailResponse) FromAssignmentAndStudentAssignment(assignment models.Assignment, studentAssignment *models.StudentAssignment, teacherName string) {
	dto.ID = assignment.ID
	dto.Title = assignment.Title
	dto.Description = assignment.Description
	dto.Subject = assignment.Subject
	dto.TeacherID = assignment.TeacherID
	dto.Points = assignment.Points
	dto.DueDate = assignment.DueDate
	dto.Status = assignment.Status
	dto.CreatedAt = assignment.CreatedAt
	dto.UpdatedAt = assignment.UpdatedAt
	dto.TeacherName = teacherName

	if studentAssignment != nil {
		dto.StudentStatus = studentAssignment.Status
		dto.Score = studentAssignment.Score
		dto.SubmittedAt = studentAssignment.SubmittedAt
		dto.GradedAt = studentAssignment.GradedAt
	} else {
		dto.StudentStatus = "not_started"
	}

	// Calculate urgency status and days remaining
	if assignment.DueDate != nil {
		daysRemaining := int(assignment.DueDate.Sub(time.Now()).Hours() / 24)
		dto.DaysRemaining = daysRemaining
		if daysRemaining < 0 && dto.StudentStatus != "completed" {
			dto.UrgencyStatus = "overdue"
		} else if daysRemaining <= 2 && dto.StudentStatus == "not_started" {
			dto.UrgencyStatus = "due_soon"
		} else {
			dto.UrgencyStatus = "normal"
		}
	} else {
		dto.UrgencyStatus = "normal"
		dto.DaysRemaining = 0
	}
}

// FromAssignments converts a slice of models.Assignment to a slice of AssignmentResponse DTOs.
func (dto *AssignmentListResponse) FromAssignments(assignments []models.Assignment) {
	dto.Assignments = make([]AssignmentResponse, len(assignments))
	for i, a := range assignments {
		var assignmentDTO AssignmentResponse
		assignmentDTO.FromAssignment(a)
		dto.Assignments[i] = assignmentDTO
	}
}
