-- +goose Up
-- +goose StatementBegin
/* sqlc:ignore */
CREATE TRIGGER trg_users_after_insert_init_user_stats
AFTER INSERT ON users
FOR EACH ROW
  INSERT IGNORE INTO user_stats (user_id, total_points, updated_at)
  VALUES (NEW.id, 100, CURRENT_TIMESTAMP);
-- +goose StatementEnd

-- +goose Down
-- +goose StatementBegin
/* sqlc:ignore */
DROP TRIGGER IF EXISTS trg_users_after_insert_init_user_stats;
-- +goose StatementEnd

