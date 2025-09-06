package dtos

import (
	"encoding/json"
	"pointmarket/backend/internal/store/gen"
	"time"
)

// BadgeDTO represents a badge for API responses
type BadgeDTO struct {
	ID          int64     `json:"id"`
	Title       string    `json:"title"`
	Description *string   `json:"description"`
	PointsMin   *int32    `json:"points_min,omitempty"`
	CreatedAt   time.Time `json:"created_at"`
}

// FromBadgeModel converts a gen.Badge model to a BadgeDTO
func (dto *BadgeDTO) FromBadgeModel(m gen.Badge) {
	dto.ID = m.ID
	dto.Title = m.Title
	if m.Description.Valid {
		dto.Description = &m.Description.String
	} else {
		dto.Description = nil
	}
	// Derive points_min if criteria encoded as {"type":"points_min","value":N}
	var tmp struct {
		Type  string `json:"type"`
		Value int32  `json:"value"`
	}
	if err := json.Unmarshal(m.Criteria, &tmp); err == nil && tmp.Type == "points_min" {
		v := tmp.Value
		dto.PointsMin = &v
	}
	dto.CreatedAt = m.CreatedAt
}

// CreateBadgeRequestDTO for creating a new badge
type CreateBadgeRequestDTO struct {
	Title       string  `json:"title" binding:"required"`
	Description *string `json:"description"`
	PointsMin   *int32  `json:"points_min"`
}

// UpdateBadgeRequestDTO for updating an existing badge
type UpdateBadgeRequestDTO struct {
	Title       *string `json:"title"`
	Description *string `json:"description"`
	PointsMin   *int32  `json:"points_min"`
}

// ListBadgesResponseDTO contains a list of BadgeDTOs
type ListBadgesResponseDTO struct {
	Badges []BadgeDTO `json:"badges"`
	Total  int        `json:"total"`
}

// MissionDTO represents a mission for API responses
type MissionDTO struct {
	ID           int64           `json:"id"`
	Title        string          `json:"title"`
	Description  *string         `json:"description"`
	RewardPoints *int32          `json:"reward_points"`
	Metadata     json.RawMessage `json:"metadata"`
	CreatedAt    time.Time       `json:"created_at"`
	UpdatedAt    time.Time       `json:"updated_at"`
}

// FromMissionModel converts a gen.Mission model to a MissionDTO
func (dto *MissionDTO) FromMissionModel(m gen.Mission) {
	dto.ID = m.ID
	dto.Title = m.Title
	if m.Description.Valid {
		dto.Description = &m.Description.String
	} else {
		dto.Description = nil
	}
	if m.RewardPoints.Valid {
		dto.RewardPoints = &m.RewardPoints.Int32
	} else {
		dto.RewardPoints = nil
	}
	dto.Metadata = m.Metadata
	dto.CreatedAt = m.CreatedAt
	dto.UpdatedAt = m.UpdatedAt
}

// CreateMissionRequestDTO for creating a new mission
type CreateMissionRequestDTO struct {
	Title        string          `json:"title" binding:"required"`
	Description  *string         `json:"description"`
	RewardPoints *int32          `json:"reward_points"`
	Metadata     json.RawMessage `json:"metadata" binding:"required"`
}

// UpdateMissionRequestDTO for updating an existing mission
type UpdateMissionRequestDTO struct {
	Title        *string         `json:"title"`
	Description  *string         `json:"description"`
	RewardPoints *int32          `json:"reward_points"`
	Metadata     json.RawMessage `json:"metadata"`
}

// ListMissionsResponseDTO contains a list of MissionDTOs
type ListMissionsResponseDTO struct {
	Missions []MissionDTO `json:"missions"`
	Total    int          `json:"total"`
}

// UserBadgeDTO represents a badge awarded to a user, including badge details
type UserBadgeDTO struct {
	UserID           int64     `json:"user_id"`
	BadgeID          int64     `json:"badge_id"`
	AwardedAt        time.Time `json:"awarded_at"`
	BadgeTitle       string    `json:"badge_title"`
	BadgeDescription *string   `json:"badge_description"`
}

// AwardBadgeRequestDTO for awarding a badge to a user
type AwardBadgeRequestDTO struct {
	UserID  int64 `json:"user_id" binding:"required"`
	BadgeID int64 `json:"badge_id" binding:"required"`
}

// ListUserBadgesResponseDTO contains a list of UserBadgeDTOs
type ListUserBadgesResponseDTO struct {
	UserBadges []UserBadgeDTO `json:"user_badges"`
	Total      int            `json:"total"`
}

// UserMissionDTO represents a user's mission instance, including mission details
type UserMissionDTO struct {
	ID                  int64           `json:"id"`
	MissionID           int64           `json:"mission_id"`
	UserID              int64           `json:"user_id"`
	Status              string          `json:"status"`
	StartedAt           time.Time       `json:"started_at"`
	CompletedAt         *time.Time      `json:"completed_at"`
	Progress            json.RawMessage `json:"progress"`
	MissionTitle        string          `json:"mission_title"`
	MissionDescription  *string         `json:"mission_description"`
	MissionRewardPoints *int32          `json:"mission_reward_points"`
	MissionMetadata     json.RawMessage `json:"mission_metadata"`
}

// CreateUserMissionRequestDTO for creating a user mission instance
type CreateUserMissionRequestDTO struct {
	UserID    int64           `json:"user_id" binding:"required"`
	MissionID int64           `json:"mission_id" binding:"required"`
	Status    string          `json:"status"` // e.g., "not_started", "in_progress"
	Progress  json.RawMessage `json:"progress"`
}

// UpdateUserMissionStatusRequestDTO for updating a user mission status
type UpdateUserMissionStatusRequestDTO struct {
	Status      string     `json:"status" binding:"required"` // e.g., "completed", "in_progress"
	CompletedAt *time.Time `json:"completed_at"`              // Optional, set if status is completed
}

// ListUserMissionsResponseDTO contains a list of UserMissionDTOs
type ListUserMissionsResponseDTO struct {
	UserMissions []UserMissionDTO `json:"user_missions"`
	Total        int              `json:"total"`
}
