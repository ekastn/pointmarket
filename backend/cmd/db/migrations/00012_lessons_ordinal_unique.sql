-- +goose Up
-- +goose StatementBegin
ALTER TABLE lessons
ADD UNIQUE INDEX uniq_lessons_course_ordinal (course_id, ordinal);
-- +goose StatementEnd

-- +goose Down
-- +goose StatementBegin
ALTER TABLE lessons
DROP INDEX uniq_lessons_course_ordinal;
-- +goose StatementEnd

