package dtos

import (
	"pointmarket/backend/internal/models"
	"time"
)

// ==================
//     Requests
// ==================

type SubmitQuestionnaireRequest struct {
	QuestionnaireID int               `json:"questionnaire_id" binding:"required"`
	Answers         map[string]string `json:"answers" binding:"required"`
	WeekNumber      int               `json:"week_number" binding:"required"`
	Year            int               `json:"year" binding:"required"`
}

// ==================
//     Responses
// ==================

type QuestionnaireResultResponse struct {
	ID                       int       `json:"id"`
	TotalScore               *float64  `json:"total_score"`
	CompletedAt              time.Time `json:"completed_at"`
	WeekNumber               int       `json:"week_number"`
	Year                     int       `json:"year"`
	QuestionnaireName        string    `json:"questionnaire_name"`
	QuestionnaireType        string    `json:"questionnaire_type"`
	QuestionnaireDescription *string   `json:"questionnaire_description"`
}

// FromQuestionnaireResult converts a models.QuestionnaireResult to a QuestionnaireResultResponse DTO.
func (dto *QuestionnaireResultResponse) FromQuestionnaireResult(result models.QuestionnaireResult) {
	dto.ID = result.ID
	dto.TotalScore = result.TotalScore
	dto.CompletedAt = result.CompletedAt
	dto.WeekNumber = result.WeekNumber
	dto.Year = result.Year
	dto.QuestionnaireName = result.QuestionnaireName
	dto.QuestionnaireType = result.QuestionnaireType
	dto.QuestionnaireDescription = result.QuestionnaireDescription
}

type QuestionnaireResponse struct {
	ID             int                `json:"id"`
	Name           string             `json:"name"`
	Description    *string            `json:"description"`
	TotalQuestions int                `json:"total_questions"`
	Questions      []QuestionResponse `json:"questions"`
	RecentResult   *QuestionnaireResultResponse `json:"recent_result,omitempty"`
	CreatedAt      time.Time          `json:"created_at"`
}

type QuestionnaireDetailResponse struct {
	Questionnaire QuestionnaireResponse      `json:"questionnaire"`
	Questions     []QuestionResponse         `json:"questions"`
	RecentResult  *QuestionnaireResultResponse `json:"recent_result,omitempty"`
}

type QuestionnaireListDTO struct {
	ID             int       `json:"id"`
	Type           string    `json:"type"`
	Name           string    `json:"name"`
	Description    *string   `json:"description"`
	TotalQuestions int       `json:"total_questions"`
	CreatedAt      time.Time `json:"created_at"`
}

type QuestionResponse struct {
	ID             int                  `json:"id"`
	QuestionnaireID int                  `json:"questionnaire_id"`
	QuestionNumber int                  `json:"question_number"`
	QuestionText   string               `json:"question_text"`
	Subscale       *string              `json:"subscale"`
	ReverseScored  bool                 `json:"reverse_scored"`
	Options        []VARKAnswerOptionDTO `json:"options,omitempty"` // Added for VARK questions
	CreatedAt      time.Time            `json:"created_at"`
}

type VARKAnswerOptionDTO struct {
	ID          int    `json:"id"`
	QuestionID  int    `json:"question_id"`
	OptionLetter string `json:"option_letter"`
	OptionText  string `json:"option_text"`
}

// FromQuestionnaire converts a models.Questionnaire and its questions to a QuestionnaireResponse DTO.
func (dto *QuestionnaireResponse) FromQuestionnaire(q models.Questionnaire, questions []QuestionResponse) {
	dto.ID = q.ID
	dto.Name = q.Name
	dto.Description = q.Description
	dto.TotalQuestions = q.TotalQuestions
	dto.CreatedAt = q.CreatedAt
	dto.Questions = make([]QuestionResponse, len(questions))
	for i, question := range questions {
		dto.Questions[i] = question
	}
}

// FromQuestion converts a models.QuestionnaireQuestion to a QuestionResponse DTO.
func (dto *QuestionResponse) FromQuestion(question models.QuestionnaireQuestion) {
	dto.ID = question.ID
	dto.QuestionnaireID = question.QuestionnaireID
	dto.QuestionNumber = question.QuestionNumber
	dto.QuestionText = question.QuestionText
	dto.Subscale = question.Subscale
	dto.ReverseScored = question.ReverseScored
	dto.CreatedAt = question.CreatedAt
}

// FromVARKAnswerOption converts a models.VARKAnswerOption to a VARKAnswerOptionDTO.
func (dto *VARKAnswerOptionDTO) FromVARKAnswerOption(option models.VARKAnswerOption) {
	dto.ID = option.ID
	dto.QuestionID = option.QuestionID
	dto.OptionLetter = option.OptionLetter
	dto.OptionText = option.OptionText
}

type QuestionnaireHistoryResponse struct {
	ID                       int        `json:"id"`
	StudentID                int        `json:"student_id"`
	QuestionnaireID          int        `json:"questionnaire_id"`
	TotalScore               *float64   `json:"total_score"`
	CompletedAt              time.Time  `json:"completed_at"`
	WeekNumber               int        `json:"week_number"`
	Year                     int        `json:"year"`
	QuestionnaireName        string     `json:"questionnaire_name"`
	QuestionnaireType        string     `json:"questionnaire_type"`
	QuestionnaireDescription *string    `json:"questionnaire_description"`
}
