package dtos

// NLPAnalysisRequest represents the request sent to the AI service for NLP analysis.
type NLPAnalysisRequest struct {
	Text        string `json:"text"`
	ContextType string `json:"context_type"`
}

// NLPAnalysisResponse represents the enhanced response received from the AI service.
type NLPAnalysisResponse struct {
	Scores       VARKScores `json:"scores"`
	StrategyUsed string     `json:"strategy_used"`
	WordCount    int        `json:"word_count"`
	Keywords     []string   `json:"keywords"`
	KeySentences []string   `json:"key_sentences"`
	TextStats    TextStats  `json:"text_stats"`
}
