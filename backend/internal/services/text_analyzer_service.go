package services

import (
	"context"
	"encoding/json"
	"math"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/gateway"
	"pointmarket/backend/internal/store/gen"
	"pointmarket/backend/internal/utils"
)

// TextAnalyzerService provides business logic for NLP analysis
type TextAnalyzerService struct {
	aiServiceGateway *gateway.AIServiceGateway
	q                gen.Querier
}

// NewTextAnalyzerService creates a new TextAnalyzerService
func NewTextAnalyzerService(aiServiceGateway *gateway.AIServiceGateway, q gen.Querier) *TextAnalyzerService {
	return &TextAnalyzerService{aiServiceGateway: aiServiceGateway, q: q}
}

// AnalyzeText performs NLP analysis on the given text
func (s *TextAnalyzerService) Predict(
	ctx context.Context,
	text string,
	studentID int64,
	questionnaireVARKScores dtos.VARKScores,
) (*gen.CreateTextAnalysisSnapshotParams, *dtos.VARKScores, []string, []string, error) {
	originalText := text

	// Get enhanced data from the external AI service
	aiServiceReq := dtos.TextAnalysisRequest{
		Text: originalText,
	}
	aiServiceResp, err := s.aiServiceGateway.GetNLPScores(aiServiceReq)
	if err != nil {
		return nil, nil, nil, nil, err
	}

	textVARKScores := utils.NormalizeVARKScores(aiServiceResp.Scores)
	keywords := aiServiceResp.Keywords
	keySentences := aiServiceResp.KeySentences
	textStats := aiServiceResp.TextStats
	grammarScore := aiServiceResp.GrammarScore
	readabilityScore := aiServiceResp.ReadabilityScore
	sentimentScore := aiServiceResp.SentimentScore
	structureScore := aiServiceResp.StructureScore
	complexityScore := aiServiceResp.ComplexityScore

	// Calculate total score (weighted average) using scores from AI service
	totalScore := (grammarScore*0.2 + structureScore*0.15 + readabilityScore*0.15 + sentimentScore*0.15 + complexityScore*0.15)

	// Learning Preference Analysis
	nlpConfidenceWeight := s.calculateNLPConfidenceWeight(textStats.WordCount)
	fusedVARKScores := s.fuseLearningPreferences(textVARKScores, nlpConfidenceWeight, questionnaireVARKScores)
	prefType, prefLabel := utils.DetermineLearningPreferenceType(fusedVARKScores)

	scoreJSON, err := json.Marshal(fusedVARKScores)
	snapshot := gen.CreateTextAnalysisSnapshotParams{
		UserID:                         studentID,
		OriginalText:                   originalText,
		CountWords:                     int32(textStats.WordCount),
		CountSentences:                 int32(textStats.SentenceCount),
		AverageWordLength:              textStats.AvgWordLength,
		ReadingTime:                    int32(textStats.ReadingTime),
		ScoreTotal:                     totalScore,
		ScoreGrammar:                   grammarScore,
		ScoreStructure:                 structureScore,
		ScoreReadability:               readabilityScore,
		ScoreSentiment:                 sentimentScore,
		ScoreComplexity:                complexityScore,
		LearningPreferenceType:         prefType,
		LearningPreferenceLabel:        prefLabel,
		LearningPreferenceCombinedVark: scoreJSON,
	}
	// Save the snapshot to the new store
	err = s.q.CreateTextAnalysisSnapshot(ctx, snapshot)
	if err != nil {
		return nil, nil, nil, nil, err
	}

	return &snapshot, &fusedVARKScores, keywords, keySentences, nil
}

// calculateNLPConfidenceWeight calculates W_NLP based on word count
func (s *TextAnalyzerService) calculateNLPConfidenceWeight(wordCount int) float64 {
	if wordCount < 100 {
		return 0.3
	} else if wordCount >= 300 {
		return 0.7
	}
	return 0.5
}

// fuseLearningPreferences combines NLP and VARK questionnaire scores using weighted fusion
func (s *TextAnalyzerService) fuseLearningPreferences(
	nlpScores dtos.VARKScores,
	nlpWeight float64,
	questionnaireVARKScores dtos.VARKScores,
) dtos.VARKScores {
	// Calculate W_VARK weight (complementary to NLP weight)
	wVARK := 1.0 - nlpWeight // W_VARK + W_NLP = 1

	// Perform weighted fusion of NLP and VARK scores
	fused := dtos.VARKScores{
		Visual:      s.roundScore(wVARK*questionnaireVARKScores.Visual + nlpWeight*nlpScores.Visual),
		Auditory:    s.roundScore(wVARK*questionnaireVARKScores.Auditory + nlpWeight*nlpScores.Auditory),
		Reading:     s.roundScore(wVARK*questionnaireVARKScores.Reading + nlpWeight*nlpScores.Reading),
		Kinesthetic: s.roundScore(wVARK*questionnaireVARKScores.Kinesthetic + nlpWeight*nlpScores.Kinesthetic),
	}

	// Validate all scores are within 1-10 range
	fused.Visual = s.validateAndClampScore(fused.Visual)
	fused.Auditory = s.validateAndClampScore(fused.Auditory)
	fused.Reading = s.validateAndClampScore(fused.Reading)
	fused.Kinesthetic = s.validateAndClampScore(fused.Kinesthetic)

	return fused
}

func (s *TextAnalyzerService) validateAndClampScore(score float64) float64 {
	if score < utils.MinVARKScore {
		return utils.MinVARKScore
	}
	if score > utils.MaxVARKScore {
		return utils.MaxVARKScore
	}
	return s.roundScore(score)
}

func (s *TextAnalyzerService) roundScore(score float64) float64 {
	return math.Round(score*100) / 100
}
