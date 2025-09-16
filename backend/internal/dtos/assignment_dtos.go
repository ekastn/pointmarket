package dtos

import (
	"encoding/json"
	"time"

	"pointmarket/backend/internal/store/gen" // Import the sqlc generated models
)

// --- Assignment DTOs ---

// AssignmentDTO represents an assignment for API responses
type AssignmentDTO struct {
	ID           int64      `json:"id"`
	Title        string     `json:"title"`
	Description  *string    `json:"description"` // Use pointer for nullable
	CourseID     int64      `json:"course_id"`
	RewardPoints int32      `json:"reward_points"`
	DueDate      *time.Time `json:"due_date"` // Use pointer for nullable
	Status       string     `json:"status"`
	CreatedAt    time.Time  `json:"created_at"`
	UpdatedAt    time.Time  `json:"updated_at"`
}

// FromAssignmentModel converts a gen.Assignment model to an AssignmentDTO
func (dto *AssignmentDTO) FromAssignmentModel(m gen.Assignment) {
	dto.ID = m.ID
	dto.Title = m.Title
	if m.Description.Valid {
		dto.Description = &m.Description.String
	} else {
		dto.Description = nil
	}
	dto.CourseID = m.CourseID
	dto.RewardPoints = m.RewardPoints
	if m.DueDate.Valid {
		dto.DueDate = &m.DueDate.Time
	} else {
		dto.DueDate = nil
	}
	// Handle NullAssignmentsStatus
	if m.Status.Valid {
		dto.Status = string(m.Status.AssignmentsStatus)
	} else {
		dto.Status = "" // Or a default status string
	}
	dto.CreatedAt = m.CreatedAt // Assuming CreatedAt is NOT nullable in DB
	dto.UpdatedAt = m.UpdatedAt // Assuming UpdatedAt is NOT nullable in DB
}

// CreateAssignmentRequestDTO for creating a new assignment
type CreateAssignmentRequestDTO struct {
	Title        string     `json:"title" binding:"required"`
	Description  *string    `json:"description"`
	CourseID     int64      `json:"course_id" binding:"required"`
	RewardPoints int32      `json:"reward_points" binding:"required"`
	DueDate      *time.Time `json:"due_date"`
	Status       string     `json:"status"` // e.g., "draft", "published"
}

// UpdateAssignmentRequestDTO for updating an existing assignment
type UpdateAssignmentRequestDTO struct {
	Title        *string    `json:"title"`
	Description  *string    `json:"description"`
	CourseID     *int64     `json:"course_id"`
	RewardPoints *int32     `json:"reward_points"`
	DueDate      *time.Time `json:"due_date"`
	Status       *string    `json:"status"`
}

// ListAssignmentsResponseDTO contains a list of AssignmentDTOs
type ListAssignmentsResponseDTO struct {
	Assignments []AssignmentDTO `json:"assignments"`
	Total       int             `json:"total"`
}

// --- Student Assignment DTOs ---

// StudentAssignmentDTO represents a student's assignment record for API responses
type StudentAssignmentDTO struct {
	ID           int64            `json:"id"`
	StudentID    string           `json:"student_id"`
	AssignmentID int64            `json:"assignment_id"`
	Status       string           `json:"status"`
	Attempt      int32            `json:"attempt"`
	Score        *float64         `json:"score"`      // Use pointer for nullable
	Submission   *string          `json:"submission"` // Use pointer for nullable
	Feedback     *string          `json:"feedback"`
	SubmittedAt  *time.Time       `json:"submitted_at"` // Use pointer for nullable
	GradedAt     *time.Time       `json:"graded_at"`    // Use pointer for nullable
	GraderUserID *int64           `json:"grader_user_id"`
	Attachments  *json.RawMessage `json:"attachments"`
	CreatedAt    time.Time        `json:"created_at"`
	UpdatedAt    time.Time        `json:"updated_at"`

	// Joined assignment details
	AssignmentTitle        string     `json:"assignment_title"`
	AssignmentDescription  *string    `json:"assignment_description"`
	AssignmentCourseID     int64      `json:"assignment_course_id"`
	AssignmentRewardPoints int32      `json:"assignment_reward_points"`
	AssignmentDueDate      *time.Time `json:"assignment_due_date"`

	// Joined student details (for GetStudentAssignmentsByAssignmentID)
	StudentName  *string `json:"student_name"`
	StudentEmail *string `json:"student_email"`
}

