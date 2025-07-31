package dtos

// NLPAnalysisRequest represents the request sent to the AI service.
// Note: This is for the external AI service, not the main backend's public API.
type NLPAnalysisRequest struct {
	Text        string `json:"text"`
	ContextType string `json:"context_type,omitempty"`
}

// NLPAnalysisResponse represents the response received from the AI service.
// Note: This is for the external AI service, not the main backend's public API.
type NLPAnalysisResponse struct {
	Scores       VARKScores `json:"scores"`
	StrategyUsed string     `json:"strategy_used"`
	WordCount    int        `json:"word_count"`
}
