package services

import (
	"encoding/json"
	"errors"
	"math"
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store"
	"regexp"
	"strings"
	"time"
)

// NLPService provides business logic for NLP analysis
type NLPService struct {
	nlpStore           *store.NLPStore
	varkStore          *store.VARKStore // Added VARKStore
	questionnaireStore *store.QuestionnaireStore // To get VARK results for personalized feedback
}

// NewNLPService creates a new NLPService
func NewNLPService(nlpStore *store.NLPStore, varkStore *store.VARKStore, questionnaireStore *store.QuestionnaireStore) *NLPService {
	return &NLPService{nlpStore: nlpStore, varkStore: varkStore, questionnaireStore: questionnaireStore}
}

// AnalyzeText performs NLP analysis on the given text
func (s *NLPService) AnalyzeText(studentID int, text, contextType string, assignmentID, quizID *int) (*models.NLPAnalysisResult, error) {
	if len(text) < 10 {
		return nil, errors.New("text is too short for analysis")
	}

	// 1. Clean Text (simplified)
	cleanText := strings.ToLower(text)
	cleanText = regexp.MustCompile(`[^a-z0-9\s.,?!]`).ReplaceAllString(cleanText, "")

	// 2. Calculate Scores
	grammarScore := s.calculateGrammarScore(text)
	keywordScore := s.calculateKeywordScore(text, contextType)
	structureScore := s.calculateStructureScore(text)
	readabilityScore := s.calculateReadabilityScore(text)
	sentimentScore := s.calculateSentimentScore(text)
	complexityScore := s.calculateComplexityScore(text)

	// Calculate total score with weights
	totalScore := (grammarScore * 0.25) +
		(keywordScore * 0.30) +
		(structureScore * 0.20) +
		(readabilityScore * 0.10) +
		(sentimentScore * 0.05) +
		(complexityScore * 0.10)

	// 3. Generate Feedback
	feedback, personalizedFeedback := s.generateFeedback(studentID, int(grammarScore), int(keywordScore), int(structureScore), int(totalScore))

	// 4. Prepare Result Model
	result := &models.NLPAnalysisResult{
		StudentID:        studentID,
		AssignmentID:     assignmentID,
		QuizID:           quizID,
		OriginalText:     text,
		CleanText:        &cleanText,
		WordCount:        len(strings.Fields(cleanText)),
		SentenceCount:    len(regexp.MustCompile(`[.?!]\s*`).FindAllString(text, -1)),
		TotalScore:       math.Round(totalScore*100)/100,
		GrammarScore:     math.Round(grammarScore*100)/100,
		KeywordScore:     math.Round(keywordScore*100)/100,
		StructureScore:   math.Round(structureScore*100)/100,
		ReadabilityScore: math.Round(readabilityScore*100)/100,
		SentimentScore:   math.Round(sentimentScore*100)/100,
		ComplexityScore:  math.Round(complexityScore*100)/100,
		Feedback:         &feedback,
		PersonalizedFeedback: &personalizedFeedback,
		ContextType:      contextType,
		AnalysisVersion:  "1.0",
		CreatedAt:        time.Now(),
		UpdatedAt:        time.Now(),
	}

	// 5. Save Result
	err := s.nlpStore.SaveNLPAnalysisResult(result)
	if err != nil {
		return nil, err
	}

	// 6. Update NLP Progress
	s.updateNLPProgress(studentID, result.TotalScore, grammarScore, keywordScore, structureScore)

	return result, nil
}

// GetOverallNLPStats retrieves overall NLP statistics for a student
func (s *NLPService) GetOverallNLPStats(studentID int) (*models.NLPProgress, error) {
	return s.nlpStore.GetOverallNLPStats(studentID)
}

// --- Private Helper Methods for Scoring ---

