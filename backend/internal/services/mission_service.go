package services

import (
	"context"
	"database/sql"
	"fmt"
	"log"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
	"pointmarket/backend/internal/utils"
	"time"
)

// MissionService provides business logic for missions and user missions
type MissionService struct {
	q      gen.Querier
	points *PointsService
}

// NewMissionService creates a new MissionService
func NewMissionService(q gen.Querier, ps *PointsService) *MissionService {
	return &MissionService{q: q, points: ps}
}

// CreateMission creates a new mission
func (s *MissionService) CreateMission(ctx context.Context, req dtos.CreateMissionRequestDTO) (dtos.MissionDTO, error) {
	result, err := s.q.CreateMission(ctx, gen.CreateMissionParams{
		Title:        req.Title,
		Description:  utils.NullString(req.Description),
		RewardPoints: utils.NullInt32(req.RewardPoints),
		Metadata:     req.Metadata,
	})
	if err != nil {
		return dtos.MissionDTO{}, err
	}

	id, err := result.LastInsertId()
	if err != nil {
		return dtos.MissionDTO{}, err
	}

	mission, err := s.q.GetMissionByID(ctx, id)
	if err != nil {
		return dtos.MissionDTO{}, err
	}

	var missionDTO dtos.MissionDTO
	missionDTO.FromMissionModel(mission)
	return missionDTO, nil
}

// GetMissionByID retrieves a mission by its ID
func (s *MissionService) GetMissionByID(ctx context.Context, id int64) (dtos.MissionDTO, error) {
	mission, err := s.q.GetMissionByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.MissionDTO{}, nil // Mission not found
	}
	if err != nil {
		return dtos.MissionDTO{}, err
	}

	var missionDTO dtos.MissionDTO
	missionDTO.FromMissionModel(mission)
	return missionDTO, nil
}

// GetMissions retrieves a list of all missions
func (s *MissionService) GetMissions(ctx context.Context) ([]dtos.MissionDTO, error) {
	missions, err := s.q.GetMissions(ctx)
	if err != nil {
		return nil, err
	}

	var missionDTOs []dtos.MissionDTO
	for _, mission := range missions {
		var missionDTO dtos.MissionDTO
		missionDTO.FromMissionModel(mission)
		missionDTOs = append(missionDTOs, missionDTO)
	}
	return missionDTOs, nil
}

// UpdateMission updates an existing mission
func (s *MissionService) UpdateMission(ctx context.Context, id int64, req dtos.UpdateMissionRequestDTO) (dtos.MissionDTO, error) {
	// Get existing mission to apply partial updates
	existingMission, err := s.q.GetMissionByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.MissionDTO{}, nil // Mission not found
	}
	if err != nil {
		return dtos.MissionDTO{}, err
	}

	// Apply updates
	title := existingMission.Title
	if req.Title != nil {
		title = *req.Title
	}

	description := existingMission.Description
	if req.Description != nil {
		description = sql.NullString{String: *req.Description, Valid: true}
	}

	rewardPoints := existingMission.RewardPoints
	if req.RewardPoints != nil {
		rewardPoints = sql.NullInt32{Int32: *req.RewardPoints, Valid: true}
	}

	metadata := existingMission.Metadata
	if req.Metadata != nil { // Assuming Metadata is always provided if updated
		metadata = req.Metadata
	}

	err = s.q.UpdateMission(ctx, gen.UpdateMissionParams{
		Title:        title,
		Description:  description,
		RewardPoints: rewardPoints,
		Metadata:     metadata,
		ID:           id,
	})
	if err != nil {
		return dtos.MissionDTO{}, err
	}

	updatedMission, err := s.q.GetMissionByID(ctx, id)
	if err != nil {
		return dtos.MissionDTO{}, err
	}

	var missionDTO dtos.MissionDTO
	missionDTO.FromMissionModel(updatedMission)
	return missionDTO, nil
}

// DeleteMission deletes a mission by its ID
func (s *MissionService) DeleteMission(ctx context.Context, id int64) error {
	return s.q.DeleteMission(ctx, id)
}

