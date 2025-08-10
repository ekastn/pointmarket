package store

import (
	"database/sql"
	"pointmarket/backend/internal/models"

	"github.com/jmoiron/sqlx"
)

// UserStore handles database operations for users
type UserStore struct {
	db *sqlx.DB
}

// NewUserStore creates a new UserStore
func NewUserStore(db *sqlx.DB) *UserStore {
	return &UserStore{db: db}
}

// GetUserByID retrieves a user by their ID
func (s *UserStore) GetUserByID(id uint) (*models.User, error) {
	var user models.User
	err := s.db.Get(&user, "SELECT id, username, password, name, email, role, avatar, created_at, updated_at, last_login FROM users WHERE id = ?", id)
	if err == sql.ErrNoRows {
		return nil, nil // User not found
	}
	if err != nil {
		return nil, err
	}
	return &user, nil
}
