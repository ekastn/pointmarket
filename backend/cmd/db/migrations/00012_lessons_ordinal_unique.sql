-- +goose Up
-- +goose StatementBegin
ALTER TABLE lessons
ADD UNIQUE INDEX uniq_lessons_course_ordinal (course_id, ordinal);
-- +goose StatementEnd

-- +goose Down
-- +goose StatementBegin
-- Ensure FK on lessons.course_id remains supported by an index
SET @exists := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'lessons'
    AND index_name = 'idx_lessons_course_id'
);
SET @sql := IF(@exists = 0,
  'CREATE INDEX idx_lessons_course_id ON lessons (course_id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Now drop the composite unique index
ALTER TABLE lessons DROP INDEX uniq_lessons_course_ordinal;
-- +goose StatementEnd
