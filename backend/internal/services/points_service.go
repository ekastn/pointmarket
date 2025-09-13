package services

import (
	"context"
	"database/sql"
	"errors"
	"strings"

	mysqlerr "github.com/go-sql-driver/mysql"
	"pointmarket/backend/internal/store/gen"
)

var (
	// ErrInvalidAmount is returned when amount <= 0
	ErrInvalidAmount = errors.New("invalid amount; must be > 0")
	// ErrInsufficientPoints is returned when a deduction would drop balance below zero
	ErrInsufficientPoints = errors.New("insufficient points")
)

// PointsService manages user points and transactions atomically.
type PointsService struct {
	db *sql.DB
	q  *gen.Queries
}

// NewPointsService constructs a PointsService.
func NewPointsService(db *sql.DB, q *gen.Queries) *PointsService {
	return &PointsService{db: db, q: q}
}

// GetOrInitTotal ensures a user_stats row exists and returns the current total.
func (s *PointsService) GetOrInitTotal(ctx context.Context, userID int64) (int64, error) {
	if err := s.q.InitUserStatsIfMissing(ctx, userID); err != nil {
		return 0, err
	}
	stats, err := s.q.GetUserStats(ctx, userID)
	if err != nil {
		return 0, err
	}
	return stats.TotalPoints, nil
}

// Add credits points to the user and records a transaction. Returns new total.
func (s *PointsService) Add(ctx context.Context, userID int64, amount int64, reason, refType string, refID *int64) (int64, error) {
	if amount <= 0 {
		return 0, ErrInvalidAmount
	}
	reason = strings.TrimSpace(reason)
	refType = strings.TrimSpace(refType)

	tx, err := s.db.BeginTx(ctx, nil)
	if err != nil {
		return 0, err
	}
	defer tx.Rollback()

	qtx := gen.New(tx)

	if err := qtx.InitUserStatsIfMissing(ctx, userID); err != nil {
		return 0, err
	}
	stats, err := qtx.GetUserStats(ctx, userID)
	if err != nil {
		return 0, err
	}

	newTotal := stats.TotalPoints + amount

	var reasonNull sql.NullString
	if reason != "" {
		if len(reason) > 255 {
			reason = reason[:255]
		}
		reasonNull = sql.NullString{String: reason, Valid: true}
	}
	var refTypeNull sql.NullString
	if refType != "" {
		if len(refType) > 255 {
			refType = refType[:255]
		}
		refTypeNull = sql.NullString{String: refType, Valid: true}
	}
	var refIDNull sql.NullInt64
	if refID != nil {
		refIDNull = sql.NullInt64{Int64: *refID, Valid: true}
	}

	if _, err := qtx.CreatePointsTransaction(ctx, gen.CreatePointsTransactionParams{
		UserID:        userID,
		Amount:        int32(amount),
		Reason:        reasonNull,
		ReferenceType: refTypeNull,
		ReferenceID:   refIDNull,
	}); err != nil {
		// If duplicate transaction (idempotent), return current total without updating
		var me *mysqlerr.MySQLError
		if errors.As(err, &me) && me.Number == 1062 {
			return stats.TotalPoints, nil
		}
		return 0, err
	}

	if err := qtx.UpdateUserStatsPoints(ctx, gen.UpdateUserStatsPointsParams{
		TotalPoints: newTotal,
		UserID:      userID,
	}); err != nil {
		return 0, err
	}

	if err := tx.Commit(); err != nil {
		return 0, err
	}
	return newTotal, nil
}

// Deduct debits points from the user and records a transaction. Returns new total.
func (s *PointsService) Deduct(ctx context.Context, userID int64, amount int64, reason, refType string, refID *int64) (int64, error) {
	if amount <= 0 {
		return 0, ErrInvalidAmount
	}
	reason = strings.TrimSpace(reason)
	refType = strings.TrimSpace(refType)

	tx, err := s.db.BeginTx(ctx, nil)
	if err != nil {
		return 0, err
	}
	defer tx.Rollback()

	qtx := gen.New(tx)

	if err := qtx.InitUserStatsIfMissing(ctx, userID); err != nil {
		return 0, err
	}
	stats, err := qtx.GetUserStats(ctx, userID)
	if err != nil {
		return 0, err
	}

	if stats.TotalPoints < amount {
		return 0, ErrInsufficientPoints
	}
	newTotal := stats.TotalPoints - amount

	var reasonNull sql.NullString
	if reason != "" {
		if len(reason) > 255 {
			reason = reason[:255]
		}
		reasonNull = sql.NullString{String: reason, Valid: true}
	}
	var refTypeNull sql.NullString
	if refType != "" {
		if len(refType) > 255 {
			refType = refType[:255]
		}
		refTypeNull = sql.NullString{String: refType, Valid: true}
	}
	var refIDNull sql.NullInt64
	if refID != nil {
		refIDNull = sql.NullInt64{Int64: *refID, Valid: true}
	}

	// Store negative amount for deductions
	if _, err := qtx.CreatePointsTransaction(ctx, gen.CreatePointsTransactionParams{
		UserID:        userID,
		Amount:        int32(-amount),
		Reason:        reasonNull,
		ReferenceType: refTypeNull,
		ReferenceID:   refIDNull,
	}); err != nil {
		var me *mysqlerr.MySQLError
		if errors.As(err, &me) && me.Number == 1062 {
			// Duplicate deduction by same reference → idempotent no-op
			return stats.TotalPoints, nil
		}
		return 0, err
	}

	if err := qtx.UpdateUserStatsPoints(ctx, gen.UpdateUserStatsPointsParams{
		TotalPoints: newTotal,
		UserID:      userID,
	}); err != nil {
		return 0, err
	}

	if err := tx.Commit(); err != nil {
		return 0, err
	}
	return newTotal, nil
}

// ListTransactions returns a page of transactions for a user (newest first).
func (s *PointsService) ListTransactions(ctx context.Context, userID int64, page, limit int) ([]gen.PointsTransaction, error) {
	if page <= 0 {
		page = 1
	}
	if limit <= 0 {
		limit = 10
	}
	offset := int32((page - 1) * limit)
	txs, err := s.q.ListPointsTransactionsByUserID(ctx, gen.ListPointsTransactionsByUserIDParams{
		UserID: userID,
		Limit:  int32(limit),
		Offset: offset,
	})
	if err != nil {
		return nil, err
	}
	return txs, nil
}
