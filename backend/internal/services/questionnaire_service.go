package services

import (
	"encoding/json"
	"fmt"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store"
	"strconv"
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

// GetAllQuestionnaires retrieves all active questionnaires (excluding VARK)
func (s *QuestionnaireService) GetAllQuestionnaires() ([]models.Questionnaire, error) {
	return s.questionnaireStore.GetAllQuestionnaires()
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

// SubmitQuestionnaire saves a student's questionnaire answers and calculates scores
func (s *QuestionnaireService) SubmitQuestionnaire(req dtos.SubmitQuestionnaireRequest, studentID uint) (models.QuestionnaireResult, error) {
	// Fetch questionnaire questions to calculate scores
	questions, err := s.questionnaireStore.GetQuestionsByQuestionnaireID(req.QuestionnaireID)
	if err != nil {
		return models.QuestionnaireResult{}, fmt.Errorf("failed to get questionnaire questions: %w", err)
	}

	// Calculate total score and subscale scores
	totalScore := 0.0
	subscaleScores := make(map[string]float64)
	subscaleCounts := make(map[string]int)

	for _, q := range questions {
		answerStr, ok := req.Answers[fmt.Sprintf("%d", q.ID)]
		if !ok {
			// If a question is not answered, skip it or handle as an error
			continue
		}
		answer, err := strconv.ParseFloat(answerStr, 64)
		if err != nil {
			return models.QuestionnaireResult{}, fmt.Errorf("invalid answer format for question %d: %w", q.ID, err)
		}

		// Apply reverse scoring if necessary
		if q.ReverseScored {
			// Assuming a 1-7 Likert scale, reverse score (8 - actual_score)
			answer = 8 - answer
		}

		totalScore += answer

		if q.Subscale != nil && *q.Subscale != "" {
			subscaleScores[*q.Subscale] += answer
			subscaleCounts[*q.Subscale]++
		}
	}

	// Calculate average total score
	avgTotalScore := 0.0
	if len(questions) > 0 {
		avgTotalScore = totalScore / float64(len(questions))
	}

	// Calculate average subscale scores
	for subscale, sum := range subscaleScores {
		if subscaleCounts[subscale] > 0 {
			subscaleScores[subscale] = sum / float64(subscaleCounts[subscale])
		}
	}

	subscaleScoresJSON, err := json.Marshal(subscaleScores)
	if err != nil {
		return models.QuestionnaireResult{}, fmt.Errorf("failed to marshal subscale scores: %w", err)
	}
	subscaleScoresStr := string(subscaleScoresJSON)

	answersJSON, err := json.Marshal(req.Answers)
	if err != nil {
		return models.QuestionnaireResult{}, err
	}

	result := models.QuestionnaireResult{
		StudentID:       int(studentID),
		QuestionnaireID: req.QuestionnaireID,
		Answers:         string(answersJSON),
		TotalScore:      &avgTotalScore,
		SubscaleScores:  &subscaleScoresStr,
		CompletedAt:     time.Now(),
		WeekNumber:      req.WeekNumber,
		Year:            req.Year,
	}

	err = s.questionnaireStore.CreateQuestionnaireResult(&result)
	return result, err
}

// GetQuestionnaireHistoryByStudentID retrieves all questionnaire results for a given student
func (s *QuestionnaireService) GetQuestionnaireHistoryByStudentID(studentID uint) ([]models.QuestionnaireResult, error) {
	return s.questionnaireStore.GetQuestionnaireResultsByStudentID(int(studentID))
}

// GetQuestionnaireStatsByStudentID retrieves statistics for questionnaires for a given student
func (s *QuestionnaireService) GetQuestionnaireStatsByStudentID(studentID uint) ([]models.QuestionnaireStat, error) {
	return s.questionnaireStore.GetQuestionnaireStatsByStudentID(int(studentID))
}
