package services

import (
	"context"
	"encoding/json"
	"fmt"
	"log"
	"pointmarket/backend/internal/dtos"

	"google.golang.org/genai"
)

// GeminiService handles communication with the Google Gemini API.
type GeminiService struct {
	genaiClient *genai.Client
	model       string
}

// NewGeminiService creates a new GeminiService.
// The API key is expected to be loaded from environment variables (e.g., GEMINI_API_KEY).
func NewGeminiService(ctx context.Context, model string) (*GeminiService, error) {
	client, err := genai.NewClient(ctx, nil)
	if err != nil {
		return nil, fmt.Errorf("failed to create genai client: %w", err)
	}

	return &GeminiService{
		genaiClient: client,
        model:       model,
	}, nil
}

// GenerateRecommendations sends the prompt to the Gemini API and returns the structured recommendations and insights.
func (s *GeminiService) GenerateRecommendations(ctx context.Context, prompt string) (*dtos.GeminiRecommendationsResponse, error) {
	thinkingBudgetVal := int32(8192)
	config := &genai.GenerateContentConfig{
		ResponseMIMEType: "application/json",
		ResponseJsonSchema: map[string]any{
			"type": "object",
			"properties": map[string]any{
				"mslq_insight": map[string]any{
					"type":        "string",
					"description": "A single, concise insight based on the MSLQ correlations in Bahasa Indonesia.",
				},
				"ams_insight": map[string]any{
					"type":        "string",
					"description": "A single, concise insight based on the AMS correlations in Bahasa Indonesia.",
				},
				"recommendations": map[string]any{
					"type": "array",
					"items": map[string]any{
						"type": "string",
					},
					"description": "3 to 5 actionable recommendations in Bahasa Indonesia.",
				},
			},
			"required": []string{"mslq_insight", "ams_insight", "recommendations"},
		},
		ThinkingConfig: &genai.ThinkingConfig{
			ThinkingBudget: &thinkingBudgetVal,
		},
	}

	resp, err := s.genaiClient.Models.GenerateContent(
		ctx,
        s.model,
		genai.Text(prompt),
		config,
	)
	if err != nil {
		return nil, fmt.Errorf("failed to generate content: %w", err)
	}

	if len(resp.Candidates) == 0 {
		return nil, fmt.Errorf("no content returned from Gemini API")
	}

	rawJSON := resp.Text()

	var geminiResp dtos.GeminiRecommendationsResponse
	if err := json.Unmarshal([]byte(rawJSON), &geminiResp); err != nil {
		log.Printf("Failed to unmarshal recommendations JSON from Gemini. Raw response: %s", rawJSON)
		return nil, fmt.Errorf("failed to decode Gemini response into JSON object: %w", err)
	}

	return &geminiResp, nil
}
