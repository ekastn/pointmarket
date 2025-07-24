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

// CreateUser inserts a new user into the database
func (s *UserStore) CreateUser(user *models.User) error {
	query := `INSERT INTO users (username, password, name, email, role) VALUES (?, ?, ?, ?, ?)`
	result, err := s.db.Exec(query, user.Username, user.Password, user.Name, user.Email, user.Role)
	if err != nil {
		return err
	}
	id, err := result.LastInsertId()
	if err != nil {
		return err
	}
	user.ID = int(id)
	return nil
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
