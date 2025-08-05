package services

import (
	"encoding/json"
	"math"
	"math/rand"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/gateway"
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store"
	"pointmarket/backend/internal/utils"
	"sort"
	"strings"
	"time"
)

func init() {
	rand.Seed(time.Now().UnixNano())
}

// NLPService provides business logic for NLP analysis
type NLPService struct {
	nlpStore         *store.NLPStore
	varkStore        *store.VARKStore
	aiServiceGateway *gateway.AIServiceGateway
}

// NewNLPService creates a new NLPService
func NewNLPService(nlpStore *store.NLPStore, varkStore *store.VARKStore, aiServiceGateway *gateway.AIServiceGateway) *NLPService {
	return &NLPService{nlpStore: nlpStore, varkStore: varkStore, aiServiceGateway: aiServiceGateway}
}

// AnalyzeText performs NLP analysis on the given text
func (s *NLPService) AnalyzeText(req dtos.AnalyzeNLPRequest, studentID uint) (models.NLPAnalysisResult, dtos.LearningPreferenceDetail, []string, []string, dtos.TextStats, error) {
	originalText := req.Text
	cleanText := s.cleanText(originalText)

	// Get enhanced data from the external AI service
	aiServiceReq := dtos.NLPAnalysisRequest{
		Text: originalText,
	}
	aiServiceResp, err := s.aiServiceGateway.GetNLPScores(aiServiceReq)
	if err != nil {
		return models.NLPAnalysisResult{}, dtos.LearningPreferenceDetail{}, []string{}, []string{}, dtos.TextStats{}, err
	}

	// Extract enhanced data from AI service response
	nlpVARKScores := aiServiceResp.Scores
	keywords := aiServiceResp.Keywords
	keySentences := aiServiceResp.KeySentences
	textStats := dtos.TextStats{
		WordCount:     aiServiceResp.TextStats.WordCount,
		SentenceCount: aiServiceResp.TextStats.SentenceCount,
		AvgWordLength: aiServiceResp.TextStats.AvgWordLength,
		ReadingTime:   aiServiceResp.TextStats.ReadingTime,
	}

	grammarScore := aiServiceResp.GrammarScore
	readabilityScore := aiServiceResp.ReadabilityScore
	sentimentScore := aiServiceResp.SentimentScore
	structureScore := aiServiceResp.StructureScore
	complexityScore := aiServiceResp.ComplexityScore
	// Keyword score is now directly from AI service, no longer needs contextType
	keywordScore := aiServiceResp.KeywordScore

	// Calculate total score (weighted average) using scores from AI service
	totalScore := (grammarScore*0.2 + keywordScore*0.2 + structureScore*0.15 + readabilityScore*0.15 + sentimentScore*0.15 + complexityScore*0.15)

	feedback := s.generateFeedback(totalScore, grammarScore, keywordScore, structureScore, readabilityScore, sentimentScore, complexityScore)
	feedbackJSON, _ := json.Marshal(feedback)
	feedbackStr := string(feedbackJSON)

	// Personalized feedback can be generated based on VARK/MSLQ profiles, which is a future enhancement
	var personalizedFeedback *string = nil

	// Learning Preference Analysis
	nlpConfidenceWeight := s.calculateNLPConfidenceWeight(textStats.WordCount)
	fusedVARKScores := s.fuseLearningPreferences(nlpVARKScores, nlpConfidenceWeight, int(studentID))
	learningPreference := s.determineLearningPreferenceType(fusedVARKScores)

	analysis := models.NLPAnalysisResult{
		StudentID:            int(studentID),
		AssignmentID:         req.AssignmentID,
		QuizID:               req.QuizID,
		OriginalText:         originalText,
		CleanText:            &cleanText,
		WordCount:            textStats.WordCount,
		SentenceCount:        textStats.SentenceCount,
		TotalScore:           s.roundScore(totalScore),
		GrammarScore:         s.roundScore(grammarScore),
		KeywordScore:         s.roundScore(keywordScore),
		StructureScore:       s.roundScore(structureScore),
		ReadabilityScore:     s.roundScore(readabilityScore),
		SentimentScore:       s.roundScore(sentimentScore),
		ComplexityScore:      s.roundScore(complexityScore),
		Feedback:             &feedbackStr,
		PersonalizedFeedback: personalizedFeedback,
		ContextType:          req.ContextType,
		AnalysisVersion:      "1.0", // Or dynamically load from config
		CreatedAt:            time.Now(),
		UpdatedAt:            time.Now(),
	}

	err = s.nlpStore.CreateNLPAnalysis(&analysis)
	if err != nil {
		return models.NLPAnalysisResult{}, dtos.LearningPreferenceDetail{}, []string{}, []string{}, dtos.TextStats{}, err
	}

	// Update NLP progress
	s.updateNLPProgress(int(studentID), analysis.TotalScore, analysis.GrammarScore, analysis.KeywordScore, analysis.StructureScore)

	return analysis, learningPreference, keywords, keySentences, textStats, nil
}

