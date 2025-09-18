-- name: GetCoachingsByIDs :many
SELECT 
  id,
  title,
  description,
  duration_minutes,
  sessions,
  modality,
  is_active,
  created_at,
  updated_at
FROM coachings
WHERE is_active = 1
  AND id IN (sqlc.slice('ids'));
