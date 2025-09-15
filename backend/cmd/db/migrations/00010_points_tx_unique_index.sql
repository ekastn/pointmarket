-- +goose Up
-- +goose StatementBegin
-- MySQL CREATE INDEX does not support IF NOT EXISTS in many versions.
-- Use ALTER TABLE to add the unique index.
ALTER TABLE points_transactions
  ADD UNIQUE INDEX uniq_points_tx_ref (user_id, reference_type, reference_id);
-- +goose StatementEnd

-- +goose Down
-- +goose StatementBegin
-- Provide a standalone index on user_id to satisfy the FK, then drop the unique index
-- Create the supporting index if it does not exist
SET @exists := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'points_transactions'
    AND index_name = 'idx_points_transactions_user_id'
);
SET @sql := IF(@exists = 0,
  'CREATE INDEX idx_points_transactions_user_id ON points_transactions (user_id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Now drop the composite unique index
ALTER TABLE points_transactions DROP INDEX uniq_points_tx_ref;
-- +goose StatementEnd
