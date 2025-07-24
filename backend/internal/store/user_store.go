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

// GetUserByUsernameAndRole retrieves a user by username and role
func (s *UserStore) GetUserByUsernameAndRole(username, role string) (*models.User, error) {
	var user models.User
	err := s.db.Get(&user, "SELECT id, username, password, name, email, role, avatar, created_at, updated_at, last_login FROM users WHERE username = ? AND role = ?", username, role)
	if err == sql.ErrNoRows {
		return nil, nil // User not found
	}
	if err != nil {
		return nil, err
	}
	return &user, nil
}

// GetUserByUsername retrieves a user by username
func (s *UserStore) GetUserByUsername(username string) (*models.User, error) {
	var user models.User
	err := s.db.Get(&user, "SELECT id, username, password, name, email, role, avatar, created_at, updated_at, last_login FROM users WHERE username = ?", username)
	if err == sql.ErrNoRows {
		return nil, nil // User not found
	}
	if err != nil {
		return nil, err
	}
	return &user, nil
}