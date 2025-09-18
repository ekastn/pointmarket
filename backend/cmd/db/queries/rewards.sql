-- name: GetRewardsByIDs :many
SELECT 
  id,
  title,
  description,
  type,
  metadata,
  is_active,
  created_at,
  updated_at
FROM rewards
WHERE is_active = 1
  AND id IN (sqlc.slice('ids'));
