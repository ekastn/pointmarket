package services

import (
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store"
	"time"
)

type WeeklyEvaluationService struct {
	store *store.WeeklyEvaluationStore
}

func NewWeeklyEvaluationService(store *store.WeeklyEvaluationStore) *WeeklyEvaluationService {
	return &WeeklyEvaluationService{store: store}
}

func (s *WeeklyEvaluationService) GetStudentEvaluationStatus() ([]models.StudentEvaluationStatus, error) {
	_, week := time.Now().ISOWeek()
	year := time.Now().Year()
	return s.store.GetStudentEvaluationStatus(week, year)
}

func (s *WeeklyEvaluationService) GetWeeklyEvaluationOverview(weeks int) ([]models.WeeklyEvaluationOverview, error) {
	return s.store.GetWeeklyEvaluationOverview(weeks)
}

func (s *WeeklyEvaluationService) GetWeeklyEvaluationProgressByStudentID(studentID, weeks int) ([]models.WeeklyEvaluationProgress, error) {
	return s.store.GetWeeklyEvaluationProgressByStudentID(studentID, weeks)
}

func (s *WeeklyEvaluationService) GetPendingWeeklyEvaluationsByStudentID(studentID int) ([]models.WeeklyEvaluationProgress, error) {
	return s.store.GetPendingWeeklyEvaluationsByStudentID(studentID)
}