// GetNLPStats retrieves NLP statistics for a student
func (s *NLPService) GetNLPStats(studentID uint) (*models.NLPProgress, error) {
	return s.nlpStore.GetOverallNLPStats(int(studentID))
}

// calculateNLPConfidenceWeight calculates W_NLP based on word count
func (s *NLPService) calculateNLPConfidenceWeight(wordCount int) float64 {
	if wordCount < 100 {
		return 0.3
	} else if wordCount >= 300 {
		return 0.7
	}
	return 0.5
}

// getUserVARKScores retrieves and normalizes VARK scores for a student
func (s *NLPService) getUserVARKScores(studentID int) dtos.VARKScores {
	// Get the latest VARK result from database
	varkResult, err := s.varkStore.GetLatestVARKResult(studentID)
	if err != nil || varkResult == nil {
		// Return default neutral scores if no VARK data exists
		defaultScore := utils.GetDefaultVARKScore()
		return dtos.VARKScores{
			Visual:      defaultScore,
			Aural:       defaultScore,
			ReadWrite:   defaultScore,
			Kinesthetic: defaultScore,
		}
	}

	// Normalize scores from database values to 1-10 scale
	visual, aural, readWrite, kinesthetic := utils.NormalizeVARKScores(
		varkResult.VisualScore,
		varkResult.AuditoryScore,
		varkResult.ReadingScore,
		varkResult.KinestheticScore,
	)

	return dtos.VARKScores{
		Visual:      visual,
		Aural:       aural,
		ReadWrite:   readWrite,
		Kinesthetic: kinesthetic,
	}
}

// fuseLearningPreferences combines NLP and VARK questionnaire scores using weighted fusion
func (s *NLPService) fuseLearningPreferences(nlpScores dtos.VARKScores, nlpWeight float64, studentID int) dtos.VARKScores {
	// Get real VARK questionnaire scores from database
	varkQuestionnaireScores := s.getUserVARKScores(studentID)

	// Calculate W_VARK weight (complementary to NLP weight)
	wVARK := 1.0 - nlpWeight // W_VARK + W_NLP = 1

	// Perform weighted fusion of NLP and VARK scores
	fused := dtos.VARKScores{
		Visual:      s.roundScore(wVARK*varkQuestionnaireScores.Visual + nlpWeight*nlpScores.Visual),
		Aural:       s.roundScore(wVARK*varkQuestionnaireScores.Aural + nlpWeight*nlpScores.Aural),
		ReadWrite:   s.roundScore(wVARK*varkQuestionnaireScores.ReadWrite + nlpWeight*nlpScores.ReadWrite),
		Kinesthetic: s.roundScore(wVARK*varkQuestionnaireScores.Kinesthetic + nlpWeight*nlpScores.Kinesthetic),
	}

	// Validate all scores are within 1-10 range
	fused.Visual = s.validateAndClampScore(fused.Visual)
	fused.Aural = s.validateAndClampScore(fused.Aural)
	fused.ReadWrite = s.validateAndClampScore(fused.ReadWrite)
	fused.Kinesthetic = s.validateAndClampScore(fused.Kinesthetic)

	return fused
}

// determineLearningPreferenceType classifies preference as Dominant or Multimodal
func (s *NLPService) determineLearningPreferenceType(fusedScores dtos.VARKScores) dtos.LearningPreferenceDetail {
	scoresMap := map[string]float64{
		"Visual":      fusedScores.Visual,
		"Aural":       fusedScores.Aural,
		"Read/Write":  fusedScores.ReadWrite,
		"Kinesthetic": fusedScores.Kinesthetic,
	}

	// Sort scores to find max1 and max2
	type ScoreEntry struct {
		Name  string
		Score float64
	}

	var entries []ScoreEntry
	for name, score := range scoresMap {
		entries = append(entries, ScoreEntry{Name: name, Score: score})
	}

	sort.Slice(entries, func(i, j int) bool {
		return entries[i].Score > entries[j].Score
	})

	// Default values
	prefType := "Dominant"
	label := "Undefined"

	if len(entries) == 0 {
		return dtos.LearningPreferenceDetail{Type: prefType, Combined: fusedScores, Label: label}
	}

	max1 := entries[0]
	if len(entries) == 1 {
		label = max1.Name
	} else {
		max2 := entries[1]
		// Threshold for Multimodal (theta from PDF is 0.15, here using 15 points difference)
		// Assuming scores are out of 100, 15 points is 0.15 * 100
		const theta = 15.0

		if math.Abs(max1.Score-max2.Score) < theta {
			prefType = "Multimodal"
			// Sort alphabetically for consistent multimodal label
			names := []string{max1.Name, max2.Name}
			sort.Strings(names)
			label = strings.Join(names, "-")
		} else {
			label = max1.Name
		}
	}

	return dtos.LearningPreferenceDetail{Type: prefType, Combined: fusedScores, Label: label}
}

// Helper functions for NLP analysis