// CreateUserMission creates a user's mission instance
func (s *MissionService) CreateUserMission(ctx context.Context, req dtos.CreateUserMissionRequestDTO) (dtos.UserMissionDTO, error) {
	// Default status if not provided
	status := req.Status
	if status == "" {
		status = "not_started" // Or "in_progress" depending on default
	}

	result, err := s.q.CreateUserMission(ctx, gen.CreateUserMissionParams{
		MissionID: req.MissionID,
		UserID:    req.UserID,
		Status:    status,
		StartedAt: time.Now(), // Set started_at to current time
		Progress:  req.Progress,
	})
	if err != nil {
		return dtos.UserMissionDTO{}, err
	}

	// Get the ID of the newly inserted row
	newlyCreatedID, err := result.LastInsertId()
	if err != nil {
		return dtos.UserMissionDTO{}, err
	}

	// Fetch the created user mission to return full details
	// This is a temporary inefficient solution. A GetUserMissionByID query should be added.
	userMissions, err := s.q.GetUserMissionsByUserID(ctx, req.UserID)
	if err != nil {
		return dtos.UserMissionDTO{}, err
	}

	var createdUserMission gen.GetUserMissionsByUserIDRow
	found := false
	for _, um := range userMissions {
		if um.ID == newlyCreatedID {
			createdUserMission = um
			found = true
			break
		}
	}

	if !found {
		return dtos.UserMissionDTO{}, fmt.Errorf("could not find newly created user mission with ID %d", newlyCreatedID)
	}

	var userMissionDTO dtos.UserMissionDTO
	userMissionDTO.ID = createdUserMission.ID
	userMissionDTO.MissionID = createdUserMission.MissionID
	userMissionDTO.UserID = createdUserMission.UserID
	userMissionDTO.Status = createdUserMission.Status
	userMissionDTO.StartedAt = createdUserMission.StartedAt
	if createdUserMission.CompletedAt.Valid {
		userMissionDTO.CompletedAt = &createdUserMission.CompletedAt.Time
	} else {
		userMissionDTO.CompletedAt = nil
	}
	userMissionDTO.Progress = createdUserMission.Progress
	userMissionDTO.MissionTitle = createdUserMission.Title
	if createdUserMission.Description.Valid {
		userMissionDTO.MissionDescription = func() *string { s := createdUserMission.Description.String; return &s }() // Convert sql.NullString to *string
	} else {
		userMissionDTO.MissionDescription = nil
	}
	if createdUserMission.RewardPoints.Valid {
		userMissionDTO.MissionRewardPoints = func() *int32 { i := createdUserMission.RewardPoints.Int32; return &i }() // Convert sql.NullInt32 to *int32
	} else {
		userMissionDTO.MissionRewardPoints = nil
	}
	userMissionDTO.MissionMetadata = createdUserMission.Metadata

	return userMissionDTO, nil
}

// GetUserMissionsByUserID retrieves all missions for a specific user
func (s *MissionService) GetUserMissionsByUserID(ctx context.Context, userID int64) ([]dtos.UserMissionDTO, error) {
	userMissions, err := s.q.GetUserMissionsByUserID(ctx, userID)
	if err != nil {
		return nil, err
	}

	var userMissionDTOs []dtos.UserMissionDTO
	for _, um := range userMissions {
		userMissionDTOs = append(userMissionDTOs, dtos.UserMissionDTO{
			ID:                  um.ID,
			MissionID:           um.MissionID,
			UserID:              um.UserID,
			Status:              um.Status,
			StartedAt:           um.StartedAt,
			CompletedAt:         func() *time.Time { t := um.CompletedAt.Time; return &t }(), // Convert sql.NullTime to *time.Time
			Progress:            um.Progress,
			MissionTitle:        um.Title,
			MissionDescription:  func() *string { s := um.Description.String; return &s }(), // Convert sql.NullString to *string
			MissionRewardPoints: func() *int32 { i := um.RewardPoints.Int32; return &i }(),  // Convert sql.NullInt32 to *int32
			MissionMetadata:     um.Metadata,
		})
	}
	return userMissionDTOs, nil
}

// UpdateUserMissionStatus updates the status of a user's mission
func (s *MissionService) UpdateUserMissionStatus(ctx context.Context, userMissionID int64, req dtos.UpdateUserMissionStatusRequestDTO) (dtos.UserMissionDTO, error) {
	completedAt := sql.NullTime{}
	if req.CompletedAt != nil {
		completedAt = sql.NullTime{Time: *req.CompletedAt, Valid: true}
	} else if req.Status == "completed" { // If status is completed but no time provided, use now
		completedAt = sql.NullTime{Time: time.Now(), Valid: true}
	}

	err := s.q.UpdateUserMissionStatus(ctx, gen.UpdateUserMissionStatusParams{
		Status:      req.Status,
		CompletedAt: completedAt,
		ID:          userMissionID,
	})
	if err != nil {
		return dtos.UserMissionDTO{}, err
	}

	// Fetch the updated user mission with mission details
	um, err := s.q.GetUserMissionByID(ctx, userMissionID)
	if err != nil {
		return dtos.UserMissionDTO{}, err
	}

	// Award points on transition to completed
	if s.points != nil {
		// We don't have previous status here; rely on requested status == completed to trigger
		if req.Status == "completed" {
			if um.RewardPoints.Valid && um.RewardPoints.Int32 > 0 {
				refID := userMissionID
				if _, err4 := s.points.Add(ctx, um.UserID, int64(um.RewardPoints.Int32), "mission_completed", "mission", &refID); err4 != nil {
					log.Printf("points award failed: context=mission_completed user_id=%d ref_id=%d error=%v", um.UserID, refID, err4)
				}
			}
		}
	}

	// Map to DTO
	var dto dtos.UserMissionDTO
	dto.ID = um.ID
	dto.MissionID = um.MissionID
	dto.UserID = um.UserID
	dto.Status = um.Status
	dto.StartedAt = um.StartedAt
	if um.CompletedAt.Valid {
		t := um.CompletedAt.Time
		dto.CompletedAt = &t
	}
	dto.Progress = um.Progress
	dto.MissionTitle = um.Title
	if um.Description.Valid {
		s := um.Description.String
		dto.MissionDescription = &s
	}
	if um.RewardPoints.Valid {
		i := um.RewardPoints.Int32
		dto.MissionRewardPoints = &i
	}
	dto.MissionMetadata = um.Metadata
	return dto, nil
}

// DeleteUserMission deletes a user's mission instance
func (s *MissionService) DeleteUserMission(ctx context.Context, userMissionID int64) error {
	return s.q.DeleteUserMission(ctx, userMissionID)
}
