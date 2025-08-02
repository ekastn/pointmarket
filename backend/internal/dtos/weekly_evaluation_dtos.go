package dtos

import (
	"pointmarket/backend/internal/models"
	"time"
)

// StudentEvaluationStatusDTO represents the data for a single student's evaluation status.
type StudentEvaluationStatusDTO struct {
	StudentID         uint       `json:"student_id"`
	StudentName       string     `json:"student_name"`
	StudentEmail      string     `json:"student_email"`
	CompletedThisWeek bool       `json:"completed_this_week"`
	PendingThisWeek   bool       `json:"pending_this_week"`
	OverdueThisWeek   bool       `json:"overdue_this_week"`
	MslqScoreThisWeek *float64   `json:"mslq_score_this_week"`
	AmsScoreThisWeek  *float64   `json:"ams_score_this_week"`
	LastEvaluation    *time.Time `json:"last_evaluation"`
}

// WeeklyEvaluationOverviewDTO represents the aggregated data for a single week's evaluations.
type WeeklyEvaluationOverviewDTO struct {
	WeekNumber        int      `json:"week_number"`
	Year              int      `json:"year"`
	QuestionnaireType string   `json:"questionnaire_type"`
	TotalCount        int      `json:"total_count"`
	CompletedCount    int      `json:"completed_count"`
	PendingCount      int      `json:"pending_count"`
	OverdueCount      int      `json:"overdue_count"`
	AverageScore      *float64 `json:"average_score"`
}

// WeeklyEvaluationProgressDTO represents the data for a student's progress on a weekly evaluation.
type WeeklyEvaluationProgressDTO struct {
	Year              int        `json:"year"`
	WeekNumber        int        `json:"week_number"`
	QuestionnaireName string     `json:"questionnaire_name"`
	MslqScore         *float64   `json:"mslq_score"`
	AmsScore          *float64   `json:"ams_score"`
	Status            string     `json:"status"`
	DueDate           time.Time  `json:"due_date"`
	CompletedAt       *time.Time `json:"completed_at"`
}

// PendingWeeklyEvaluationDTO represents the data for a student's pending weekly evaluation.
type PendingWeeklyEvaluationDTO struct {
	Year              int       `json:"year"`
	WeekNumber        int       `json:"week_number"`
	QuestionnaireName string    `json:"questionnaire_name"`
	Status            string    `json:"status"`
	DueDate           time.Time `json:"due_date"`
}

// ToStudentEvaluationStatusDTO maps a StudentEvaluationStatus model to a DTO.
func ToStudentEvaluationStatusDTO(m models.StudentEvaluationStatus) StudentEvaluationStatusDTO {
	return StudentEvaluationStatusDTO{
		StudentID:         uint(m.StudentID),
		StudentName:       m.StudentName,
		StudentEmail:      m.StudentEmail,
		CompletedThisWeek: m.CompletedThisWeek > 0,
		PendingThisWeek:   m.PendingThisWeek > 0,
		OverdueThisWeek:   m.OverdueThisWeek > 0,
		MslqScoreThisWeek: m.MSLQScoreThisWeek,
		AmsScoreThisWeek:  m.AMSScoreThisWeek,
		LastEvaluation:    m.LastEvaluation,
	}
}

// ToWeeklyEvaluationOverviewDTO maps a WeeklyEvaluationOverview model to a DTO.
func ToWeeklyEvaluationOverviewDTO(m models.WeeklyEvaluationOverview) WeeklyEvaluationOverviewDTO {
	return WeeklyEvaluationOverviewDTO{
		WeekNumber:        m.WeekNumber,
		Year:              m.Year,
		QuestionnaireType: m.QuestionnaireType,
		TotalCount:        m.TotalCount,
		CompletedCount:    m.CompletedCount,
		PendingCount:      m.PendingCount,
		OverdueCount:      m.OverdueCount,
		AverageScore:      m.AverageScore,
	}
}

// ToWeeklyEvaluationProgressDTO maps a WeeklyEvaluationProgress model to a DTO.
func ToWeeklyEvaluationProgressDTO(m models.WeeklyEvaluationProgress) WeeklyEvaluationProgressDTO {
	var dueDate time.Time
	if m.DueDate != nil {
		dueDate = *m.DueDate
	}
	return WeeklyEvaluationProgressDTO{
		Year:              m.Year,
		WeekNumber:        m.WeekNumber,
		QuestionnaireName: m.QuestionnaireName,
		MslqScore:         m.MSLQScore,
		AmsScore:          m.AMSScore,
		Status:            m.Status,
		DueDate:           dueDate,
		CompletedAt:       m.CompletedAt,
	}
}

// ToPendingWeeklyEvaluationDTO maps a WeeklyEvaluationProgress model to a PendingWeeklyEvaluationDTO.
func ToPendingWeeklyEvaluationDTO(m models.WeeklyEvaluationProgress) PendingWeeklyEvaluationDTO {
	var dueDate time.Time
	if m.DueDate != nil {
		dueDate = *m.DueDate
	}
	return PendingWeeklyEvaluationDTO{
		Year:              m.Year,
		WeekNumber:        m.WeekNumber,
		QuestionnaireName: m.QuestionnaireName,
		Status:            m.Status,
		DueDate:           dueDate,
	}
}
