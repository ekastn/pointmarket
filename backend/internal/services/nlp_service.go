package services

import (
	"encoding/json"
	"math"
	"math/rand"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/gateway"
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store"
	"regexp"
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
	aiServiceGateway *gateway.AIServiceGateway
}

// NewNLPService creates a new NLPService
func NewNLPService(nlpStore *store.NLPStore, aiServiceGateway *gateway.AIServiceGateway) *NLPService {
	return &NLPService{nlpStore: nlpStore, aiServiceGateway: aiServiceGateway}
}

// AnalyzeText performs NLP analysis on the given text
func (s *NLPService) AnalyzeText(req dtos.AnalyzeNLPRequest, studentID uint) (models.NLPAnalysisResult, dtos.LearningPreferenceDetail, error) {
	originalText := req.Text
	cleanText := s.cleanText(originalText)

	wordCount := s.countWords(cleanText)
	sentenceCount := s.countSentences(cleanText)

	// Get VARK scores from the external AI service
	aiServiceReq := dtos.NLPAnalysisRequest{
		Text:        originalText,
		ContextType: req.ContextType,
	}
	aiServiceResp, err := s.aiServiceGateway.GetNLPScores(aiServiceReq)
	if err != nil {
		return models.NLPAnalysisResult{}, dtos.LearningPreferenceDetail{}, err
	}

	// Use scores from AI service
	nlpVARKScores := aiServiceResp.Scores

	grammarScore := s.calculateGrammarScore(originalText)
	keywordScore := s.calculateKeywordScore(cleanText, req.ContextType)
	readabilityScore := s.calculateReadabilityScore(wordCount, sentenceCount, cleanText)
	sentimentScore := s.calculateSentimentScore(cleanText)

	// For simplicity, structure and complexity scores are basic for now
	structureScore := s.calculateStructureScore(sentenceCount)
	complexityScore := s.calculateComplexityScore(wordCount)

	// Calculate total score (weighted average)
	totalScore := (grammarScore*0.2 + keywordScore*0.2 + structureScore*0.15 + readabilityScore*0.15 + sentimentScore*0.15 + complexityScore*0.15)

	feedback := s.generateFeedback(totalScore, grammarScore, keywordScore, structureScore, readabilityScore, sentimentScore, complexityScore)
	feedbackJSON, _ := json.Marshal(feedback)
	feedbackStr := string(feedbackJSON)

	// Personalized feedback can be generated based on VARK/MSLQ profiles, which is a future enhancement
	var personalizedFeedback *string = nil

	// Simulate Learning Preference
	nlpConfidenceWeight := s.calculateNLPConfidenceWeight(wordCount)
	fusedVARKScores := s.fuseLearningPreferences(nlpVARKScores, nlpConfidenceWeight)
	learningPreference := s.determineLearningPreferenceType(fusedVARKScores)

	analysis := models.NLPAnalysisResult{
		StudentID:            int(studentID),
		AssignmentID:         req.AssignmentID,
		QuizID:               req.QuizID,
		OriginalText:         originalText,
		CleanText:            &cleanText,
		WordCount:            wordCount,
		SentenceCount:        sentenceCount,
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
		return models.NLPAnalysisResult{}, dtos.LearningPreferenceDetail{}, err
	}

	// Update NLP progress
	s.updateNLPProgress(int(studentID), analysis.TotalScore, analysis.GrammarScore, analysis.KeywordScore, analysis.StructureScore)

	return analysis, learningPreference, nil
}

// GetNLPStats retrieves NLP statistics for a student
func (s *NLPService) GetNLPStats(studentID uint) (*models.NLPProgress, error) {
	return s.nlpStore.GetOverallNLPStats(int(studentID))
}

