package dtos

import (
	"encoding/json"
	"pointmarket/backend/internal/store/gen"
	"time"
)

// EnrolledStudentDTO represents basic information for a student enrolled in a course.
type EnrolledStudentDTO struct {
	UserID      int64  `json:"user_id"`
	DisplayName string `json:"display_name"`
	Email       string `json:"email"`
	StudentID   string `json:"student_id"`
}

// CourseDTO represents a course for general purpose
type CourseDTO struct {
	ID               int64                `json:"id"`
	Title            string               `json:"title"`
	Slug             string               `json:"slug"`
	Description      *string              `json:"description,omitempty"`
	OwnerID          int64                `json:"owner_id"`
	OwnerDisplayName string               `json:"owner_display_name,omitempty"`
	OwnerRole        string               `json:"owner_role,omitempty"`
	Metadata         json.RawMessage      `json:"metadata,omitempty"`
	CreatedAt        time.Time            `json:"created_at"`
	UpdatedAt        time.Time            `json:"updated_at"`
	EnrolledStudents []EnrolledStudentDTO `json:"enrolled_students,omitempty"`
}

// FromCourseModel maps the database model to the DTO
func (dto *CourseDTO) FromCourseModel(course gen.Course) {
	dto.ID = course.ID
	dto.Title = course.Title
	dto.Slug = course.Slug
	if course.Description.Valid {
		dto.Description = &course.Description.String
	}
	dto.OwnerID = course.OwnerID
	dto.Metadata = course.Metadata
	dto.CreatedAt = course.CreatedAt
	dto.UpdatedAt = course.UpdatedAt
}

// CreateCourseRequestDTO defines the payload for creating a new course
type CreateCourseRequestDTO struct {
	Title       string          `json:"title" binding:"required"`
	Slug        string          `json:"slug" binding:"required"`
	Description *string         `json:"description"`
	OwnerID     int64           `json:"owner_id" binding:"required"`
	Metadata    json.RawMessage `json:"metadata"`
}

// UpdateCourseRequestDTO defines the payload for updating a course
type UpdateCourseRequestDTO struct {
	Title       *string         `json:"title"`
	Slug        *string         `json:"slug"`
	Description *string         `json:"description"`
	Metadata    json.RawMessage `json:"metadata"`
}

// EnrollStudentRequestDTO defines the payload for enrolling a student in a course
type EnrollStudentRequestDTO struct {
	UserID   int64 `json:"user_id" binding:"required"`
	CourseID int64 `json:"course_id" binding:"required"`
}

// StudentCoursesDTO represents a course with enrollment status for a student
type StudentCoursesDTO struct {
	ID               int64           `json:"id"`
	Title            string          `json:"title"`
	Slug             string          `json:"slug"`
	Description      *string         `json:"description,omitempty"`
	OwnerID          int64           `json:"owner_id"`
	OwnerDisplayName string          `json:"owner_display_name,omitempty"`
	OwnerRole        string          `json:"owner_role,omitempty"`
	Metadata         json.RawMessage `json:"metadata,omitempty"`
	CreatedAt        time.Time       `json:"created_at"`
	UpdatedAt        time.Time       `json:"updated_at"`
	IsEnrolled       bool            `json:"is_enrolled"`
}

// FromStudentCoursesModel maps the database model to the DTO for student view
func (dto *StudentCoursesDTO) FromStudentCoursesModel(course gen.GetCoursesWithEnrollmentStatusRow) {
	dto.ID = course.ID
	dto.Title = course.Title
	dto.Slug = course.Slug
	if course.Description.Valid {
		dto.Description = &course.Description.String
	}
	dto.OwnerID = course.OwnerID
	dto.Metadata = course.Metadata
	dto.CreatedAt = course.CreatedAt
	dto.UpdatedAt = course.UpdatedAt
	dto.IsEnrolled = course.IsEnrolled == 1 // MariaDB/MySQL bool is 1 or 0
}

// TeacherCoursesDTO represents a course with ownership status for a teacher
type TeacherCoursesDTO struct {
	ID               int64           `json:"id"`
	Title            string          `json:"title"`
	Slug             string          `json:"slug"`
	Description      *string         `json:"description,omitempty"`
	OwnerID          int64           `json:"owner_id"`
	OwnerDisplayName string          `json:"owner_display_name,omitempty"`
	OwnerRole        string          `json:"owner_role,omitempty"`
	Metadata         json.RawMessage `json:"metadata,omitempty"`
	CreatedAt        time.Time       `json:"created_at"`
	UpdatedAt        time.Time       `json:"updated_at"`
	IsOwner          bool            `json:"is_owner"`
}

// FromTeacherCoursesModel maps the database model to the DTO for teacher view
func (dto *TeacherCoursesDTO) FromTeacherCoursesModel(course gen.GetCoursesWithOwnershipStatusRow) {
	dto.ID = course.ID
	dto.Title = course.Title
	dto.Slug = course.Slug
	if course.Description.Valid {
		dto.Description = &course.Description.String
	}
	dto.OwnerID = course.OwnerID
	dto.Metadata = course.Metadata
	dto.CreatedAt = course.CreatedAt
	dto.UpdatedAt = course.UpdatedAt
	dto.IsOwner = course.IsOwner == 1 // MariaDB/MySQL bool is 1 or 0
}