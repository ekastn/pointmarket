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
  display_name = ?,
  email = ?
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

-- name: DeleteUser :exec
DELETE FROM users
WHERE id = ?;

-- name: SearchUsers :many
SELECT * FROM users
WHERE
  (display_name LIKE ? OR username LIKE ? OR email LIKE ?)
  AND
  (role = sqlc.arg('role') OR sqlc.arg('role') = '')
ORDER BY created_at DESC;

-- name: GetRoles :many
SELECT role FROM users;
