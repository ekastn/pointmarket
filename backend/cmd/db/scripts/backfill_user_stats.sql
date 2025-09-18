-- Backfill user_stats for existing users who don't have a stats row.
-- Safe to run multiple times due to INSERT IGNORE.

INSERT IGNORE INTO user_stats (user_id, total_points, updated_at)
SELECT u.id, 100, CURRENT_TIMESTAMP
FROM users u
LEFT JOIN user_stats us ON us.user_id = u.id
WHERE us.user_id IS NULL;

