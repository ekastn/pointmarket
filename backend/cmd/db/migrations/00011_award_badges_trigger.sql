-- +goose Up
-- +goose StatementBegin
/* sqlc:ignore */
CREATE TRIGGER trg_user_stats_after_update_award_badges
AFTER UPDATE ON user_stats
FOR EACH ROW
  INSERT IGNORE INTO user_badges (user_id, badge_id)
  SELECT NEW.user_id, b.id
  FROM badges b
  WHERE NEW.total_points > OLD.total_points
    AND JSON_UNQUOTE(JSON_EXTRACT(b.criteria, '$.type')) = 'points_min'
    AND CAST(JSON_UNQUOTE(JSON_EXTRACT(b.criteria, '$.value')) AS UNSIGNED) <= NEW.total_points;
-- +goose StatementEnd

-- +goose Down
-- +goose StatementBegin
/* sqlc:ignore */
DROP TRIGGER IF EXISTS trg_user_stats_after_update_award_badges;
-- +goose StatementEnd
