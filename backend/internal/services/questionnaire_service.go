package services

import (
	"encoding/json"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store"
	"time"
)

// QuestionnaireService provides business logic for questionnaires
type QuestionnaireService struct {
	questionnaireStore *store.QuestionnaireStore
}

// NewQuestionnaireService creates a new QuestionnaireService
func NewQuestionnaireService(questionnaireStore *store.QuestionnaireStore) *QuestionnaireService {
	return &QuestionnaireService{questionnaireStore: questionnaireStore}
}

// GetQuestionnaireByID retrieves a questionnaire by ID with its questions
func (s *QuestionnaireService) GetQuestionnaireByID(id uint) (models.Questionnaire, []dtos.QuestionResponse, error) {
	q, err := s.questionnaireStore.GetQuestionnaireByID(int(id))
	if err != nil {
		return models.Questionnaire{}, nil, err
	}

	questions, err := s.questionnaireStore.GetQuestionsByQuestionnaireID(int(id))
	if err != nil {
		return models.Questionnaire{}, nil, err
	}

	var questionDTOs []dtos.QuestionResponse
	for _, question := range questions {
		var questionDTO dtos.QuestionResponse
		questionDTO.FromQuestion(question)
		questionDTOs = append(questionDTOs, questionDTO)
	}

	return *q, questionDTOs, nil
}

// SubmitQuestionnaire saves a student's questionnaire answers
func (s *QuestionnaireService) SubmitQuestionnaire(req dtos.SubmitQuestionnaireRequest, studentID uint) (models.QuestionnaireResult, error) {
	answersJSON, err := json.Marshal(req.Answers)
	if err != nil {
		return models.QuestionnaireResult{}, err
	}

	result := models.QuestionnaireResult{
		StudentID:       int(studentID),
		QuestionnaireID: req.QuestionnaireID,
		Answers:         string(answersJSON),
		CompletedAt:     time.Now(),
		WeekNumber:      req.WeekNumber,
		Year:            req.Year,
	}

	err = s.questionnaireStore.CreateQuestionnaireResult(&result)
	return result, err
}