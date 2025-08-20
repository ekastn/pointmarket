package dtos

import (
	"pointmarket/backend/internal/store/gen"
	"time"
)

// WeeklyEvaluationDetailDTO represents an augmented weekly evaluation with score details
type WeeklyEvaluationDetailDTO struct {
	ID                       int64      `json:"id"`
	StudentID                int64      `json:"student_id"`
	QuestionnaireID          int32      `json:"questionnaire_id"`
	QuestionnaireTitle       string     `json:"questionnaire_title"`
	QuestionnaireType        string     `json:"questionnaire_type"`
	QuestionnaireDescription *string    `json:"questionnaire_description,omitempty"`
	Status                   string     `json:"status"`
	DueDate                  time.Time  `json:"due_date"`
	CompletedAt              *time.Time `json:"completed_at,omitempty"` // Actual completion time from results
	Score                    *float64   `json:"score,omitempty"`        // Score from results
}

// FromWeeklyEvaluation converts a gen.GetWeeklyEvaluationsByStudentIDRow to WeeklyEvaluationDetailDTO
func FromWeeklyEvaluation(we gen.GetWeeklyEvaluationsByStudentIDRow) WeeklyEvaluationDetailDTO {
	dto := WeeklyEvaluationDetailDTO{
		ID:                 we.ID,
		StudentID:          we.StudentID,
		QuestionnaireID:    we.QuestionnaireID,
		QuestionnaireTitle: we.QuestionnaireTitle,
		QuestionnaireType:  string(we.QuestionnaireType),
		Status:             string(we.Status),
		DueDate:            we.DueDate,
	}
	if we.QuestionnaireDescription.Valid {
		dto.QuestionnaireDescription = &we.QuestionnaireDescription.String
	}
	return dto
}