func (s *NLPService) calculateGrammarScore(text string) float64 {
	// Simple check for capitalization at the start of sentences
	errors := 0
	sentences := regexp.MustCompile(`[.?!]\s*`).Split(text, -1)
	for _, sentence := range sentences {
		sentence = strings.TrimSpace(sentence)
		if len(sentence) > 0 && !regexp.MustCompile(`^[A-Z]`).MatchString(sentence) {
			errors++
		}
	}
	// Simple check for punctuation at the end
	if len(text) > 0 && !regexp.MustCompile(`[.?!]$`).MatchString(strings.TrimSpace(text)) {
		errors++
	}
	return math.Max(0, 100-float64(errors*10))
}

func (s *NLPService) calculateKeywordScore(text, contextType string) float64 {
	keywords, err := s.nlpStore.GetNLPKeywords(contextType)
	if err != nil {
		return 0
	}

	foundKeywords := 0.0
	totalWeight := 0.0
	textLower := strings.ToLower(text)

	for _, kw := range keywords {
		if strings.Contains(textLower, strings.ToLower(kw.Keyword)) {
			foundKeywords += kw.Weight
		}
		totalWeight += kw.Weight
	}

	if totalWeight > 0 {
		return (foundKeywords / totalWeight) * 100
	}
	return 0
}

func (s *NLPService) calculateStructureScore(text string) float64 {
	score := 0.0
	// Check for paragraphs (newlines)
	if strings.Contains(text, "\n") {
		score += 30
	}
	// Check for numbering or bullet points
	if regexp.MustCompile(`(1\.|-|\*)\s`).MatchString(text) {
		score += 40
	}
	// Check for logical connectors
	connectors := []string{"karena", "dengan demikian", "selain itu", "namun"}
	for _, connector := range connectors {
		if strings.Contains(strings.ToLower(text), connector) {
			score += 10
			break
		}
	}
	return math.Min(100, score)
}

func (s *NLPService) calculateReadabilityScore(text string) float64 {
	words := len(strings.Fields(text))
	sentences := len(regexp.MustCompile(`[.?!]\s*`).FindAllString(text, -1))
	if words == 0 || sentences == 0 {
		return 0
	}
	asl := float64(words) / float64(sentences) // Average sentence length
	// Flesch-Kincaid formula simplified for demonstration
	score := 206.835 - (1.015 * asl)
	return math.Max(0, math.Min(100, score))
}

func (s *NLPService) calculateSentimentScore(text string) float64 {
	// Simplified sentiment analysis
	positiveWords := []string{"baik", "bagus", "penting", "menarik", "efektif", "meningkatkan"}
	negativeWords := []string{"buruk", "jelek", "tidak", "gagal", "masalah"}
	positiveCount := 0
	negativeCount := 0
	words := strings.Fields(strings.ToLower(text))
	for _, word := range words {
		if contains(positiveWords, word) {
			positiveCount++
		}
		if contains(negativeWords, word) {
			negativeCount++
		}
	}
	total := positiveCount + negativeCount
	if total > 0 {
		return (float64(positiveCount) / float64(total)) * 100
	}
	return 50 // Neutral if no sentiment words found
}

func (s *NLPService) calculateComplexityScore(text string) float64 {
	words := len(strings.Fields(text))
	sentences := len(regexp.MustCompile(`[.?!]\s*`).FindAllString(text, -1))
	if words == 0 || sentences == 0 {
		return 0
	}
	asl := float64(words) / float64(sentences)
	longWords := 0
	for _, word := range strings.Fields(text) {
		if len(word) > 8 {
			longWords++
		}
	}
	score := (asl * 0.5) + (float64(longWords) * 0.5)
	return math.Min(100, score)
}

