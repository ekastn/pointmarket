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
	varkStore  *store.VARKStore
	nlpService *NLPService // Add nlpService field
}

// NewVARKService creates a new VARKService
func NewVARKService(varkStore *store.VARKStore, nlpService *NLPService) *VARKService {
	return &VARKService{varkStore: varkStore, nlpService: nlpService} // Assign nlpService
}

// GetVARKQuestions retrieves VARK questions and their options
func (s *VARKService) GetVARKQuestions() (models.Questionnaire, []dtos.QuestionResponse, error) {
	q, err := s.varkStore.GetVARKQuestionnaire()
	if err != nil {
		return models.Questionnaire{}, nil, err
	}

	questions, err := s.varkStore.GetQuestionsByQuestionnaireID(q.ID)
	if err != nil {
		return models.Questionnaire{}, nil, err
	}

	options, err := s.varkStore.GetVARKAnswerOptionsByQuestionnaireID(q.ID)
	if err != nil {
		return models.Questionnaire{}, nil, err
	}

	// Map options to questions
	questionOptionsMap := make(map[int][]dtos.VARKAnswerOptionDTO)
	for _, opt := range options {
		var optDTO dtos.VARKAnswerOptionDTO
		optDTO.FromVARKAnswerOption(opt)
		questionOptionsMap[opt.QuestionID] = append(questionOptionsMap[opt.QuestionID], optDTO)
	}

	var questionDTOs []dtos.QuestionResponse
	for _, question := range questions {
		var questionDTO dtos.QuestionResponse
		questionDTO.FromQuestion(question)
		questionDTO.Options = questionOptionsMap[question.ID]
		questionDTOs = append(questionDTOs, questionDTO)
	}

	return *q, questionDTOs, nil
}

// SubmitVARK calculates and saves a student's VARK assessment result
func (s *VARKService) SubmitVARK(req dtos.SubmitVARKRequest, studentID uint) (models.VARKResult, dtos.LearningPreferenceDetail, []string, []string, dtos.TextStats, float64, float64, float64, float64, float64, error) {
	// Initialize return values for NLP data
	var nlpLearningPreference dtos.LearningPreferenceDetail
	nlpKeywords := []string{}
	nlpKeySentences := []string{}
	nlpTextStats := dtos.TextStats{}
	var grammarScore, readabilityScore, sentimentScore, structureScore, complexityScore float64

	// Get the VARK questionnaire to get its ID
	varkQuestionnaire, err := s.varkStore.GetVARKQuestionnaire()
	if err != nil || varkQuestionnaire == nil {
		return models.VARKResult{}, nlpLearningPreference, nlpKeywords, nlpKeySentences, nlpTextStats, grammarScore, readabilityScore, sentimentScore, structureScore, complexityScore, fmt.Errorf("VARK questionnaire not found: %w", err)
	}

	// Get all VARK answer options for this questionnaire
	options, err := s.varkStore.GetVARKAnswerOptionsByQuestionnaireID(varkQuestionnaire.ID)
	if err != nil {
		return models.VARKResult{}, nlpLearningPreference, nlpKeywords, nlpKeySentences, nlpTextStats, grammarScore, readabilityScore, sentimentScore, structureScore, complexityScore, fmt.Errorf("failed to get VARK answer options: %w", err)
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
		return models.VARKResult{}, nlpLearningPreference, nlpKeywords, nlpKeySentences, nlpTextStats, grammarScore, readabilityScore, sentimentScore, structureScore, complexityScore, err
	}

	result := models.VARKResult{
		StudentID:          int(studentID),
		VisualScore:        scores["Visual"],
		AuditoryScore:      scores["Auditory"],
		ReadingScore:       scores["Reading"],
		KinestheticScore:   scores["Kinesthetic"],
		DominantStyle:      dominantStyle,
		LearningPreference: &learningPreference,
		Answers:            string(answersJSON),
		CompletedAt:        time.Now(),
	}

	// If NLP text is provided, perform NLP analysis and fuse scores
	if req.NLPText != nil && *req.NLPText != "" {
		// Convert questionnaire scores to dtos.VARKScores for NLP service
		questionnaireVARKScores := dtos.VARKScores{
			Visual:      float64(scores["Visual"]),
			Aural:       float64(scores["Auditory"]),
			ReadWrite:   float64(scores["Reading"]),
			Kinesthetic: float64(scores["Kinesthetic"]),
		}

		nlpReq := dtos.AnalyzeNLPRequest{
			Text: *req.NLPText,
		}

		// Call NLP service with questionnaire scores for fusion
		nlpSnapshot, fusedLP, nlpKw, nlpKs, nlpTs, nlpErr := s.nlpService.AnalyzeText(nlpReq, studentID, &questionnaireVARKScores)
		if nlpErr != nil {
			// Log the error but don't block VARK submission
			fmt.Printf("Error during NLP analysis for VARK submission: %v\n", nlpErr)
		} else {
			// Update VARK result with fused learning preference
			result.DominantStyle = fusedLP.Label
			result.LearningPreference = &fusedLP.Label

			// Assign NLP analysis data to return values
			nlpLearningPreference = fusedLP
			nlpKeywords = nlpKw
			nlpKeySentences = nlpKs
			nlpTextStats = nlpTs
			grammarScore = nlpSnapshot.GrammarScore
			readabilityScore = nlpSnapshot.ReadabilityScore
			sentimentScore = nlpSnapshot.SentimentScore
			structureScore = nlpSnapshot.StructureScore
			complexityScore = nlpSnapshot.ComplexityScore
		}
	}

	err = s.varkStore.CreateVARKResult(&result)
	if err != nil {
		return models.VARKResult{}, nlpLearningPreference, nlpKeywords, nlpKeySentences, nlpTextStats, grammarScore, readabilityScore, sentimentScore, structureScore, complexityScore, err
	}
	return result, nlpLearningPreference, nlpKeywords, nlpKeySentences, nlpTextStats, grammarScore, readabilityScore, sentimentScore, structureScore, complexityScore, nil
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
