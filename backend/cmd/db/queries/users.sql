-- name: GetUserByID :one
SELECT * FROM users
WHERE id = ?;

-- name: GetUserByEmail :one
SELECT * FROM users
WHERE email = ?;

-- name: GetUserByUsername :one
SELECT * FROM users
WHERE username = ?;

-- name: GetUsers :many
SELECT * FROM users
ORDER BY created_at DESC;

-- name: CreateUser :execresult
INSERT INTO users (
  email, username, password, display_name, role
) VALUES (
  ?, ?, ?, ?, ?
);

-- name: UpdateUser :exec
UPDATE users
SET
  username = ?,
  display_name = ?,
  email = ?,
  role = ?
WHERE id = ?;

-- name: UpdateUserProfile :exec
UPDATE user_profiles
SET
  avatar_url = ?,
  bio = ?
WHERE user_id = ?;

-- name: UpdateUserRole :exec
UPDATE users
SET
  role = ?
WHERE id = ?;

-- name: UpdateUserPassword :exec
UPDATE users
SET
  password = ?,
  updated_at = CURRENT_TIMESTAMP
WHERE id = ?;

-- name: DeleteUser :exec
DELETE FROM users
WHERE id = ?;

-- name: SearchUsers :many
SELECT * FROM users
WHERE
  (display_name LIKE CONCAT('%', sqlc.arg('search'), '%') OR username LIKE CONCAT('%', sqlc.arg('search'), '%') OR email LIKE CONCAT('%', sqlc.arg('search'), '%'))
  AND
  (role = sqlc.arg('role') OR sqlc.arg('role') = '')
ORDER BY created_at DESC
LIMIT ? OFFSET ?;

-- name: CountSearchedUsers :one
SELECT count(*) FROM users
WHERE
  (display_name LIKE CONCAT('%', sqlc.arg('search'), '%') OR username LIKE CONCAT('%', sqlc.arg('search'), '%') OR email LIKE CONCAT('%', sqlc.arg('search'), '%'))
  AND
  (role = sqlc.arg('role') OR sqlc.arg('role') = '');

SELECT role FROM users;

-- name: CreateUserLearningStyle :exec
INSERT INTO student_learning_styles
  (student_id, type, label, score_visual, score_auditory, score_reading, score_kinesthetic)
VALUES ((SELECT student_id FROM students WHERE user_id = ?), ?, ?, ?, ?, ?, ?);

-- name: GetLatestUserLearningStyle :one
SELECT sls.id, sls.student_id, sls.type, sls.label, sls.score_visual, sls.score_auditory, sls.score_reading, sls.score_kinesthetic, sls.created_at
FROM student_learning_styles sls
JOIN students s ON s.student_id = sls.student_id
WHERE s.user_id = ?
ORDER BY sls.created_at DESC
LIMIT 1;

-- name: GetActiveStudents :many
SELECT id, email, username, display_name FROM users
WHERE role = 'siswa';

-- name: GetUserProfileByID :one
SELECT
  u.id,
  u.username,
  u.display_name,
  u.email,
  u.role,
  p.avatar_url,
  p.bio,
  u.created_at,
  u.updated_at
FROM users u
LEFT JOIN user_profiles p ON p.user_id = u.id
WHERE u.id = ?;

-- name: UpsertUserProfile :exec
INSERT INTO user_profiles (user_id, avatar_url, bio)
VALUES (?, ?, ?)
ON DUPLICATE KEY UPDATE
  avatar_url = VALUES(avatar_url),
  bio        = VALUES(bio);