func (s *NLPService) cleanText(text string) string {
	// Convert to lowercase
	text = strings.ToLower(text)
	// Remove extra spaces
	re := regexp.MustCompile(`\\s+`)
	text = re.ReplaceAllString(text, " ")
	// Remove leading/trailing spaces
	text = strings.TrimSpace(text)
	return text
}

func (s *NLPService) countWords(text string) int {
	if text == "" {
		return 0
	}
	words := strings.Fields(text)
	return len(words)
}

func (s *NLPService) countSentences(text string) int {
	if text == "" {
		return 0
	}
	// Simple sentence splitting by common punctuation marks
	sentences := regexp.MustCompile(`[.!?]+`).Split(text, -1)
	count := 0
	for _, s := range sentences {
		if strings.TrimSpace(s) != "" {
			count++
		}
	}
	return count
}

// The following functions are now handled by the AI service and are removed from here:
// func (s *NLPService) calculateGrammarScore(text string) float64 { ... }
// func (s *NLPService) calculateKeywordScore(text, contextType string) float64 { ... }
// func (s *NLPService) calculateReadabilityScore(wordCount, sentenceCount int, text string) float64 { ... }
// func (s *NLPService) calculateSentimentScore(text string) float64 { ... }
// func (s *NLPService) calculateStructureScore(sentenceCount int) float64 { ... }
// func (s *NLPService) calculateComplexityScore(wordCount int) float64 { ... }

func (s *NLPService) generateFeedback(totalScore, grammarScore, keywordScore, structureScore, readabilityScore, sentimentScore, complexityScore float64) []string {
	feedback := []string{}

	if totalScore >= 80 {
		feedback = append(feedback, "Excellent work! Your text is well-written and effective.")
	} else if totalScore >= 60 {
		feedback = append(feedback, "Good effort! Your text is generally clear, but there's room for improvement.")
	} else {
		feedback = append(feedback, "Your text needs significant improvement in several areas.")
	}

	if grammarScore < 70 {
		feedback = append(feedback, "Consider reviewing your grammar and punctuation for better clarity.")
	}
	if keywordScore < 70 {
		feedback = append(feedback, "Try to incorporate more relevant keywords to strengthen your argument.")
	}
	if structureScore < 70 {
		feedback = append(feedback, "Focus on organizing your thoughts with clearer paragraphs and logical flow.")
	}
	if readabilityScore < 70 {
		feedback = append(feedback, "Aim for shorter sentences and simpler vocabulary to improve readability.")
	}
	if sentimentScore < 40 {
		feedback = append(feedback, "Your text has a somewhat negative tone. Consider using more positive language.")
	} else if sentimentScore > 60 {
		feedback = append(feedback, "Your text has a positive tone, which is great!")
	}
	if complexityScore < 50 {
		feedback = append(feedback, "Your text is quite simple. Try to elaborate more on your ideas.")
	}

	return feedback
}

// validateAndClampScore ensures score is within the 1-10 range
func (s *NLPService) validateAndClampScore(score float64) float64 {
	if score < utils.MinVARKScore {
		return utils.MinVARKScore
	}
	if score > utils.MaxVARKScore {
		return utils.MaxVARKScore
	}
	return s.roundScore(score)
}

func (s *NLPService) roundScore(score float64) float64 {
	return math.Round(score*100) / 100
}

func (s *NLPService) updateNLPProgress(studentID int, currentTotalScore, currentGrammarScore, currentKeywordScore, currentStructureScore float64) error {
	now := time.Now()
	month := int(now.Month())
	year := now.Year()

	progress, err := s.nlpStore.GetNLPProgress(studentID, month, year)
	if err != nil {
		return err
	}

	if progress == nil {
		// First analysis for the month
		progress = &models.NLPProgress{
			StudentID:            studentID,
			Month:                month,
			Year:                 year,
			TotalAnalyses:        1,
			AverageScore:         currentTotalScore,
			BestScore:            currentTotalScore,
			GrammarImprovement:   0, // No previous data for improvement
			KeywordImprovement:   0,
			StructureImprovement: 0,
		}
	} else {
		// Update existing progress
		progress.TotalAnalyses++
		progress.AverageScore = (progress.AverageScore*float64(progress.TotalAnalyses-1) + currentTotalScore) / float64(progress.TotalAnalyses)
		if currentTotalScore > progress.BestScore {
			progress.BestScore = currentTotalScore
		}

		// Simplified improvement calculation (can be more sophisticated)
		// For now, just a dummy increase or based on current vs. average
		progress.GrammarImprovement = s.roundScore(math.Max(0, currentGrammarScore-progress.GrammarImprovement)) // This logic needs refinement for real improvement tracking
		progress.KeywordImprovement = s.roundScore(math.Max(0, currentKeywordScore-progress.KeywordImprovement))
		progress.StructureImprovement = s.roundScore(math.Max(0, currentStructureScore-progress.StructureImprovement))
	}

	return s.nlpStore.SaveNLPProgress(progress)
}