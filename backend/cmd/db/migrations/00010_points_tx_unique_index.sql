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
-- Use a distinct name to avoid clashes if an index already exists
ALTER TABLE points_transactions
  DROP INDEX uniq_points_tx_ref;
-- +goose StatementEnd
