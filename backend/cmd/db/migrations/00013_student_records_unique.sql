-- +goose Up
-- +goose StatementBegin
ALTER TABLE student_assignments
  ADD UNIQUE INDEX uq_student_assignment (student_id, assignment_id);

ALTER TABLE student_quizzes
  ADD UNIQUE INDEX uq_student_quiz (student_id, quiz_id);
-- +goose StatementEnd

-- +goose Down
-- +goose StatementBegin
-- Ensure supporting indexes for FKs exist before dropping composite unique indexes
-- Create index on student_assignments.student_id if missing
SET @exists := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'student_assignments'
    AND index_name = 'idx_student_assignments_student_id'
);
SET @sql := IF(@exists = 0,
  'CREATE INDEX idx_student_assignments_student_id ON student_assignments (student_id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Create index on student_quizzes.student_id if missing
SET @exists := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'student_quizzes'
    AND index_name = 'idx_student_quizzes_student_id'
);
SET @sql := IF(@exists = 0,
  'CREATE INDEX idx_student_quizzes_student_id ON student_quizzes (student_id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Now it is safe to drop the composite unique indexes
ALTER TABLE student_assignments DROP INDEX uq_student_assignment;
ALTER TABLE student_quizzes DROP INDEX uq_student_quiz;
-- +goose StatementEnd
