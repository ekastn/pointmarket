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
	questionnaireStore    *store.QuestionnaireStore
	varkStore             *store.VARKStore
	weeklyEvaluationStore *store.WeeklyEvaluationStore // Added for weekly evaluation updates
}

// NewQuestionnaireService creates a new QuestionnaireService
func NewQuestionnaireService(questionnaireStore *store.QuestionnaireStore, varkStore *store.VARKStore, weeklyEvaluationStore *store.WeeklyEvaluationStore) *QuestionnaireService {
	return &QuestionnaireService{questionnaireStore: questionnaireStore, varkStore: varkStore, weeklyEvaluationStore: weeklyEvaluationStore}
}

// GetAllQuestionnaires retrieves all active questionnaires (excluding VARK)
func (s *QuestionnaireService) GetAllQuestionnaires() ([]models.Questionnaire, error) {
	return s.questionnaireStore.GetAllQuestionnaires()
}

// GetQuestionnaireByID retrieves a questionnaire by ID with its questions and optionally the latest result for a student
func (s *QuestionnaireService) GetQuestionnaireByID(id uint, studentID uint) (models.Questionnaire, []dtos.QuestionResponse, *dtos.QuestionnaireResultResponse, error) {
	q, err := s.questionnaireStore.GetQuestionnaireByID(int(id))
	if err != nil {
		return models.Questionnaire{}, nil, nil, err
	}

	questions, err := s.questionnaireStore.GetQuestionsByQuestionnaireID(int(id))
	if err != nil {
		return models.Questionnaire{}, nil, nil, err
	}

	var questionDTOs []dtos.QuestionResponse
	for _, question := range questions {
		var questionDTO dtos.QuestionResponse
		questionDTO.FromQuestion(question)
		questionDTOs = append(questionDTOs, questionDTO)
	}

	var recentResultDTO *dtos.QuestionnaireResultResponse
	if studentID != 0 {
		latestResult, err := s.questionnaireStore.GetLatestQuestionnaireResultByQuestionnaireIDAndStudentID(int(studentID), int(id))
		if err != nil {
			fmt.Printf("Error fetching latest questionnaire result for student %d, questionnaire %d: %v\n", studentID, id, err)
		} else if latestResult != nil {
			recentResultDTO = &dtos.QuestionnaireResultResponse{}
			recentResultDTO.FromQuestionnaireResult(*latestResult)
		}
	}

	return *q, questionDTOs, recentResultDTO, nil
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
			continue
		}
		answer, err := strconv.ParseFloat(answerStr, 64)
		if err != nil {
			return models.QuestionnaireResult{}, fmt.Errorf("invalid answer format for question %d: %w", q.ID, err)
		}

		if q.ReverseScored {
			answer = 8 - answer
		}

		totalScore += answer

		if q.Subscale != nil && *q.Subscale != "" {
			subscaleScores[*q.Subscale] += answer
			subscaleCounts[*q.Subscale]++
		}
	}

	avgTotalScore := 0.0
	if len(questions) > 0 {
		avgTotalScore = totalScore / float64(len(questions))
	}

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
	if err != nil {
		return models.QuestionnaireResult{}, fmt.Errorf("failed to create questionnaire result: %w", err)
	}

	// After successfully saving the questionnaire result, update the weekly_evaluations status
	if req.WeekNumber != 0 && req.Year != 0 {
		err = s.weeklyEvaluationStore.UpdateWeeklyEvaluationStatusByStudentWeekYearAndQuestionnaire(
			int(studentID), req.WeekNumber, req.Year, req.QuestionnaireID, "completed")
		if err != nil {
			// Log the error but don't return it, as the questionnaire result was already saved.
			fmt.Printf("Warning: Failed to update weekly evaluation status for student %d, week %d, year %d, questionnaire %d: %v\n",
				studentID, req.WeekNumber, req.Year, req.QuestionnaireID, err)
		}
	}

	return result, nil
}

// GetQuestionnaireHistoryByStudentID retrieves all questionnaire results for a given student
func (s *QuestionnaireService) GetQuestionnaireHistoryByStudentID(studentID uint) ([]models.QuestionnaireResult, error) {
	return s.questionnaireStore.GetQuestionnaireResultsByStudentID(int(studentID))
}

// GetQuestionnaireStatsByStudentID retrieves statistics for questionnaires for a given student
func (s *QuestionnaireService) GetQuestionnaireStatsByStudentID(studentID uint) ([]models.QuestionnaireStat, error) {
	// Get MSLQ and AMS stats
	stats, err := s.questionnaireStore.GetQuestionnaireStatsByStudentID(int(studentID))
	if err != nil {
		return nil, err
	}

	// Get VARK stats separately
	varkResult, err := s.varkStore.GetLatestVARKResult(int(studentID))
	if err != nil {
		// Log error but don't fail if VARK stats can't be fetched
		fmt.Printf("Error fetching VARK result for student %d: %v\n", studentID, err)
	} else {
		// Add VARK stats if available
		var varkTotalCompleted int
		var varkLastCompleted *time.Time
		if varkResult != nil {
			varkTotalCompleted = 1 // Assuming one entry means completed
			varkLastCompleted = &varkResult.CompletedAt
		}

		// Check if VARK stat already exists in the slice (e.g., if it was added by a previous query)
		varkStatExists := false
		for i, stat := range stats {
			if stat.Type == "vark" {
				stats[i].TotalCompleted = varkTotalCompleted
				stats[i].LastCompleted = varkLastCompleted
				varkStatExists = true
				break
			}
		}

		if !varkStatExists {
			stats = append(stats, models.QuestionnaireStat{
				Type:           "vark",
				Name:           "VARK Learning Style Assessment",
				TotalCompleted: varkTotalCompleted,
				AverageScore:   nil, // VARK doesn't have traditional scoring
				BestScore:      nil,
				LowestScore:    nil,
				LastCompleted:  varkLastCompleted,
			})
		}
	}

	return stats, nil
}
