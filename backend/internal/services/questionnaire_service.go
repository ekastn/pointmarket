package services

import (
	"encoding/json"
	"errors"
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

// GetQuestionnaire retrieves a questionnaire by ID with its questions
func (s *QuestionnaireService) GetQuestionnaire(id int) (*models.Questionnaire, []models.QuestionnaireQuestion, error) {
	q, err := s.questionnaireStore.GetQuestionnaireByID(id)
	if err != nil {
		return nil, nil, err
	}
	if q == nil {
		return nil, nil, errors.New("questionnaire not found")
	}

	questions, err := s.questionnaireStore.GetQuestionnaireQuestions(id)
	if err != nil {
		return nil, nil, err
	}

	return q, questions, nil
}

// SubmitQuestionnaireResult saves a student's questionnaire answers and calculates score
func (s *QuestionnaireService) SubmitQuestionnaireResult(studentID int, questionnaireID int, answers map[string]int, weekNumber, year int) error {
	// Basic validation: ensure answers are not empty
	if len(answers) == 0 {
		return errors.New("no answers provided")
	}

	// Fetch questionnaire to get total questions and type
	questionnaire, _, err := s.GetQuestionnaire(questionnaireID)
	if err != nil {
		return err
	}

	if questionnaire.Type == "vark" {
		return errors.New("VARK assessment should be submitted via VARK service")
	}

	// Calculate total score (simplified for now, actual scoring logic would be more complex)
	totalScore := 0.0
	for _, score := range answers {
		totalScore += float64(score)
	}

	averageScore := totalScore / float64(len(answers))

	answersJSON, err := json.Marshal(answers)
	if err != nil {
		return errors.New("failed to marshal answers")
	}

	result := &models.QuestionnaireResult{
		StudentID:     studentID,
		QuestionnaireID: questionnaireID,
		Answers:       string(answersJSON),
		TotalScore:    &averageScore,
		CompletedAt:   time.Now(),
		WeekNumber:    weekNumber,
		Year:          year,
	}

	return s.questionnaireStore.SaveQuestionnaireResult(result)
}

// GetLatestQuestionnaireResult retrieves the latest result for a student and questionnaire type
func (s *QuestionnaireService) GetLatestQuestionnaireResult(studentID int, qType string) (*models.QuestionnaireResult, error) {
	return s.questionnaireStore.GetLatestQuestionnaireResult(studentID, qType)
}
