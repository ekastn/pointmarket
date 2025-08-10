-- name: GetProductByID :one
SELECT * FROM products
WHERE id = ?;

-- name: GetProducts :many
SELECT * FROM products
ORDER BY created_at DESC;