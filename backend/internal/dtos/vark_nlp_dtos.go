package dtos

// VARKNLPCombinedResponse combines VARK result with NLP analysis details
type VARKNLPCombinedResponse struct {
	VARKResultResponse
	LearningPreference LearningPreferenceDetail `json:"learning_preference"`
	Keywords           []string                 `json:"keywords"`
	KeySentences       []string                 `json:"key_sentences"`
	TextStats          TextStats                `json:"text_stats"`
	GrammarScore       ScoreDetail              `json:"grammar_score"`
	ReadabilityScore   ScoreDetail              `json:"readability_score"`
	SentimentScore     ScoreDetail              `json:"sentiment_score"`
	StructureScore     ScoreDetail              `json:"structure_score"`
	ComplexityScore    ScoreDetail              `json:"complexity_score"`
}
