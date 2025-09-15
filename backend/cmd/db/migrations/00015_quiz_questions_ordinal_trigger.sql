-- +goose Up
-- +goose StatementBegin
-- Create trigger without changing delimiter by using a single SET statement
CREATE TRIGGER trg_quiz_questions_set_ordinal
BEFORE INSERT ON quiz_questions
FOR EACH ROW
SET NEW.ordinal = IF(NEW.ordinal IS NULL OR NEW.ordinal = 0,
                     1 + COALESCE((SELECT MAX(qq.ordinal) FROM quiz_questions qq WHERE qq.quiz_id = NEW.quiz_id), 0),
                     NEW.ordinal);
-- +goose StatementEnd

-- +goose Down
-- +goose StatementBegin
DROP TRIGGER IF EXISTS trg_quiz_questions_set_ordinal;
-- +goose StatementEnd
