package dtos

// NLPAnalysisRequest represents the request sent to the AI service for NLP analysis.
type NLPAnalysisRequest struct {
	Text        string `json:"text"`
}

// NLPAnalysisResponse represents the enhanced response received from the AI service.
type NLPAnalysisResponse struct {
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
	KeywordScore     float64    `json:"keyword_score"`
}