package dtos

// RecommendationItem represents a single recommended item.
type RecommendationItem struct {
	Title             string `json:"title"`
	Description       string `json:"description"`
	Category          string `json:"category"`
	TargetAudience    string `json:"target_audience"`
	DifficultyLevel   string `json:"difficulty_level"`
	EstimatedDuration string `json:"estimated_duration"`
}

// RecommendationAction groups items under an action (intervention type).
type RecommendationAction struct {
	ActionCode int                  `json:"action_code"`
	ActionName string               `json:"action_name"`
	QValue     float64              `json:"q_value"`
	Items      []RecommendationItem `json:"items"`
}

// StudentRecommendationsDTO full recommendation payload consumed by the frontend.
type StudentRecommendationsDTO struct {
	StudentID    string                 `json:"student_id"`
	State        string                 `json:"state"`
	Source       string                 `json:"source"` // trained | persisted | default | fallback
	ModelVersion string                 `json:"model_version"`
	Actions      []RecommendationAction `json:"actions"`
	TotalActions int                    `json:"total_actions"`
	TotalItems   int                    `json:"total_items"`
}

// TrainRecommendationsResponse wraps training trigger output.
type TrainRecommendationsResponse struct {
	Success      bool   `json:"success"`
	Message      string `json:"message"`
	Episodes     int    `json:"episodes"`
	ModelVersion string `json:"model_version"`
}
