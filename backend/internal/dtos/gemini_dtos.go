package dtos

// GeminiRecommendationsResponse defines the expected JSON structure from the Gemini API call.
type GeminiRecommendationsResponse struct {
	MSLQInsight      string   `json:"mslq_insight"`
	AMSInsight       string   `json:"ams_insight"`
	Recommendations  []string `json:"recommendations"`
}
