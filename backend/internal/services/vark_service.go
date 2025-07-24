package services

import (
	"encoding/json"
	"fmt"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store"
	"strings"
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

	// For the frontend to display options, we need to attach them to questions
	// This might require a custom DTO or modifying the existing QuestionnaireQuestion model
	// For now, assuming frontend handles options based on question ID and option letter (a,b,c,d)

	return *q, questions, nil
}

// SubmitVARK calculates and saves a student's VARK assessment result
func (s *VARKService) SubmitVARK(req dtos.SubmitVARKRequest, studentID uint) (models.VARKResult, error) {
	// Get the VARK questionnaire to get its ID
	varkQuestionnaire, err := s.varkStore.GetVARKQuestionnaire()
	if err != nil || varkQuestionnaire == nil {
		return models.VARKResult{}, fmt.Errorf("VARK questionnaire not found: %w", err)
	}

	// Get all VARK answer options for this questionnaire
	options, err := s.varkStore.GetVARKAnswerOptionsByQuestionnaireID(varkQuestionnaire.ID)
	if err != nil {
		return models.VARKResult{}, fmt.Errorf("failed to get VARK answer options: %w", err)
	}

	// Create a map for quick lookup: questionID -> optionLetter -> learningStyle
	optionMap := make(map[int]map[string]string)
	for _, opt := range options {
		if _, ok := optionMap[opt.QuestionID]; !ok {
			optionMap[opt.QuestionID] = make(map[string]string)
		}
		optionMap[opt.QuestionID][opt.OptionLetter] = opt.LearningStyle
	}

	scores := map[string]int{
		"Visual":      0,
		"Auditory":    0,
		"Reading":     0,
		"Kinesthetic": 0,
	}

	for questionIDStr, answerLetter := range req.Answers {
		questionID := 0
		fmt.Sscanf(questionIDStr, "%d", &questionID) // Convert string key to int

		if qOptions, ok := optionMap[questionID]; ok {
			if learningStyle, ok := qOptions[answerLetter]; ok {
				scores[learningStyle]++
			}
		}
	}

	dominantStyle := ""
	maxScore := -1
	var dominantStyles []string

	// Find max score and all dominant styles
	for style, score := range scores {
		if score > maxScore {
			maxScore = score
			dominantStyles = []string{style} // Start new list of dominant styles
		} else if score == maxScore && maxScore != -1 {
			dominantStyles = append(dominantStyles, style) // Add to existing dominant styles
		}
	}

	// Determine dominant style string
	if len(dominantStyles) == 1 {
		dominantStyle = dominantStyles[0]
	} else if len(dominantStyles) > 1 {
		// Handle multi-modal styles (e.g., VARK types like "VR", "ARK")
		// Sort styles alphabetically for consistent multi-modal naming
		// For simplicity, just join them for now.
		dominantStyle = strings.Join(dominantStyles, "/")
	}

	// Generate learning preference description
	learningPreference := s.generateLearningPreference(dominantStyle)

	answersJSON, err := json.Marshal(req.Answers)
	if err != nil {
		return models.VARKResult{}, err
	}

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

// Helper function to generate learning preference description
func (s *VARKService) generateLearningPreference(dominantStyle string) string {
	switch dominantStyle {
	case "Visual":
		return "Visual Learner: Prefers learning through seeing (diagrams, charts, videos)."
	case "Auditory":
		return "Auditory Learner: Prefers learning through listening (lectures, discussions, audio)."
	case "Reading":
		return "Reading/Writing Learner: Prefers learning through reading and writing (texts, notes, essays)."
	case "Kinesthetic":
		return "Kinesthetic Learner: Prefers learning through doing (hands-on activities, experiments, practice)."
	case "Visual/Auditory":
		return "Visual/Auditory Learner: Learns best by seeing and hearing."
	case "Visual/Reading":
		return "Visual/Reading/Writing Learner: Learns best by seeing and reading/writing."
	case "Visual/Kinesthetic":
		return "Visual/Kinesthetic Learner: Learns best by seeing and doing."
	case "Auditory/Reading":
		return "Auditory/Reading/Writing Learner: Learns best by hearing and reading/writing."
	case "Auditory/Kinesthetic":
		return "Auditory/Kinesthetic Learner: Learns best by hearing and doing."
	case "Reading/Kinesthetic":
		return "Reading/Writing/Kinesthetic Learner: Learns best by reading/writing and doing."
	case "Visual/Auditory/Reading":
		return "VARK Type VAR: Learns best by seeing, hearing, and reading/writing."
	case "Visual/Auditory/Kinesthetic":
		return "VARK Type VAK: Learns best by seeing, hearing, and doing."
	case "Visual/Reading/Kinesthetic":
		return "VARK Type VRK: Learns best by seeing, reading/writing, and doing."
	case "Auditory/Reading/Kinesthetic":
		return "VARK Type ARK: Learns best by hearing, reading/writing, and doing."
	case "Visual/Auditory/Reading/Kinesthetic":
		return "Multimodal Learner (VARK): Learns effectively through all modalities."
	default:
		return "Learning preference not specified."
	}
}
