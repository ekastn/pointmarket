-- +goose Up
-- +goose StatementBegin
ALTER TABLE badges DROP COLUMN repeatable;
-- +goose StatementEnd

-- +goose Down
-- +goose StatementBegin
ALTER TABLE badges ADD COLUMN repeatable BOOLEAN NOT NULL DEFAULT FALSE;
-- +goose StatementEnd

