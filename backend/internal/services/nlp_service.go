package services

import (
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store"
	"time"
)

// NLPService provides business logic for NLP analysis
type NLPService struct {
	nlpStore *store.NLPStore
}

// NewNLPService creates a new NLPService
func NewNLPService(nlpStore *store.NLPStore) *NLPService {
	return &NLPService{nlpStore: nlpStore}
}

// AnalyzeText performs NLP analysis on the given text
func (s *NLPService) AnalyzeText(req dtos.AnalyzeNLPRequest, studentID uint) (models.NLPAnalysisResult, error) {
	// Simplified analysis logic
	analysis := models.NLPAnalysisResult{
		StudentID:        int(studentID),
		AssignmentID:     req.AssignmentID,
		QuizID:           req.QuizID,
		OriginalText:     req.Text,
		ContextType:      req.ContextType,
		TotalScore:       75.5, // Dummy score
		SentimentScore:   0.8,  // Dummy score
		CreatedAt:        time.Now(),
		UpdatedAt:        time.Now(),
	}

	err := s.nlpStore.CreateNLPAnalysis(&analysis)
	return analysis, err
}

// GetNLPStats retrieves NLP statistics for a student
func (s *NLPService) GetNLPStats(studentID uint) (*models.NLPProgress, error) {
	return s.nlpStore.GetNLPStats(int(studentID))
}
