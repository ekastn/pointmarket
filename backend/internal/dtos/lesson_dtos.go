package dtos

import (
	"encoding/json"
	"pointmarket/backend/internal/store/gen"
)

// LessonDTO represents a lesson entity
type LessonDTO struct {
	ID       int64           `json:"id"`
	CourseID int64           `json:"course_id"`
	Title    string          `json:"title"`
	Ordinal  int32           `json:"ordinal"`
	Content  json.RawMessage `json:"content"`
}

func (d *LessonDTO) FromLessonModel(m gen.Lesson) {
	d.ID = m.ID
	d.CourseID = m.CourseID
	d.Title = m.Title
	d.Ordinal = m.Ordinal
	d.Content = m.Content
}

// CreateLessonRequestDTO request to create a lesson
type CreateLessonRequestDTO struct {
	CourseID int64           `json:"course_id" binding:"required"`
	Title    string          `json:"title" binding:"required"`
	Ordinal  int32           `json:"ordinal" binding:"required"`
	Content  json.RawMessage `json:"content"`
}

// UpdateLessonRequestDTO request to update a lesson
type UpdateLessonRequestDTO struct {
	Title   *string         `json:"title"`
	Ordinal *int32          `json:"ordinal"`
	Content json.RawMessage `json:"content"`
}

// ListLessonsResponseDTO list response
type ListLessonsResponseDTO struct {
	Lessons []LessonDTO `json:"lessons"`
	Total   int64       `json:"total"`
}
