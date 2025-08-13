package dtos

import (
	"pointmarket/backend/internal/store/gen"
	"time"
)

type QuestionnaireDTO struct {
	ID             int32     `json:"id"`
	Type           string    `json:"type"`
	Name           string    `json:"name"`
	Description    *string   `json:"description,omitempty"`
	TotalQuestions int32     `json:"total_questions"`
	Status         string    `json:"status"`
	CreatedAt      time.Time `json:"created_at"`
}

type QuestionnaireQuestionDTO struct {
	ID              int32   `json:"id"`
	QuestionnaireID int32   `json:"questionnaire_id"`
	Number          int32   `json:"number"`
	Text            string  `json:"text"`
	Subscale        *string `json:"subscale,omitempty"`
}

type LikertSubmissionRequestDTO struct {
	QuestionnaireID    int32             `json:"questionnaire_id"`
	Answers            map[string]string `json:"answers"`
	WeeklyEvaluationID *int64            `json:"weekly_evaluation_id,omitempty"`
}

type LikertSubmissionResponseDTO struct {
	TotalScore float64 `json:"total_score"`
}

type VarkSubmissionRequestDTO struct {
	QuestionnaireID int32             `json:"questionnaire_id"`
	Answers         map[string]string `json:"answers"`
	Text            string            `json:"text"`
}

type VarkAnswerDetailDTO struct {
	QuestionID int64  `json:"question_id"`
	Answer     string `json:"answer"`
	Style      string `json:"style"`
}

type LearningStyleDTO struct {
	Type      string     `json:"type"` // dominant | multimodal
	Label     string     `json:"label"`
	Scores    VARKScores `json:"scores"`
	CreatedAt *time.Time `json:"created_at,omitempty"`
}

type VarkSubmissionResponseDTO struct {
	QuestionnaireID int64                 `json:"questionnaire_id"`
	StudentID       int64                 `json:"student_id"`
	Scores          VARKScores            `json:"scores"`
	Style           LearningStyleDTO      `json:"style"`
	RawAnswers      []VarkAnswerDetailDTO `json:"raw_answers"`
	CreatedAt       time.Time             `json:"created_at"`
}

type LikertResultDTO struct {
	ID              int64              `json:"id"`
	QuestionnaireID int64              `json:"questionnaire_id"`
	StudentID       int64              `json:"student_id"`
	Answers         []int              `json:"answers"`
	TotalScore      float64            `json:"total_score"`
	SubscaleScores  map[string]float64 `json:"subscale_scores"`
	CreatedAt       time.Time          `json:"created_at"`
}

type VarkResultDTO struct {
	ID              int64                 `json:"id"`
	QuestionnaireID int64                 `json:"questionnaire_id"`
	StudentID       int64                 `json:"student_id"`
	Scores          VARKScores            `json:"scores"`
	Answers         []VarkAnswerDetailDTO `json:"answers"`
	CreatedAt       time.Time             `json:"created_at"`
}

type LikertStatsDTO struct {
	QuestionnaireID int64    `json:"questionnaire_id"`
	Type            string   `json:"type"`
	Name            string   `json:"name"`
	Attempts        int64    `json:"attempts"`
	AverageScore    *float64 `json:"average_score"`
	BestScore       *float64 `json:"best_score"`
	LowestScore     *float64 `json:"lowest_score"`
}

type QuestionnaireDetailResponseDTO struct {
	Questionnaire QuestionnaireDTO           `json:"questionnaire"`
	Questions     []QuestionnaireQuestionDTO `json:"questions"`
}

func (d *QuestionnaireDTO) FromQuestionnaire(q gen.Questionnaire) {
	d.ID = q.ID
	d.Type = string(q.Type)
	d.Name = q.Name
	d.Description = &q.Description.String
	d.TotalQuestions = q.TotalQuestions

	if q.Status.Valid {
		d.Status = string(q.Status.QuestionnairesStatus)
	} else {
		d.Status = string(gen.QuestionnairesStatusInactive)
	}

	if q.CreatedAt.Valid {
		d.CreatedAt = q.CreatedAt.Time
	} else {
		d.CreatedAt = time.Time{}
	}
}

func (d *QuestionnaireQuestionDTO) FromQuestion(q gen.QuestionnaireQuestion) {
	d.ID = q.ID
	d.QuestionnaireID = q.QuestionnaireID
	d.Number = q.QuestionNumber
	d.Text = q.QuestionText
	d.Subscale = &q.Subscale.String
}
