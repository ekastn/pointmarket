package gateway

import (
	"bytes"
	"encoding/json"
	"fmt"
	"net/http"
	"pointmarket/backend/internal/dtos"
	"time"
)

// AIServiceGateway handles communication with the external AI service.

type AIServiceGateway struct {
	BaseURL string
	Client  *http.Client
}

// NewAIServiceGateway creates a new AIServiceGateway.
func NewAIServiceGateway(baseURL string) *AIServiceGateway {
	return &AIServiceGateway{
		BaseURL: baseURL,
		Client: &http.Client{
			Timeout: 30 * time.Second,
		},
	}
}

// GetNLPScores calls the external AI service to get NLP scores for the given text.
func (g *AIServiceGateway) GetNLPScores(req dtos.TextAnalysisRequest) (*dtos.TextAnalysisResponse, error) {
	// Marshal the request body
	requestBody, err := json.Marshal(req)
	if err != nil {
		return nil, fmt.Errorf("failed to marshal request body: %w", err)
	}

	// Create the HTTP request
	httpReq, err := http.NewRequest("POST", g.BaseURL+"/nlp/predict", bytes.NewBuffer(requestBody))
	if err != nil {
		return nil, fmt.Errorf("failed to create request: %w", err)
	}
	httpReq.Header.Set("Content-Type", "application/json")

	// Send the request
	httpResp, err := g.Client.Do(httpReq)
	if err != nil {
		return nil, fmt.Errorf("failed to send request to AI service: %w", err)
	}
	defer httpResp.Body.Close()

	// Check for non-200 status codes
	if httpResp.StatusCode != http.StatusOK {
		return nil, fmt.Errorf("AI service returned non-200 status: %d", httpResp.StatusCode)
	}

	// Decode the response body
	var resp dtos.TextAnalysisResponse
	if err := json.NewDecoder(httpResp.Body).Decode(&resp); err != nil {
		return nil, fmt.Errorf("failed to decode response body: %w", err)
	}

	return &resp, nil
}
