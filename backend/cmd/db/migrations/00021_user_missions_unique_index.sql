-- +goose Up
-- +goose StatementBegin
/* sqlc:ignore */
ALTER TABLE user_missions
  ADD UNIQUE INDEX uniq_user_mission (user_id, mission_id);
-- +goose StatementEnd

-- +goose Down
-- +goose StatementBegin
/* sqlc:ignore */
-- Ensure supporting indexes for FKs exist before dropping composite unique index
-- Create index on user_missions.user_id if missing
SET @exists := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'user_missions'
    AND index_name = 'idx_user_missions_user_id'
);
SET @sql := IF(@exists = 0,
  'CREATE INDEX idx_user_missions_user_id ON user_missions (user_id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Create index on user_missions.mission_id if missing
SET @exists := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'user_missions'
    AND index_name = 'idx_user_missions_mission_id'
);
SET @sql := IF(@exists = 0,
  'CREATE INDEX idx_user_missions_mission_id ON user_missions (mission_id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Now it is safe to drop the composite unique index
ALTER TABLE user_missions
  DROP INDEX uniq_user_mission;
-- +goose StatementEnd

