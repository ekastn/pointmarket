-- +goose Up
-- +goose StatementBegin
ALTER TABLE quiz_questions
  ADD COLUMN ordinal INT NOT NULL DEFAULT 0;

-- Backfill ordinal per quiz using window function
WITH ranked AS (
  SELECT id, ROW_NUMBER() OVER (PARTITION BY quiz_id ORDER BY id) AS rn
  FROM quiz_questions
)
UPDATE quiz_questions q
JOIN ranked r ON r.id = q.id
SET q.ordinal = r.rn;

-- Ensure unique ordinal per quiz
ALTER TABLE quiz_questions
  ADD UNIQUE INDEX uq_quiz_questions_ordinal (quiz_id, ordinal);
-- +goose StatementEnd

-- +goose Down
-- +goose StatementBegin
-- Ensure an index exists for the FK on quiz_questions(quiz_id) before dropping the composite unique index
SET @exists := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'quiz_questions'
    AND index_name = 'idx_quiz_questions_quiz_id'
);
SET @sql := IF(@exists = 0,
  'CREATE INDEX idx_quiz_questions_quiz_id ON quiz_questions (quiz_id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Drop the composite unique index used for ordering
ALTER TABLE quiz_questions DROP INDEX uq_quiz_questions_ordinal;

-- Drop the ordinal column
ALTER TABLE quiz_questions DROP COLUMN ordinal;
-- +goose StatementEnd
