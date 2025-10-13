-- +goose Up
-- +goose StatementBegin
-- Ensure a default program with id=1 exists to satisfy the FK when auto-inserting students.
INSERT INTO programs (id, name, faculty_id)
SELECT 1, 'Default Program', NULL
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM programs WHERE id = 1);

-- Auto-create a students row for every newly inserted siswa user.
-- student_id format: '1' + 6 digits derived from user.id (mod 1,000,000), zero-padded → 1XXXXXX
-- program_id is fixed to 1; status defaults to active; cohort_year left NULL for later edits.
CREATE TRIGGER trg_users_after_insert_init_student
AFTER INSERT ON users
FOR EACH ROW
  INSERT IGNORE INTO students (student_id, user_id, program_id, cohort_year, status)
  SELECT
    CONCAT('1', LPAD(MOD(NEW.id, 1000000), 6, '0')),
    NEW.id,
    1,
    NULL,
    'active'
  FROM DUAL
  WHERE NEW.role = 'siswa';

/* Backfill existing siswa users missing student rows */
INSERT IGNORE INTO students (student_id, user_id, program_id, cohort_year, status)
SELECT
  CONCAT('1', LPAD(MOD(u.id, 1000000), 6, '0')),
  u.id,
  1,
  NULL,
  'active'
FROM users u
LEFT JOIN students s ON s.user_id = u.id
WHERE u.role = 'siswa' AND s.user_id IS NULL;
-- +goose StatementEnd

-- +goose Down
-- +goose StatementBegin
DROP TRIGGER IF EXISTS trg_users_after_insert_init_student;
-- +goose StatementEnd

