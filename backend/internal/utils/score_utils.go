package utils

import (
	"math"
)

// VARKScoreConstants defines the scoring system parameters
const (
	// Score scale for API responses (1-10)
	MinVARKScore     = 1.0
	MaxVARKScore     = 10.0
	DefaultVARKScore = 5.0

	// VARK questionnaire typically has scores that sum to around 16 (4 questions * 4 max points)
	// Each dimension can have 0-16 points
	MaxVARKQuestionnaireScore = 16.0
)

// NormalizeVARKScore converts database VARK scores to 1-10 scale
// Input: dbScore (0-16 from questionnaire)
// Output: normalized score (1.0-10.0)
func NormalizeVARKScore(dbScore int) float64 {
	if dbScore < 0 {
		return MinVARKScore
	}

	// Normalize from 0-16 scale to 1-10 scale
	// Formula: normalizedScore = (dbScore / maxDbScore) * 9 + 1
	normalized := (float64(dbScore)/MaxVARKQuestionnaireScore)*(MaxVARKScore-MinVARKScore) + MinVARKScore

	// Ensure score is within bounds
	if normalized > MaxVARKScore {
		return MaxVARKScore
	}
	if normalized < MinVARKScore {
		return MinVARKScore
	}

	return math.Round(normalized*100) / 100 // Round to 2 decimal places
}

// ValidateVARKScore ensures score is within the 1-10 range
func ValidateVARKScore(score float64) bool {
	return score >= MinVARKScore && score <= MaxVARKScore
}

// GetDefaultVARKScore returns the default neutral score when no data is available
func GetDefaultVARKScore() float64 {
	return DefaultVARKScore
}

// NormalizeVARKScores normalizes all scores in a set while maintaining their relative ratios
func NormalizeVARKScores(visual, aural, readWrite, kinesthetic int) (float64, float64, float64, float64) {
	return NormalizeVARKScore(visual),
		NormalizeVARKScore(aural),
		NormalizeVARKScore(readWrite),
		NormalizeVARKScore(kinesthetic)
}
