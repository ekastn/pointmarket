package dtos

import "pointmarket/backend/internal/models"

// ==================
//     Requests
// ==================

type AnalyzeNLPRequest struct {
	Text         string `json:"text" binding:"required"`
	ContextType  string `json:"context_type" binding:"required"`
	AssignmentID *int   `json:"assignment_id"`
	QuizID       *int   `json:"quiz_id"`
}

// ==================
//     Responses
// ==================

type ScoreDetail struct {
	Score float64 `json:"score"`
	Label string  `json:"label"`
}

type TextStats struct {
	WordCount     int     `json:"wordCount"`
	SentenceCount int     `json:"sentenceCount"`
	AvgWordLength float64 `json:"avgWordLength"`
	ReadingTime   int     `json:"readingTime"`
}

type VARKScores struct {
	Visual      float64 `json:"visual"`
	Aural       float64 `json:"aural"`
	ReadWrite   float64 `json:"read_write"`
	Kinesthetic float64 `json:"kinesthetic"`
}

type LearningPreferenceDetail struct {
	Type     string     `json:"type"` // e.g., "Dominant", "Multimodal"
	Combined VARKScores `json:"combined"`
	Label    string     `json:"label"` // e.g., "Visual-Kinesthetic"
}

type NLPAnalysisResponseDTO struct {
	Sentiment          ScoreDetail              `json:"sentiment"`
	Complexity         ScoreDetail              `json:"complexity"`
	Coherence          ScoreDetail              `json:"coherence"`
	Keywords           []string                 `json:"keywords"`
	KeySentences       []string                 `json:"keySentences"`
	Stats              TextStats                `json:"stats"`
	LearningPreference LearningPreferenceDetail `json:"learning_preference"`
}

type NLPStatsResponse struct {
	TotalAnalyses        int     `json:"total_analyses"`
	AverageScore         float64 `json:"average_score"`
	BestScore            float64 `json:"best_score"`
	GrammarImprovement   float64 `json:"grammar_improvement"`
	KeywordImprovement   float64 `json:"keyword_improvement"`
	StructureImprovement float64 `json:"structure_improvement"`
}

// FromNLPStats converts a models.NLPProgress to an NLPStatsResponse DTO.
func (dto *NLPStatsResponse) FromNLPStats(stats models.NLPProgress) {
	dto.TotalAnalyses = stats.TotalAnalyses
	dto.AverageScore = stats.AverageScore
	dto.BestScore = stats.BestScore
	dto.GrammarImprovement = stats.GrammarImprovement
	dto.KeywordImprovement = stats.KeywordImprovement
	dto.StructureImprovement = stats.StructureImprovement
}
