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

type QuestionnaireResponse struct {
	ID          int                `json:"id"`
	Name        string             `json:"name"`
	Description *string            `json:"description"`
	Questions   []QuestionResponse `json:"questions"`
	CreatedAt   time.Time          `json:"created_at"`
}

type QuestionResponse struct {
	ID             int     `json:"id"`
	QuestionnaireID int     `json:"questionnaire_id"`
	QuestionNumber int     `json:"question_number"`
	QuestionText   string  `json:"question_text"`
	Subscale       *string `json:"subscale"`
	ReverseScored  bool    `json:"reverse_scored"`
	CreatedAt      time.Time `json:"created_at"`
}

// FromQuestionnaire converts a models.Questionnaire and its questions to a QuestionnaireResponse DTO.
func (dto *QuestionnaireResponse) FromQuestionnaire(q models.Questionnaire, questions []models.QuestionnaireQuestion) {
	dto.ID = q.ID
	dto.Name = q.Name
	dto.Description = q.Description
	dto.CreatedAt = q.CreatedAt
	dto.Questions = make([]QuestionResponse, len(questions))
	for i, question := range questions {
		var questionDTO QuestionResponse
		questionDTO.FromQuestion(question)
		dto.Questions[i] = questionDTO
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