func (s *NLPService) generateFeedback(studentID, grammarScore, keywordScore, structureScore, totalScore int) (string, string) {
	generalFeedback := []string{}
	personalizedFeedback := []string{}

	// General feedback based on total score
	if totalScore < 50 {
		generalFeedback = append(generalFeedback, "Secara keseluruhan, tulisan Anda masih memerlukan banyak perbaikan. Fokus pada dasar-dasar penulisan.")
	} else if totalScore < 75 {
		generalFeedback = append(generalFeedback, "Tulisan Anda sudah cukup baik, namun ada beberapa area yang bisa ditingkatkan untuk hasil yang lebih maksimal.")
	} else {
		generalFeedback = append(generalFeedback, "Kerja bagus! Tulisan Anda sudah terstruktur dengan baik dan informatif.")
	}

	// Specific feedback based on components
	if grammarScore < 60 {
		generalFeedback = append(generalFeedback, "Perhatikan kembali tata bahasa dan penggunaan tanda baca.")
	}
	if keywordScore < 60 {
		generalFeedback = append(generalFeedback, "Coba gunakan lebih banyak kata kunci yang relevan dengan topik untuk memperkuat argumen Anda.")
	}
	if structureScore < 60 {
		generalFeedback = append(generalFeedback, "Struktur tulisan bisa diperbaiki dengan penggunaan paragraf dan poin-poin yang jelas.")
	}

	// Personalized feedback based on VARK style
	varkResult, err := s.varkStore.GetLatestVARKResult(studentID)
	if err == nil && varkResult != nil {
		varkStyle := varkResult.DominantStyle
		varkTips := map[string]string{
			"Visual":      "Untuk memperjelas poin Anda, coba tambahkan deskripsi visual atau bayangkan Anda sedang membuat diagram.",
			"Auditory":    "Coba baca tulisan Anda dengan suara keras. Ini bisa membantu menemukan kalimat yang terdengar janggal.",
			"Reading":     "Gaya belajar Anda sangat cocok untuk penulisan. Manfaatkan dengan membuat outline sebelum menulis.",
			"Kinesthetic": "Hubungkan konsep yang Anda tulis dengan contoh-contoh praktis atau aplikasi di dunia nyata.",
		}
		if tip, ok := varkTips[varkStyle]; ok {
			personalizedFeedback = append(personalizedFeedback, tip)
		}
	}

	// Get feedback from templates
	// This part would ideally query nlp_feedback_templates table
	// For now, using hardcoded logic based on scores

	// Example of using templates (conceptual)
	// templates, err := s.nlpStore.GetNLPFeedbackTemplates("grammar", varkStyle, "all", grammarScore)
	// if err == nil {
	// 	for _, t := range templates {
	// 		personalizedFeedback = append(personalizedFeedback, t.FeedbackText)
	// 	}
	// }

	generalJSON, _ := json.Marshal(generalFeedback)
	personalizedJSON, _ := json.Marshal(personalizedFeedback)

	return string(generalJSON), string(personalizedJSON)
}

func (s *NLPService) updateNLPProgress(studentID int, totalScore, grammarScore, keywordScore, structureScore float64) {
	currentMonth := int(time.Now().Month())
	currentYear := time.Now().Year()

	progress, err := s.nlpStore.GetNLPProgress(studentID, currentMonth, currentYear)
	if err != nil {
		// Log error but don't fail the main analysis
		return
	}

	if progress == nil {
		// New entry for the month
		progress = &models.NLPProgress{
			StudentID: studentID,
			Month:     currentMonth,
			Year:      currentYear,
			TotalAnalyses: 1,
			AverageScore:  totalScore,
			BestScore:     totalScore,
		}
	} else {
		// Update existing entry
		progress.TotalAnalyses++
		progress.AverageScore = (progress.AverageScore*float64(progress.TotalAnalyses-1) + totalScore) / float64(progress.TotalAnalyses)
		if totalScore > progress.BestScore {
			progress.BestScore = totalScore
		}
		// Simplified improvement calculation (can be more complex)
		progress.GrammarImprovement = (progress.GrammarImprovement*float64(progress.TotalAnalyses-1) + grammarScore) / float64(progress.TotalAnalyses)
		progress.KeywordImprovement = (progress.KeywordImprovement*float64(progress.TotalAnalyses-1) + keywordScore) / float64(progress.TotalAnalyses)
		progress.StructureImprovement = (progress.StructureImprovement*float64(progress.TotalAnalyses-1) + structureScore) / float64(progress.TotalAnalyses)
	}

	err = s.nlpStore.SaveNLPProgress(progress)
	if err != nil {
		// Log error
	}
}

// Helper to check if a slice contains a string
func contains(s []string, str string) bool {
	for _, v := range s {
		if v == str {
			return true
		}
	}
	return false
}