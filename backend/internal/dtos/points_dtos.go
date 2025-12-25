package dtos

import "time"

// AdjustUserStatsRequest represents the admin payload to add/deduct points.
// Delta must be non-zero. Positive adds points; negative deducts.
type AdjustUserStatsRequest struct {
	Delta         int64   `json:"delta"`
	Reason        *string `json:"reason,omitempty"`
	ReferenceType *string `json:"reference_type,omitempty"`
	ReferenceID   *int64  `json:"reference_id,omitempty"`
}

// UserStatsResponse represents the current user points stats.
type UserStatsResponse struct {
	TotalPoints int64      `json:"total_points"`
	UpdatedAt   *time.Time `json:"updated_at,omitempty"`
}
