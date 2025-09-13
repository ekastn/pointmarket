-- name: CreateLesson :execresult
INSERT INTO lessons (
    course_id, title, ordinal, content
) VALUES (
    ?, ?, ?, ?
);

-- name: GetLessonByID :one
SELECT id, course_id, title, ordinal, content
FROM lessons
WHERE id = ?;

-- name: GetLessonsByCourseID :many
SELECT id, course_id, title, ordinal, content
FROM lessons
WHERE course_id = ?
ORDER BY ordinal ASC, id ASC
LIMIT ? OFFSET ?;

-- name: CountLessonsByCourseID :one
SELECT COUNT(*) FROM lessons WHERE course_id = ?;

-- name: UpdateLesson :exec
UPDATE lessons
SET
    title = ?,
    ordinal = ?,
    content = ?
WHERE id = ?;

-- name: DeleteLesson :exec
DELETE FROM lessons
WHERE id = ?;

