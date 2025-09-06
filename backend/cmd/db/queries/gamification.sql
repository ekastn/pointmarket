-- Badges --

-- name: CreateBadge :execresult
INSERT INTO badges (
    title, description, criteria
) VALUES (
    ?, ?, ?
);

-- name: GetBadgeByID :one
SELECT * FROM badges
WHERE id = ?;

-- name: GetBadges :many
SELECT * FROM badges
ORDER BY created_at DESC
LIMIT ? OFFSET ?;

-- name: CountBadges :one
SELECT count(*) FROM badges;

-- name: UpdateBadge :exec
UPDATE badges
SET
    title = ?,
    description = ?,
    criteria = ?
WHERE id = ?;

-- name: DeleteBadge :exec
DELETE FROM badges
WHERE id = ?;


-- Missions --

-- name: CreateMission :execresult
INSERT INTO missions (
    title, description, reward_points, metadata
) VALUES (
    ?, ?, ?, ?
);

-- name: GetMissionByID :one
SELECT * FROM missions
WHERE id = ?;

-- name: GetMissions :many
SELECT * FROM missions
ORDER BY created_at DESC;

-- name: UpdateMission :exec
UPDATE missions
SET
    title = ?,
    description = ?,
    reward_points = ?,
    metadata = ?
WHERE id = ?;

-- name: DeleteMission :exec
DELETE FROM missions
WHERE id = ?;


-- User Badges --

-- name: AwardBadgeToUser :execresult
INSERT INTO user_badges (
    user_id, badge_id
) VALUES (
    ?, ?
);

-- name: GetUserBadgesByUserID :many
SELECT ub.user_id, ub.badge_id, ub.awarded_at, b.title, b.description, b.criteria
FROM user_badges ub
JOIN badges b ON ub.badge_id = b.id
WHERE ub.user_id = ?
ORDER BY ub.awarded_at DESC;

-- name: RevokeBadgeFromUser :exec
DELETE FROM user_badges
WHERE user_id = ? AND badge_id = ?;


-- User Missions --

-- name: CreateUserMission :execresult
INSERT INTO user_missions (
    mission_id, user_id, status, started_at, progress
) VALUES (
    ?, ?, ?, ?, ?
);

-- name: GetUserMissionsByUserID :many
SELECT um.id, um.mission_id, um.user_id, um.status, um.started_at, um.completed_at, um.progress, m.title, m.description, m.reward_points, m.metadata
FROM user_missions um
JOIN missions m ON um.mission_id = m.id
WHERE um.user_id = ?
ORDER BY um.started_at DESC;

-- name: UpdateUserMissionStatus :exec
UPDATE user_missions
SET
    status = ?,
    completed_at = ? -- Set to current timestamp if status is 'completed'
WHERE id = ?;

-- name: DeleteUserMission :exec
DELETE FROM user_missions
WHERE id = ?;

-- User Stats --

-- name: GetUserStats :one
SELECT * FROM user_stats
WHERE user_id = ?;

-- name: UpdateUserStatsPoints :exec
UPDATE user_stats
SET
    total_points = ?
WHERE user_id = ?;
