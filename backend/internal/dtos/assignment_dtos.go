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

// FromAssignments converts a slice of models.Assignment to a slice of AssignmentResponse DTOs.
func (dto *AssignmentListResponse) FromAssignments(assignments []models.Assignment) {
	dto.Assignments = make([]AssignmentResponse, len(assignments))
	for i, a := range assignments {
		var assignmentDTO AssignmentResponse
		assignmentDTO.FromAssignment(a)
		dto.Assignments[i] = assignmentDTO
	}
}