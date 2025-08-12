package dtos

// TextAnalysisRequest represents the request sent to the AI service for NLP analysis.
type TextAnalysisRequest struct {
	Text string `json:"text"`
}

// TextAnalysisResponse represents the enhanced response received from the AI service.
type TextAnalysisResponse struct {
	Scores           VARKScores `json:"scores"`
	StrategyUsed     string     `json:"strategy_used"`
	WordCount        int        `json:"word_count"`
	Keywords         []string   `json:"keywords"`
	KeySentences     []string   `json:"key_sentences"`
	TextStats        TextStats  `json:"text_stats"`
	GrammarScore     float64    `json:"grammar_score"`
	ReadabilityScore float64    `json:"readability_score"`
	SentimentScore   float64    `json:"sentiment_score"`
	StructureScore   float64    `json:"structure_score"`
	ComplexityScore  float64    `json:"complexity_score"`
}

type TextStats struct {
	WordCount     int     `json:"wordCount"`
	SentenceCount int     `json:"sentenceCount"`
	AvgWordLength float64 `json:"avgWordLength"`
	ReadingTime   int     `json:"readingTime"`
}
