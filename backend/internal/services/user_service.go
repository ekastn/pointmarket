package services

import (
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store"
	"time"
)

type UserService struct {
	userStore *store.UserStore
}

func NewUserService(userStore *store.UserStore) *UserService {
	return &UserService{userStore: userStore}
}

func (s *UserService) GetUserByID(id uint) (models.User, error) {
	user, err := s.userStore.GetUserByID(id)
	if err != nil {
		return models.User{}, err
	}
	return *user, nil
}

// GetStudentEvaluationStatus retrieves the weekly evaluation status for all students
func (s *UserService) GetStudentEvaluationStatus() ([]models.StudentEvaluationStatus, error) {
	// Get current week and year
	_, week := time.Now().ISOWeek()
	year := time.Now().Year()
	return s.userStore.GetStudentEvaluationStatus(week, year)
}

// GetWeeklyEvaluationOverview retrieves aggregated weekly progress for teachers
func (s *UserService) GetWeeklyEvaluationOverview(weeks int) ([]models.WeeklyEvaluationOverview, error) {
	return s.userStore.GetWeeklyEvaluationOverview(weeks)
}
