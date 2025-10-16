-- +goose Up
-- +goose StatementBegin
/* sqlc:ignore */
ALTER TABLE user_missions
  ADD UNIQUE INDEX uniq_user_mission (user_id, mission_id);
-- +goose StatementEnd

-- +goose Down
-- +goose StatementBegin
/* sqlc:ignore */
ALTER TABLE user_missions
  DROP INDEX uniq_user_mission;
-- +goose StatementEnd

