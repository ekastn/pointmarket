-- name: GetProductByID :one
SELECT
    p.*,
    pc.name AS category_name
FROM products p
LEFT JOIN product_categories pc ON p.category_id = pc.id
WHERE p.id = ?;

-- name: GetProducts :many
SELECT
    p.*,
    pc.name AS category_name
FROM products p
LEFT JOIN product_categories pc ON p.category_id = pc.id
WHERE (sqlc.arg('category_id') IS NULL OR p.category_id = sqlc.arg('category_id'))
  AND (sqlc.arg('only_active') = FALSE OR p.is_active = TRUE)
  AND (
    sqlc.arg('search') = '' OR
    p.name LIKE CONCAT('%', sqlc.arg('search'), '%') OR
    p.description LIKE CONCAT('%', sqlc.arg('search'), '%')
  )
ORDER BY created_at DESC
LIMIT ? OFFSET ?;

-- name: CountProducts :one
SELECT count(*) FROM products p
WHERE (sqlc.arg('category_id') IS NULL OR p.category_id = sqlc.arg('category_id'))
  AND (sqlc.arg('only_active') = FALSE OR p.is_active = TRUE)
  AND (
    sqlc.arg('search') = '' OR
    p.name LIKE CONCAT('%', sqlc.arg('search'), '%') OR
    p.description LIKE CONCAT('%', sqlc.arg('search'), '%')
  );

-- name: CreateProduct :execresult
INSERT INTO products (
    category_id, name, description, points_price, type, stock_quantity, is_active, metadata
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?
);

-- name: UpdateProduct :exec
UPDATE products
SET
    category_id = ?,
    name = ?,
    description = ?,
    points_price = ?,
    type = ?,
    stock_quantity = ?,
    is_active = ?,
    metadata = ?
WHERE id = ?;

-- name: DeleteProduct :exec
DELETE FROM products
WHERE id = ?;

-- name: CreateOrder :execresult
INSERT INTO orders (
    user_id, product_id, points_spent, status
) VALUES (
    ?, ?, ?, ?
);

-- name: UpdateProductStock :exec
UPDATE products
SET
    stock_quantity = ?
WHERE id = ?;

-- Atomically decrement stock if available (non-null and > 0)
-- name: DecrementProductStockIfAvailable :execresult
UPDATE products
SET stock_quantity = stock_quantity - 1
WHERE id = ? AND stock_quantity IS NOT NULL AND stock_quantity > 0;

-- Product Categories --

-- name: CreateProductCategory :execresult
INSERT INTO product_categories (
    name, description
) VALUES (
    ?, ?
);

-- name: GetProductCategoryByID :one
SELECT * FROM product_categories
WHERE id = ?;

-- name: GetProductCategories :many
SELECT * FROM product_categories
ORDER BY name ASC
LIMIT ? OFFSET ?;

-- name: CountProductCategories :one
SELECT count(*) FROM product_categories;

-- name: UpdateProductCategory :exec
UPDATE product_categories
SET
    name = ?,
    description = ?
WHERE id = ?;

-- name: DeleteProductCategory :exec
DELETE FROM product_categories
WHERE id = ?;

-- Course Details for Course-type Products --

-- name: GetProductCourseDetailByProductID :one
SELECT product_id, course_id, access_duration_days, enrollment_behavior
FROM product_course_details
WHERE product_id = ?;

-- name: GetProductsByIDs :many
SELECT 
  id,
  category_id,
  name,
  description,
  points_price,
  `type`,
  stock_quantity,
  is_active,
  metadata,
  created_at,
  updated_at
FROM products
WHERE is_active = 1
  AND id IN (sqlc.slice('ids'));

-- name: GetAllOrders :many
SELECT
    o.id,
    o.ordered_at,
    o.points_spent,
    o.status,
    p.name AS product_name,
    u.display_name AS user_name,
    COALESCE(us.total_points, 0) AS user_current_points
FROM orders o
JOIN products p ON o.product_id = p.id
JOIN users u ON o.user_id = u.id
LEFT JOIN user_stats us ON u.id = us.user_id
WHERE (
    sqlc.arg('search') = '' OR
    u.display_name LIKE CONCAT('%', sqlc.arg('search'), '%') OR
    p.name LIKE CONCAT('%', sqlc.arg('search'), '%')
)
ORDER BY o.ordered_at DESC
LIMIT ? OFFSET ?;

-- name: CountAllOrders :one
SELECT count(*)
FROM orders o
JOIN products p ON o.product_id = p.id
JOIN users u ON o.user_id = u.id
WHERE (
    sqlc.arg('search') = '' OR
    u.display_name LIKE CONCAT('%', sqlc.arg('search'), '%') OR
    p.name LIKE CONCAT('%', sqlc.arg('search'), '%')
);
