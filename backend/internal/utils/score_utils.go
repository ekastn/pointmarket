package utils

import (
	"math"
	"pointmarket/backend/internal/dtos"
	"sort"
	"strings"
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

// NormalizeSixtenScore converts database VARK scores to 1-10 scale
// Input: dbScore (0-16 from questionnaire)
// Output: normalized score (1.0-10.0)
func NormalizeSixtenScore(dbScore float64) float64 {
	if dbScore < 0 {
		return MinVARKScore
	}

	// Normalize from 0-16 scale to 1-10 scale
	// Formula: normalizedScore = (dbScore / maxDbScore) * 9 + 1
	normalized := (dbScore/MaxVARKQuestionnaireScore)*(MaxVARKScore-MinVARKScore) + MinVARKScore

	// Ensure score is within bounds
	if normalized > MaxVARKScore {
		return MaxVARKScore
	}
	if normalized < MinVARKScore {
		return MinVARKScore
	}

	return math.Round(normalized*100) / 100 // Round to 2 decimal places
}

// NormalizeZeroOneScore converts a score from a 0-1 scale to the 1-10 standard VARK score scale.
// Input: score (0.0-1.0)
// Output: normalized score (1.0-10.0)
func NormalizeZeroOneScore(score float64) float64 {
	// Clamp the input score to the expected 0.0-1.0 range
	if score < 0.0 {
		score = 0.0
	}
	if score > 1.0 {
		score = 1.0
	}

	// Normalize from 0-1 scale to 1-10 scale
	// Formula: normalizedScore = score * 9 + 1
	normalized := (score * (MaxVARKScore - MinVARKScore)) + MinVARKScore

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

// NormalizeVARKDBScores normalizes all scores in a set while maintaining their relative ratios
func NormalizeVARKDBScores(scores dtos.VARKScores) dtos.VARKScores {
	return dtos.VARKScores{
		Visual:      NormalizeSixtenScore(scores.Visual),
		Auditory:    NormalizeSixtenScore(scores.Auditory),
		Reading:     NormalizeSixtenScore(scores.Reading),
		Kinesthetic: NormalizeSixtenScore(scores.Kinesthetic),
	}
}

// NormalizeVARKScores normalizes all scores in a set while maintaining their relative ratios
func NormalizeVARKScores(scores dtos.VARKScores) dtos.VARKScores {
	return dtos.VARKScores{
		Visual:      NormalizeZeroOneScore(scores.Visual),
		Auditory:    NormalizeZeroOneScore(scores.Auditory),
		Reading:     NormalizeZeroOneScore(scores.Reading),
		Kinesthetic: NormalizeZeroOneScore(scores.Kinesthetic),
	}
}

// determineLearningPreferenceType classifies preference as Dominant or Multimodal
// returns preference type and label
func DetermineLearningPreferenceType(scores dtos.VARKScores) (string, string) {
	scoresMap := map[string]float64{
		"Visual":      scores.Visual,
		"Auditory":    scores.Auditory,
		"Reading":     scores.Reading,
		"Kinesthetic": scores.Kinesthetic,
	}

	// Sort scores to find max1 and max2
	type ScoreEntry struct {
		Name  string
		Score float64
	}

	var entries []ScoreEntry
	for name, score := range scoresMap {
		entries = append(entries, ScoreEntry{Name: name, Score: score})
	}

	sort.Slice(entries, func(i, j int) bool {
		return entries[i].Score > entries[j].Score
	})

	// Default values
	prefType := "dominant"
	label := "undefined"

	if len(entries) == 0 {
		return prefType, label
	}

	max1 := entries[0]
	if len(entries) == 1 {
		label = max1.Name
	} else {
		max2 := entries[1]
		// Threshold for Multimodal
		// Assuming scores are out of 10
		const theta = 0.15

		if math.Abs(max1.Score-max2.Score) < theta {
			prefType = "multimodal"
			// Sort alphabetically for consistent multimodal label
			names := []string{max1.Name, max2.Name}
			sort.Strings(names)
			label = strings.Join(names, "/")
		} else {
			label = max1.Name
		}
	}

	return prefType, label
}
