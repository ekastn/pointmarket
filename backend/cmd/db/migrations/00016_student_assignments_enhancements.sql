-- +goose Up
-- +goose StatementBegin
-- Extend student_assignments with attempt, feedback, grader_user_id, attachments
ALTER TABLE student_assignments
  ADD COLUMN attempt INT NOT NULL DEFAULT 1 AFTER status,
  ADD COLUMN feedback TEXT NULL AFTER submission,
  ADD COLUMN grader_user_id BIGINT NULL AFTER graded_at,
  ADD COLUMN attachments JSON NULL AFTER grader_user_id;
-- +goose StatementEnd

-- +goose Down
-- +goose StatementBegin
ALTER TABLE student_assignments
  DROP COLUMN attachments,
  DROP COLUMN grader_user_id,
  DROP COLUMN feedback,
  DROP COLUMN attempt;
-- +goose StatementEnd
