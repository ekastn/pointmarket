package services

import (
	"encoding/json"
	"errors"
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

// GetVARKAssessment retrieves VARK questions and their options
func (s *VARKService) GetVARKAssessment() ([]models.QuestionnaireQuestion, map[int][]models.VARKAnswerOption, error) {
	questions, err := s.varkStore.GetVARKQuestions()
	if err != nil {
		return nil, nil, err
	}

	optionsMap := make(map[int][]models.VARKAnswerOption)
	for _, q := range questions {
		opts, err := s.varkStore.GetVARKAnswerOptions(q.ID)
		if err != nil {
			return nil, nil, err
		}
		optionsMap[q.ID] = opts
	}

	return questions, optionsMap, nil
}

// SubmitVARKResult calculates and saves a student's VARK assessment result
func (s *VARKService) SubmitVARKResult(studentID int, answers map[int]string) (*models.VARKResult, error) {
	if len(answers) != 16 {
		return nil, errors.New("all 16 VARK questions must be answered")
	}

	scores := map[string]int{
		"Visual":      0,
		"Auditory":    0,
		"Reading":     0,
		"Kinesthetic": 0,
	}

	for qID, selectedOption := range answers {
		opts, err := s.varkStore.GetVARKAnswerOptions(qID)
		if err != nil {
			return nil, err
		}

		found := false
		for _, opt := range opts {
			if opt.OptionLetter == selectedOption {
				scores[opt.LearningStyle]++
				found = true
				break
			}
		}
		if !found {
			return nil, errors.New("invalid answer option provided")
		}
	}

	// Determine dominant style
	maxScore := 0
	for _, score := range scores {
		if score > maxScore {
			maxScore = score
		}
	}

	dominantStyles := []string{}
	for style, score := range scores {
		if score == maxScore {
			dominantStyles = append(dominantStyles, style)
		}
	}

	dominantStyleStr := ""
	if len(dominantStyles) == 1 {
		dominantStyleStr = dominantStyles[0]
	} else {
		dominantStyleStr = "Multimodal"
	}

	// Determine learning preference (simplified logic)
	learningPreference := ""
	if maxScore >= 8 {
		learningPreference = "Strong " + dominantStyleStr
	} else if maxScore >= 5 {
		learningPreference = "Mild " + dominantStyleStr
	} else {
		learningPreference = "Multimodal"
	}

	answersJSON, err := json.Marshal(answers)
	if err != nil {
		return nil, errors.New("failed to marshal answers")
	}

	result := &models.VARKResult{
		StudentID:        studentID,
		VisualScore:      scores["Visual"],
		AuditoryScore:    scores["Auditory"],
		ReadingScore:     scores["Reading"],
		KinestheticScore: scores["Kinesthetic"],
		DominantStyle:    dominantStyleStr,
		LearningPreference: &learningPreference,
		Answers:          string(answersJSON),
		CompletedAt:      time.Now(),
	}

	err = s.varkStore.SaveVARKResult(result)
	if err != nil {
		return nil, err
	}

	return result, nil
}

// GetLatestVARKResult retrieves the latest VARK result for a student
func (s *VARKService) GetLatestVARKResult(studentID int) (*models.VARKResult, error) {
	return s.varkStore.GetLatestVARKResult(studentID)
}
