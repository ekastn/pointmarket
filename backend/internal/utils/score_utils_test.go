package utils_test

import (
	"pointmarket/backend/internal/utils"
	"testing"
)

func TestNormalizeVARKScore(t *testing.T) {
	testCases := []struct {
		name  string
		input float64
		want  float64
	}{
		{
			name:  "Test case: score 0",
			input: 0,
			want:  1.0,
		},
		{
			name:  "Test case: score 16 (max)",
			input: 16,
			want:  10.0,
		},
		{
			name:  "Test case: score 8 (mid)",
			input: 8,
			want:  5.5,
		},
		{
			name:  "Test case: score 4 (quarter)",
			input: 4,
			want:  3.25,
		},
		{
			name:  "Test case: negative score",
			input: -10,
			want:  1.0,
		},
		{
			name:  "Test case: score over max",
			input: 20,
			want:  10.0,
		},
	}

	for _, tc := range testCases {
		t.Run(tc.name, func(t *testing.T) {
			got := utils.NormalizeVARKScore(tc.input)
			if got != tc.want {
				t.Errorf("NormalizeVARKScore(%f) = %f; want %f", tc.input, got, tc.want)
			}
		})
	}
}

func TestNormalizeZeroOneScore(t *testing.T) {
	testCases := []struct {
		name  string
		input float64
		want  float64
	}{
		{
			name:  "Test case: score 0.0",
			input: 0.0,
			want:  1.0,
		},
		{
			name:  "Test case: score 1.0 (max)",
			input: 1.0,
			want:  10.0,
		},
		{
			name:  "Test case: score 0.5 (mid)",
			input: 0.5,
			want:  5.5,
		},
		{
			name:  "Test case: score 0.25 (quarter)",
			input: 0.25,
			want:  3.25,
		},
		{
			name:  "Test case: negative score",
			input: -0.5,
			want:  1.0,
		},
		{
			name:  "Test case: score over 1.0",
			input: 1.5,
			want:  10.0,
		},
	}

	for _, tc := range testCases {
		t.Run(tc.name, func(t *testing.T) {
			got := utils.NormalizeZeroOneScore(tc.input)
			if got != tc.want {
				t.Errorf("NormalizeZeroOneScore(%f) = %f; want %f", tc.input, got, tc.want)
			}
		})
	}
}
