package dtos

// MultimodalThresholdRequest represents the request payload to update the multimodal threshold.
type MultimodalThresholdRequest struct {
	Threshold float64 `json:"threshold" binding:"required"`
}

// MultimodalThresholdResponse represents the response payload for multimodal threshold.
type MultimodalThresholdResponse struct {
	Threshold float64 `json:"threshold"`
}
