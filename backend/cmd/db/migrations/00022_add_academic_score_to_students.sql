-- +goose Up
ALTER TABLE students
ADD COLUMN academic_score FLOAT NOT NULL DEFAULT 0.0;

-- +goose Down
ALTER TABLE students
DROP COLUMN academic_score;
