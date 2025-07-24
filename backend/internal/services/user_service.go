package services

import (
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store"
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
