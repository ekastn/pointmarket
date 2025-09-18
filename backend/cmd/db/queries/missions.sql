-- name: GetMissionsByIDs :many
SELECT 
  id,
  title,
  description,
  reward_points,
  metadata,
  created_at,
  updated_at
FROM missions
WHERE id IN (sqlc.slice('ids'));

