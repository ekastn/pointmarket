-- +goose Up
-- +goose StatementBegin
/* sqlc:ignore */
-- Ensure idempotency: drop then recreate with zero default points
DROP TRIGGER IF EXISTS trg_users_after_insert_init_user_stats;
CREATE TRIGGER trg_users_after_insert_init_user_stats
AFTER INSERT ON users
FOR EACH ROW
  INSERT IGNORE INTO user_stats (user_id, total_points, updated_at)
  VALUES (NEW.id, 0, CURRENT_TIMESTAMP);
-- +goose StatementEnd

-- +goose Down
-- +goose StatementBegin
/* sqlc:ignore */
-- Revert to previous behavior (100 default points)
DROP TRIGGER IF EXISTS trg_users_after_insert_init_user_stats;
CREATE TRIGGER trg_users_after_insert_init_user_stats
AFTER INSERT ON users
FOR EACH ROW
  INSERT IGNORE INTO user_stats (user_id, total_points, updated_at)
  VALUES (NEW.id, 100, CURRENT_TIMESTAMP);
-- +goose StatementEnd

