package dtos

import (
	"time"
)

type UpdateUserRequest struct {
	Username  string  `json:"username" binding:"required"`
	Name      string  `json:"name" binding:"required"`
	Email     string  `json:"email" binding:"required,email"`
	Role      string  `json:"role" binding:"required"`
	AvatarURL *string `json:"avatar_url"`
	Bio       *string `json:"bio"`
}

type CreateUserRequest struct {
	Username string `json:"username" binding:"required"`
	Password string `json:"password" binding:"required"`
	Name     string `json:"name" binding:"required"`
	Email    string `json:"email" binding:"required,email"`
	Role     string `json:"role" binding:"required"`
}

type UserDTO struct {
	ID        int       `json:"id"`
	Username  string    `json:"username"`
	Name      string    `json:"name"`
	Email     string    `json:"email"`
	Role      string    `json:"role"`
	Avatar    *string   `json:"avatar"`
	CreatedAt time.Time `json:"created_at"`
	UpdatedAt time.Time `json:"updated_at"`
}

type VARKScores struct {
	Visual      float64 `json:"visual"`
	Auditory    float64 `json:"auditory"`
	Reading     float64 `json:"reading"`
	Kinesthetic float64 `json:"kinesthetic"`
}

type StudentLearningStyle struct {
	Type   string     `json:"type"`
	Label  string     `json:"label"`
	Scores VARKScores `json:"scores"`
}

// ProfileResponse represents merged user + profile data for the current user
type ProfileResponse struct {
	ID        int         `json:"id"`
	Username  string      `json:"username"`
	Name      string      `json:"name"` // from users.display_name
	Email     string      `json:"email"`
	Role      string      `json:"role"`
	Avatar    *string     `json:"avatar"`
	Bio       *string     `json:"bio"`
	CreatedAt time.Time   `json:"created_at"`
	UpdatedAt time.Time   `json:"updated_at"`
	Student   *StudentDTO `json:"student,omitempty"`
}

// UpdateProfileRequest is a partial update payload for the current user's profile
type UpdateProfileRequest struct {
	Name      *string `json:"name"`
	Email     *string `json:"email"`
	AvatarURL *string `json:"avatar"`
	Bio       *string `json:"bio"`
}

// ChangePasswordRequest represents the payload to change current user's password
type ChangePasswordRequest struct {
	CurrentPassword string `json:"current_password" binding:"required"`
	NewPassword     string `json:"new_password" binding:"required"`
	ConfirmPassword string `json:"confirm_password" binding:"required"`
}

// UserDetailsDTO represents detailed user information for the admin panel.
type PointsStats struct {
	TotalPoints int `json:"total_points"`
}

type UserDetailsDTO struct {
	ID         int           `json:"id"`
	Username   string        `json:"username"`
	Name       string        `json:"name"`
	Email      string        `json:"email"`
	Role       string        `json:"role"`
	Avatar     *string       `json:"avatar"`
	Bio        *string       `json:"bio"`
	Points     PointsStats   `json:"points"`
	CreatedAt  time.Time     `json:"created_at"`
	UpdatedAt  time.Time     `json:"updated_at"`
}
