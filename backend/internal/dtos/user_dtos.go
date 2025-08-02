package dtos

import (
	"pointmarket/backend/internal/models"
	"time"
)

// ==================
//     Requests
// ==================

type UpdateProfileRequest struct {
	Name   string  `json:"name" binding:"required"`
	Email  string  `json:"email" binding:"required,email"`
	Avatar *string `json:"avatar"`
}

// ==================
//      Response
// ==================

type UserDTO struct {
	ID        int        `json:"id"`
	Username  string     `json:"username"`
	Name      string     `json:"name"`
	Email     string     `json:"email"`
	Role      string     `json:"role"`
	Avatar    *string    `json:"avatar"`
	CreatedAt time.Time  `json:"created_at"`
	UpdatedAt time.Time  `json:"updated_at"`
	LastLogin *time.Time `json:"last_login"`
}

type PendingEvaluationDTO struct {
	WeekNumber        int        `json:"week_number"`
	Year              int        `json:"year"`
	QuestionnaireType string     `json:"questionnaire_type"`
	QuestionnaireName string     `json:"questionnaire_name"`
	Status            string     `json:"status"`
	DueDate           *time.Time `json:"due_date"`
	CompletedAt       *time.Time `json:"completed_at"`
}

// FromUser converts a models.User to a UserDTO.
func (dto *UserDTO) FromUser(user models.User) {
	dto.ID = user.ID
	dto.Username = user.Username
	dto.Name = user.Name
	dto.Email = user.Email
	dto.Role = user.Role
	dto.Avatar = user.Avatar
	dto.CreatedAt = user.CreatedAt
	dto.UpdatedAt = user.UpdatedAt
	dto.LastLogin = user.LastLogin
}
