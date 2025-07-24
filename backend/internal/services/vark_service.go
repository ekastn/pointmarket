package services

import (
	"encoding/json"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store"
	"time"
)

// VARKService provides business logic for VARK assessments
type VARKService struct {
	varkStore *store.VARKStore
}

// NewVARKService creates a new VARKService
func NewVARKService(varkStore *store.VARKStore) *VARKService {
	return &VARKService{varkStore: varkStore}
}

// GetVARKQuestions retrieves VARK questions and their options
func (s *VARKService) GetVARKQuestions() (models.Questionnaire, []models.QuestionnaireQuestion, error) {
	q, err := s.varkStore.GetVARKQuestionnaire()
	if err != nil {
		return models.Questionnaire{}, nil, err
	}

	questions, err := s.varkStore.GetQuestionsByQuestionnaireID(q.ID)
	if err != nil {
		return models.Questionnaire{}, nil, err
	}

	return *q, questions, nil
}

// SubmitVARK calculates and saves a student's VARK assessment result
func (s *VARKService) SubmitVARK(req dtos.SubmitVARKRequest, studentID uint) (models.VARKResult, error) {
	// This is a simplified logic. In a real application, you would have a more robust way to calculate the VARK score.
	scores := map[string]int{
		"Visual":      0,
		"Auditory":    0,
		"Reading":     0,
		"Kinesthetic": 0,
	}
	for _, answer := range req.Answers {
		switch answer {
		case "a":
			scores["Visual"]++
		case "b":
			scores["Auditory"]++
		case "c":
			scores["Reading"]++
		case "d":
			scores["Kinesthetic"]++
		}
	}

	dominantStyle := ""
	maxScore := 0
	for style, score := range scores {
		if score > maxScore {
			maxScore = score
			dominantStyle = style
		}
	}

	answersJSON, err := json.Marshal(req.Answers)
	if err != nil {
		return models.VARKResult{}, err
	}

	learningPreference := "Mild " + dominantStyle // Simplified
	result := models.VARKResult{
		StudentID:        int(studentID),
		VisualScore:      scores["Visual"],
		AuditoryScore:    scores["Auditory"],
		ReadingScore:     scores["Reading"],
		KinestheticScore: scores["Kinesthetic"],
		DominantStyle:    dominantStyle,
		LearningPreference: &learningPreference,
		Answers:          string(answersJSON),
		CompletedAt:      time.Now(),
	}

	err = s.varkStore.CreateVARKResult(&result)
	return result, err
}

// GetLatestVARKResult retrieves the latest VARK result for a student
func (s *VARKService) GetLatestVARKResult(studentID uint) (*models.VARKResult, error) {
	return s.varkStore.GetLatestVARKResult(int(studentID))
}