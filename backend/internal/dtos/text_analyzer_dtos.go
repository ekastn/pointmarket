package dtos

// TextAnalyzerRequest represents the API request payload for text analysis.
type TextAnalyzerRequest struct {
	Text string `json:"text" binding:"required"`
}

// TextAnalyzerTextStats represents text statistics computed for the request.
type TextAnalyzerTextStats struct {
	WordCount         int32   `json:"word_count"`
	SentenceCount     int32   `json:"sentence_count"`
	AverageWordLength float64 `json:"average_word_length"`
	ReadingTime       int32   `json:"reading_time"`
	GrammarScore      float64 `json:"grammar_score"`
	ReadabilityScore  float64 `json:"readability_score"`
	SentimentScore    float64 `json:"sentiment_score"`
	StructureScore    float64 `json:"structure_score"`
	ComplexityScore   float64 `json:"complexity_score"`
}

// TextAnalyzerResponse represents the API response payload for text analysis.
type TextAnalyzerResponse struct {
	Keywords      []string              `json:"keywords"`
	KeySentences  []string              `json:"key_sentences"`
	LearningStyle StudentLearningStyle  `json:"learning_style"`
	TextStats     TextAnalyzerTextStats `json:"text_stats"`
}