// FromStudentAssignmentModel converts a gen.StudentAssignment model to a StudentAssignmentDTO
// This is for basic StudentAssignment without joins
func (dto *StudentAssignmentDTO) FromStudentAssignmentModel(m gen.StudentAssignment) {
	dto.ID = m.ID
	dto.StudentID = m.StudentID
	dto.AssignmentID = m.AssignmentID
	// Handle NullStudentAssignmentsStatus
	if m.Status.Valid {
		dto.Status = string(m.Status.StudentAssignmentsStatus)
	} else {
		dto.Status = "" // Or a default status string
	}
	// Attempt (non-nullable with default 1)
	dto.Attempt = m.Attempt
	dto.Score = m.Score
	if m.Submission.Valid {
		dto.Submission = &m.Submission.String
	} else {
		dto.Submission = nil
	}
	if m.Feedback.Valid {
		dto.Feedback = &m.Feedback.String
	} else {
		dto.Feedback = nil
	}
	if m.SubmittedAt.Valid {
		dto.SubmittedAt = &m.SubmittedAt.Time
	} else {
		dto.SubmittedAt = nil
	}
	if m.GradedAt.Valid {
		dto.GradedAt = &m.GradedAt.Time
	} else {
		dto.GradedAt = nil
	}
	if m.GraderUserID.Valid {
		dto.GraderUserID = &m.GraderUserID.Int64
	} else {
		dto.GraderUserID = nil
	}
	if m.Attachments != nil && len(m.Attachments) > 0 {
		rm := json.RawMessage(m.Attachments)
		dto.Attachments = &rm
	} else {
		dto.Attachments = nil
	}
	// Handle sql.NullTime for CreatedAt and UpdatedAt
	if m.CreatedAt.Valid {
		dto.CreatedAt = m.CreatedAt.Time
	} else {
		dto.CreatedAt = time.Time{} // Default zero value
	}
	if m.UpdatedAt.Valid {
		dto.UpdatedAt = m.UpdatedAt.Time
	} else {
		dto.UpdatedAt = time.Time{} // Default zero value
	}
}

// FromGetStudentAssignmentsByStudentIDRow converts a gen.GetStudentAssignmentsByStudentIDRow to a StudentAssignmentDTO
func (dto *StudentAssignmentDTO) FromGetStudentAssignmentsByStudentIDRow(m gen.GetStudentAssignmentsByStudentIDRow) {
	dto.ID = m.ID
	dto.StudentID = m.StudentID
	dto.AssignmentID = m.AssignmentID
	// Handle NullStudentAssignmentsStatus
	if m.Status.Valid {
		dto.Status = string(m.Status.StudentAssignmentsStatus)
	} else {
		dto.Status = "" // Or a default status string
	}
	dto.Attempt = m.Attempt
	dto.Score = m.Score
	if m.Submission.Valid {
		dto.Submission = &m.Submission.String
	} else {
		dto.Submission = nil
	}
	if m.Feedback.Valid {
		dto.Feedback = &m.Feedback.String
	} else {
		dto.Feedback = nil
	}
	if m.SubmittedAt.Valid {
		dto.SubmittedAt = &m.SubmittedAt.Time
	} else {
		dto.SubmittedAt = nil
	}
	if m.GradedAt.Valid {
		dto.GradedAt = &m.GradedAt.Time
	} else {
		dto.GradedAt = nil
	}
	if m.GraderUserID.Valid {
		dto.GraderUserID = &m.GraderUserID.Int64
	} else {
		dto.GraderUserID = nil
	}
	if m.Attachments != nil && len(m.Attachments) > 0 {
		rm := json.RawMessage(m.Attachments)
		dto.Attachments = &rm
	} else {
		dto.Attachments = nil
	}
	// Handle sql.NullTime for CreatedAt and UpdatedAt
	if m.CreatedAt.Valid {
		dto.CreatedAt = m.CreatedAt.Time
	} else {
		dto.CreatedAt = time.Time{} // Default zero value
	}
	if m.UpdatedAt.Valid {
		dto.UpdatedAt = m.UpdatedAt.Time
	} else {
		dto.UpdatedAt = time.Time{} // Default zero value
	}

	// Joined assignment details
	dto.AssignmentTitle = m.AssignmentTitle
	if m.AssignmentDescription.Valid {
		dto.AssignmentDescription = &m.AssignmentDescription.String
	} else {
		dto.AssignmentDescription = nil
	}
	dto.AssignmentCourseID = m.AssignmentCourseID
	dto.AssignmentRewardPoints = m.AssignmentRewardPoints
	if m.AssignmentDueDate.Valid {
		dto.AssignmentDueDate = &m.AssignmentDueDate.Time
	} else {
		dto.AssignmentDueDate = nil
	}
}

