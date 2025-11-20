package dtos

import (
	"encoding/json"
	"time"

	"pointmarket/backend/internal/store/gen" // Import the sqlc generated models
)

// --- Course DTOs ---

// CourseDTO represents a course for API responses
type CourseDTO struct {
	ID               int64           `json:"id"`
	Title            string          `json:"title"`
	Slug             string          `json:"slug"`
	Description      *string         `json:"description"` // Use pointer for nullable
	OwnerID          int64           `json:"owner_id"`
	OwnerDisplayName string          `json:"owner_display_name"`
	OwnerRole        string          `json:"owner_role"`
	Metadata         json.RawMessage `json:"metadata"`
	CreatedAt        time.Time       `json:"created_at"`
	UpdatedAt        time.Time       `json:"updated_at"`
}

// FromCourseModel converts a gen.Course model to a CourseDTO
func (dto *CourseDTO) FromCourseModel(m gen.Course) {
	dto.ID = m.ID
	dto.Title = m.Title
	dto.Slug = m.Slug
	if m.Description.Valid {
		dto.Description = &m.Description.String
	} else {
		dto.Description = nil
	}
	dto.OwnerID = m.OwnerID
	dto.Metadata = m.Metadata
	dto.CreatedAt = m.CreatedAt
	dto.UpdatedAt = m.UpdatedAt
}

// StudentCoursesDTO for listing courses for students with enrollment status
type StudentCoursesDTO struct {
	CourseDTO
	IsEnrolled bool `json:"is_enrolled"`
}

// FromStudentCoursesModel converts a gen.StudentCourses model to a StudentCoursesDTO
func (dto *StudentCoursesDTO) FromStudentCoursesModel(m gen.GetCoursesWithEnrollmentStatusRow) {
	dto.ID = m.ID
	dto.Title = m.Title
	dto.Slug = m.Slug
	if m.Description.Valid {
		dto.Description = &m.Description.String
	} else {
		dto.Description = nil
	}
	dto.OwnerID = m.OwnerID
	dto.Metadata = m.Metadata
	dto.CreatedAt = m.CreatedAt
	dto.UpdatedAt = m.UpdatedAt
	dto.IsEnrolled = m.IsEnrolled == 1
}

// CreateCourseRequestDTO for creating a new course
type CreateCourseRequestDTO struct {
	Title       string          `json:"title" binding:"required"`
	Slug        string          `json:"slug" binding:"required"`
	Description *string         `json:"description"`
	OwnerID     int64           `json:"owner_id" binding:"required"` // Will be inferred or set by admin
	Metadata    json.RawMessage `json:"metadata" binding:"required"`
}

// UpdateCourseRequestDTO for updating an existing course
type UpdateCourseRequestDTO struct {
	Title       *string         `json:"title"`
	Slug        *string         `json:"slug"`
	Description *string         `json:"description"`
	Metadata    json.RawMessage `json:"metadata"`
}

// ListCoursesResponseDTO contains a list of CourseDTOs
type ListCoursesResponseDTO struct {
	Courses []CourseDTO `json:"courses"`
	Total   int         `json:"total"`
}

// EnrollStudentRequestDTO for enrolling a student in a course
type EnrollStudentRequestDTO struct {
	// Optional for students; backend derives from auth when omitted
	UserID int64 `json:"user_id"`
	// Optional; backend takes course ID from the path
	CourseID int64 `json:"course_id"`
}

// StudentCourseDTO represents a student's enrollment in a course, including course details
type StudentCourseDTO struct {
	StudentID  int64     `json:"student_id"`
	CourseID   int64     `json:"course_id"`
	EnrolledAt time.Time `json:"enrolled_at"`
	// Course details from the join
	CourseTitle       string          `json:"course_title"`
	CourseSlug        string          `json:"course_slug"`
	CourseDescription *string         `json:"course_description"`
	CourseOwnerID     int64           `json:"course_owner_id"`
	CourseMetadata    json.RawMessage `json:"course_metadata"`
}

// ListStudentCoursesResponseDTO for listing a student's enrolled courses
type ListStudentCoursesResponseDTO struct {
	StudentCourses []StudentCoursesDTO `json:"student_courses"`
	Total          int                 `json:"total"`
}
