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

type AnalyzeNLPResponse struct {
	TotalScore       float64  `json:"total_score"`
	GrammarScore     float64  `json:"grammar_score"`
	KeywordScore     float64  `json:"keyword_score"`
	StructureScore   float64  `json:"structure_score"`
	ReadabilityScore float64  `json:"readability_score"`
	SentimentScore   float64  `json:"sentiment_score"`
	ComplexityScore  float64  `json:"complexity_score"`
	Feedback         *string  `json:"feedback"`
	PersonalizedFeedback *string `json:"personalized_feedback"`
}

type NLPStatsResponse struct {
	TotalAnalyses      int     `json:"total_analyses"`
	AverageScore       float64 `json:"average_score"`
	BestScore          float64 `json:"best_score"`
	GrammarImprovement float64 `json:"grammar_improvement"`
	KeywordImprovement float64 `json:"keyword_improvement"`
	StructureImprovement float64 `json:"structure_improvement"`
}

// FromNLPAnalysis converts a models.NLPAnalysisResult to an AnalyzeNLPResponse DTO.
func (dto *AnalyzeNLPResponse) FromNLPAnalysis(analysis models.NLPAnalysisResult) {
	dto.TotalScore = analysis.TotalScore
	dto.GrammarScore = analysis.GrammarScore
	dto.KeywordScore = analysis.KeywordScore
	dto.StructureScore = analysis.StructureScore
	dto.ReadabilityScore = analysis.ReadabilityScore
	dto.SentimentScore = analysis.SentimentScore
	dto.ComplexityScore = analysis.ComplexityScore
	dto.Feedback = analysis.Feedback
	dto.PersonalizedFeedback = analysis.PersonalizedFeedback
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