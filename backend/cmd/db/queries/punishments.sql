-- name: GetPunishmentsByIDs :many
SELECT 
  id,
  title,
  description,
  severity,
  metadata,
  is_active,
  created_at,
  updated_at
FROM punishments
WHERE is_active = 1
  AND id IN (sqlc.slice('ids'));