// FromGetStudentAssignmentsByAssignmentIDRow converts a gen.GetStudentAssignmentsByAssignmentIDRow to a StudentAssignmentDTO
func (dto *StudentAssignmentDTO) FromGetStudentAssignmentsByAssignmentIDRow(m gen.GetStudentAssignmentsByAssignmentIDRow) {
	dto.ID = m.ID
	dto.StudentID = m.StudentID
	dto.AssignmentID = m.AssignmentID
	// Handle NullStudentAssignmentsStatus
	if m.Status.Valid {
		dto.Status = string(m.Status.StudentAssignmentsStatus)
	} else {
		dto.Status = "" // Or a default status string
	}
	dto.Attempt = m.Attempt
	dto.Score = m.Score
	if m.Submission.Valid {
		dto.Submission = &m.Submission.String
	} else {
		dto.Submission = nil
	}
	if m.Feedback.Valid {
		dto.Feedback = &m.Feedback.String
	} else {
		dto.Feedback = nil
	}
	if m.SubmittedAt.Valid {
		dto.SubmittedAt = &m.SubmittedAt.Time
	} else {
		dto.SubmittedAt = nil
	}
	if m.GradedAt.Valid {
		dto.GradedAt = &m.GradedAt.Time
	} else {
		dto.GradedAt = nil
	}
	if m.GraderUserID.Valid {
		dto.GraderUserID = &m.GraderUserID.Int64
	} else {
		dto.GraderUserID = nil
	}
	if m.Attachments != nil && len(m.Attachments) > 0 {
		rm := json.RawMessage(m.Attachments)
		dto.Attachments = &rm
	} else {
		dto.Attachments = nil
	}
	// Handle sql.NullTime for CreatedAt and UpdatedAt
	if m.CreatedAt.Valid {
		dto.CreatedAt = m.CreatedAt.Time
	} else {
		dto.CreatedAt = time.Time{} // Default zero value
	}
	if m.UpdatedAt.Valid {
		dto.UpdatedAt = m.UpdatedAt.Time
	} else {
		dto.UpdatedAt = time.Time{} // Default zero value
	}

	// Joined student details
	dto.StudentName = &m.StudentName   // Directly assign address of string
	dto.StudentEmail = &m.StudentEmail // Directly assign address of string
}

// CreateStudentAssignmentRequestDTO for creating a new student assignment record (e.g., when starting)
type CreateStudentAssignmentRequestDTO struct {
	StudentID    int64   `json:"student_id" binding:"required"`
	AssignmentID int64   `json:"assignment_id" binding:"required"`
	Status       string  `json:"status"` // e.g., "not_started", "in_progress"
	Submission   *string `json:"submission"`
}

// UpdateStudentAssignmentRequestDTO for updating a student assignment record (e.g., submission, score)
type UpdateStudentAssignmentRequestDTO struct {
	Status       *string          `json:"status"` // e.g., "in_progress", "completed"
	Score        *float64         `json:"score"`
	Submission   *string          `json:"submission"`
	Feedback     *string          `json:"feedback"`
	SubmittedAt  *time.Time       `json:"submitted_at"`
	GradedAt     *time.Time       `json:"graded_at"`
	GraderUserID *int64           `json:"grader_user_id"`
	Attachments  *json.RawMessage `json:"attachments"`
}

// ListStudentAssignmentsResponseDTO contains a list of StudentAssignmentDTOs
type ListStudentAssignmentsResponseDTO struct {
	StudentAssignments []StudentAssignmentDTO `json:"student_assignments"`
	Total              int                    `json:"total"`
}