// simulateVARKScores simulates VARK scores based on text and context
func (s *NLPService) simulateVARKScores(text string, contextType string) dtos.VARKScores {
	// Define keywords for each VARK style (simplified for simulation)
	keywords := map[string][]string{
		"visual":      {"gambar", "diagram", "ilustrasi", "melihat", "visualisasi", "skema"},
		"aural":       {"diskusi", "mendengar", "berbicara", "menjelaskan", "suara", "ceramah"},
		"read_write":  {"membaca", "menulis", "catatan", "artikel", "buku", "definisi", "ringkasan"},
		"kinesthetic": {"melakukan", "praktik", "bergerak", "membangun", "eksperimen", "aplikasi"},
	}

	textLower := strings.ToLower(text)
	wordCount := s.countWords(textLower)

	scores := dtos.VARKScores{}

	// Keyword-based scoring
	for style, kws := range keywords {
		score := 0.0
		foundCount := 0
		for _, kw := range kws {
			if strings.Contains(textLower, kw) {
				foundCount++
			}
		}
		if len(kws) > 0 {
			score = (float64(foundCount) / float64(len(kws))) * 100.0
		}

		// Add a small random component for simulation realism
		score += rand.Float64() * 10.0 // Add up to 10 points randomly

		// Apply context bias
		switch contextType {
		case "matematik", "fisika":
			if style == "visual" || style == "kinesthetic" {
				score += 15.0 // Boost for visual/kinesthetic in these contexts
			}
		case "biologi":
			if style == "visual" || style == "read_write" {
				score += 15.0 // Boost for visual/read_write in biology
			}
		case "assignment":
			if style == "read_write" {
				score += 10.0 // Slight boost for read_write in assignments
			}
		}

		// Cap scores at 100
		score = math.Min(100.0, score)

		switch style {
		case "visual":
			scores.Visual = score
		case "aural":
			scores.Aural = score
		case "read_write":
			scores.ReadWrite = score
		case "kinesthetic":
			scores.Kinesthetic = score
		}
	}

	// Linguistic Style/Structure Analysis (very simplified simulation)
	// Longer texts might slightly favor Read/Write
	if wordCount > 100 {
		scores.ReadWrite += 5.0
	}

	return scores
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

// fuseLearningPreferences simulates weighted fusion of NLP and VARK questionnaire scores
func (s *NLPService) fuseLearningPreferences(nlpScores dtos.VARKScores, nlpWeight float64) dtos.VARKScores {
	// Placeholder for VARK questionnaire scores (fixed for simulation)
	// In a real system, these would come from the database
	varkQuestionnaireScores := dtos.VARKScores{
		Visual:      rand.Float64() * 100, // Simulate some VARK scores
		Aural:       rand.Float64() * 100,
		ReadWrite:   rand.Float64() * 100,
		Kinesthetic: rand.Float64() * 100,
	}

	// Fixed W_VARK for simulation
	wVARK := 1.0 - nlpWeight // W_VARK + W_NLP = 1

	fused := dtos.VARKScores{
		Visual:      s.roundScore(wVARK*varkQuestionnaireScores.Visual + nlpWeight*nlpScores.Visual),
		Aural:       s.roundScore(wVARK*varkQuestionnaireScores.Aural + nlpWeight*nlpScores.Aural),
		ReadWrite:   s.roundScore(wVARK*varkQuestionnaireScores.ReadWrite + nlpWeight*nlpScores.ReadWrite),
		Kinesthetic: s.roundScore(wVARK*varkQuestionnaireScores.Kinesthetic + nlpWeight*nlpScores.Kinesthetic),
	}
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

func (s *NLPService) calculateGrammarScore(text string) float64 {
	// This is a very simplified grammar check for demonstration.
	// A real NLP system would use advanced parsing and rule sets.
	score := 100.0
	errors := 0

	// Check for common issues
	if strings.Contains(text, "  ") { // Double spaces
		errors++
	}
	if strings.Contains(text, ",,") || strings.Contains(text, "..") { // Double punctuation
		errors++
	}
	if strings.Contains(text, " .") || strings.Contains(text, " ,") { // Space before punctuation
		errors++
	}
	// Add more simple rules as needed

	if errors > 0 {
		score = math.Max(0, 100.0-(float64(errors)*10)) // Deduct points for errors
	}
	return score
}

func (s *NLPService) calculateKeywordScore(text, contextType string) float64 {
	keywords, err := s.nlpStore.GetNLPKeywords(contextType)
	if err != nil {
		// Log error, but don't fail analysis
		return 50.0 // Default score if keywords cannot be fetched
	}

	if len(keywords) == 0 {
		return 100.0 // No specific keywords defined, so perfect score
	}

	foundKeywords := 0
	totalWeight := 0.0
	matchedWeight := 0.0

	for _, kw := range keywords {
		totalWeight += kw.Weight
		if strings.Contains(text, kw.Keyword) {
			foundKeywords++
			matchedWeight += kw.Weight
		}
	}

	if totalWeight == 0 {
		return 100.0 // Avoid division by zero if no weights are defined
	}

	score := (matchedWeight / totalWeight) * 100.0
	return math.Min(100.0, score) // Cap at 100
}

func (s *NLPService) calculateReadabilityScore(wordCount, sentenceCount int, text string) float64 {
	if wordCount == 0 || sentenceCount == 0 {
		return 0.0
	}

	avgWordsPerSentence := float64(wordCount) / float64(sentenceCount)
	avgSyllablesPerWord := 1.5 // Simplified: average syllables per word (can be improved)

	// Flesch-Kincaid like formula (simplified)
	// Score = 206.835 - 1.015 * (words/sentences) - 84.6 * (syllables/words)
	// Higher score means easier to read. We'll normalize to 0-100.
	readability := 206.835 - (1.015 * avgWordsPerSentence) - (84.6 * avgSyllablesPerWord)

	// Normalize to a 0-100 scale. This is a rough normalization.
	// Assuming typical readability scores range from 0 to 100,
	// we can cap and floor it.
	score := math.Max(0, math.Min(100, readability))
	return score
}

func (s *NLPService) calculateSentimentScore(text string) float64 {
	// Very basic sentiment analysis: count positive/negative words
	positiveWords := map[string]bool{
		"baik": true, "bagus": true, "positif": true, "hebat": true, "efektif": true,
		"penting": true, "menarik": true, "membantu": true, "sukses": true, "maju": true,
	}
	negativeWords := map[string]bool{
		"buruk": true, "jelek": true, "negatif": true, "sulit": true, "masalah": true,
		"gagal": true, "kurang": true, "tidak": true, "kesalahan": true, "rumit": true,
	}

	words := strings.Fields(text)
	posCount := 0
	negCount := 0

	for _, word := range words {
		if positiveWords[word] {
			posCount++
		} else if negativeWords[word] {
			negCount++
		}
	}

	totalSentimentWords := posCount + negCount
	if totalSentimentWords == 0 {
		return 50.0 // Neutral if no sentiment words found
	}

	sentimentRatio := float64(posCount) / float64(totalSentimentWords)
	score := sentimentRatio * 100.0
	return score
}

func (s *NLPService) calculateStructureScore(sentenceCount int) float64 {
	// Simplified structure score: rewards more sentences (implies more detailed thought)
	// A real structure score would analyze paragraphing, topic sentences, coherence, etc.
	if sentenceCount < 3 {
		return 30.0 // Too few sentences
	} else if sentenceCount < 7 {
		return 60.0 // Moderate number of sentences
	}
	return 90.0 // Good number of sentences
}

func (s *NLPService) calculateComplexityScore(wordCount int) float64 {
	// Simplified complexity score: rewards longer texts (implies more complex ideas)
	// A real complexity score would analyze vocabulary richness, sentence complexity, etc.
	if wordCount < 50 {
		return 40.0 // Very simple
	} else if wordCount < 150 {
		return 70.0 // Moderate complexity
	}
	return 95.0 // High complexity
}

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
