package services

import (
	"context"
	"database/sql"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
)

// BadgeService provides business logic for badges and user badges
type BadgeService struct {
	q gen.Querier
}

// NewBadgeService creates a new BadgeService
func NewBadgeService(q gen.Querier) *BadgeService {
	return &BadgeService{q: q}
}

// CreateBadge creates a new badge
func (s *BadgeService) CreateBadge(ctx context.Context, req dtos.CreateBadgeRequestDTO) (dtos.BadgeDTO, error) {
	result, err := s.q.CreateBadge(ctx, gen.CreateBadgeParams{
		Title:       req.Title,
		Description: sql.NullString{String: *req.Description, Valid: req.Description != nil},
		Criteria:    req.Criteria,
		Repeatable:  req.Repeatable,
	})
	if err != nil {
		return dtos.BadgeDTO{}, err
	}

	id, err := result.LastInsertId()
	if err != nil {
		return dtos.BadgeDTO{}, err
	}

	badge, err := s.q.GetBadgeByID(ctx, id)
	if err != nil {
		return dtos.BadgeDTO{}, err
	}

	var badgeDTO dtos.BadgeDTO
	badgeDTO.FromBadgeModel(badge)
	return badgeDTO, nil
}

// GetBadgeByID retrieves a badge by its ID
func (s *BadgeService) GetBadgeByID(ctx context.Context, id int64) (dtos.BadgeDTO, error) {
	badge, err := s.q.GetBadgeByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.BadgeDTO{}, nil // Badge not found
	}
	if err != nil {
		return dtos.BadgeDTO{}, err
	}

	var badgeDTO dtos.BadgeDTO
	badgeDTO.FromBadgeModel(badge)
	return badgeDTO, nil
}

// GetBadges retrieves a list of all badges
func (s *BadgeService) GetBadges(ctx context.Context) ([]dtos.BadgeDTO, error) {
	badges, err := s.q.GetBadges(ctx)
	if err != nil {
		return nil, err
	}

	var badgeDTOs []dtos.BadgeDTO
	for _, badge := range badges {
		var badgeDTO dtos.BadgeDTO
		badgeDTO.FromBadgeModel(badge)
		badgeDTOs = append(badgeDTOs, badgeDTO)
	}
	return badgeDTOs, nil
}

// UpdateBadge updates an existing badge
func (s *BadgeService) UpdateBadge(ctx context.Context, id int64, req dtos.UpdateBadgeRequestDTO) (dtos.BadgeDTO, error) {
	// Get existing badge to apply partial updates
	existingBadge, err := s.q.GetBadgeByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.BadgeDTO{}, nil // Badge not found
	}
	if err != nil {
		return dtos.BadgeDTO{}, err
	}

	// Apply updates
	title := existingBadge.Title
	if req.Title != nil {
		title = *req.Title
	}

	description := existingBadge.Description
	if req.Description != nil {
		description = sql.NullString{String: *req.Description, Valid: true}
	}

	criteria := existingBadge.Criteria
	if req.Criteria != nil { // Assuming Criteria is always provided if updated
		criteria = req.Criteria
	}

	repeatable := existingBadge.Repeatable
	if req.Repeatable != nil {
		repeatable = *req.Repeatable
	}

	err = s.q.UpdateBadge(ctx, gen.UpdateBadgeParams{
		Title:       title,
		Description: description,
		Criteria:    criteria,
		Repeatable:  repeatable,
		ID:          id,
	})
	if err != nil {
		return dtos.BadgeDTO{}, err
	}

	updatedBadge, err := s.q.GetBadgeByID(ctx, id)
	if err != nil {
		return dtos.BadgeDTO{}, err
	}

	var badgeDTO dtos.BadgeDTO
	badgeDTO.FromBadgeModel(updatedBadge)
	return badgeDTO, nil
}

// DeleteBadge deletes a badge by its ID
func (s *BadgeService) DeleteBadge(ctx context.Context, id int64) error {
	return s.q.DeleteBadge(ctx, id)
}

// AwardBadgeToUser awards a badge to a specific user
func (s *BadgeService) AwardBadgeToUser(ctx context.Context, userID, badgeID int64) error {
	_, err := s.q.AwardBadgeToUser(ctx, gen.AwardBadgeToUserParams{
		UserID:  userID,
		BadgeID: badgeID,
	})
	return err
}

// GetUserBadgesByUserID retrieves all badges awarded to a specific user
func (s *BadgeService) GetUserBadgesByUserID(ctx context.Context, userID int64) ([]dtos.UserBadgeDTO, error) {
	userBadges, err := s.q.GetUserBadgesByUserID(ctx, userID)
	if err != nil {
		return nil, err
	}

	var userBadgeDTOs []dtos.UserBadgeDTO
	for _, ub := range userBadges {
		userBadgeDTOs = append(userBadgeDTOs, dtos.UserBadgeDTO{
			UserID:           ub.UserID,
			BadgeID:          ub.BadgeID,
			AwardedAt:        ub.AwardedAt,
			BadgeTitle:       ub.Title,
			BadgeDescription: func() *string { s := ub.Description.String; return &s }(), // Convert sql.NullString to *string
			BadgeCriteria:    ub.Criteria,
			BadgeRepeatable:  ub.Repeatable,
		})
	}
	return userBadgeDTOs, nil
}

// RevokeBadgeFromUser revokes a badge from a user
func (s *BadgeService) RevokeBadgeFromUser(ctx context.Context, userID, badgeID int64) error {
	return s.q.RevokeBadgeFromUser(ctx, gen.RevokeBadgeFromUserParams{
		UserID:  userID,
		BadgeID: badgeID,
	})
}
