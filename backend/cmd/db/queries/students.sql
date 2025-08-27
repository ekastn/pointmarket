-- name: GetStudentByUserID :one
SELECT s.user_id,
       s.student_id,
       s.program_id,
       p.name AS program_name,
       s.cohort_year,
       s.status,
       s.birth_date,
       s.gender,
       s.phone,
       s.created_at,
       s.updated_at
FROM students s
JOIN programs p ON p.id = s.program_id
WHERE s.user_id = ?;

-- name: GetStudentByStudentID :one
SELECT s.user_id,
       s.student_id,
       s.program_id,
       p.name AS program_name,
       s.cohort_year,
       s.status,
       s.birth_date,
       s.gender,
       s.phone,
       s.created_at,
       s.updated_at
FROM students s
JOIN programs p ON p.id = s.program_id
WHERE s.student_id = ?;

-- name: InsertStudent :exec
INSERT INTO students (
  user_id, student_id, program_id, cohort_year, status, birth_date, gender, phone
) VALUES (?, ?, ?, ?, ?, ?, ?, ?);

-- name: UpdateStudentByUserID :exec
UPDATE students
SET student_id = ?,
    program_id = ?,
    cohort_year = ?,
    status = ?,
    birth_date = ?,
    gender = ?,
    phone = ?
WHERE user_id = ?;

-- name: SearchStudents :many
SELECT
  u.id          AS user_id,
  u.display_name,
  u.email,
  s.student_id,
  s.program_id,
  p.name        AS program_name,
  s.cohort_year,
  s.status,
  s.created_at,
  s.updated_at
FROM students s
JOIN users u ON u.id = s.user_id
JOIN programs p ON p.id = s.program_id
WHERE
  (u.display_name LIKE CONCAT('%', sqlc.arg('search'), '%')
   OR u.username LIKE CONCAT('%', sqlc.arg('search'), '%')
   OR u.email LIKE CONCAT('%', sqlc.arg('search'), '%')
   OR s.student_id LIKE CONCAT('%', sqlc.arg('search'), '%')
   OR sqlc.arg('search') = '')
  AND (s.program_id = sqlc.narg('program_id') OR sqlc.narg('program_id') IS NULL)
  AND (s.cohort_year = sqlc.arg('cohort_year') OR sqlc.arg('cohort_year') IS NULL)
  AND (s.status = sqlc.arg('status') OR sqlc.arg('status') = '')
ORDER BY u.display_name ASC
LIMIT ? OFFSET ?;

-- name: CountStudents :one
SELECT COUNT(*)
FROM students s
JOIN users u ON u.id = s.user_id
WHERE
  (u.display_name LIKE CONCAT('%', sqlc.arg('search'), '%')
   OR u.username LIKE CONCAT('%', sqlc.arg('search'), '%')
   OR u.email LIKE CONCAT('%', sqlc.arg('search'), '%')
   OR s.student_id LIKE CONCAT('%', sqlc.arg('search'), '%')
   OR sqlc.arg('search') = '')
  AND (s.program_id = sqlc.narg('program_id') OR sqlc.narg('program_id') IS NULL)
  AND (s.cohort_year = sqlc.arg('cohort_year') OR sqlc.arg('cohort_year') IS NULL)
  AND (s.status = sqlc.arg('status') OR sqlc.arg('status') = '');

-- name: ListPrograms :many
SELECT id, name, faculty_id, created_at, updated_at
FROM programs
ORDER BY name ASC;
