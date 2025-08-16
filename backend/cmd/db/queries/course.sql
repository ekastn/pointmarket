-- name: CreateCourse :execresult
INSERT INTO courses (
    title, slug, description, owner_id, metadata
) VALUES (
    ?, ?, ?, ?, ?
);

-- name: GetCourseByID :one
SELECT * FROM courses
WHERE id = ?;

-- name: GetCourses :many
SELECT * FROM courses
ORDER BY created_at DESC
LIMIT ? OFFSET ?;

-- name: CountCourses :one
SELECT count(*) FROM courses;

-- name: GetCoursesByOwnerID :many
SELECT * FROM courses
WHERE owner_id = ?
ORDER BY created_at DESC
LIMIT ? OFFSET ?;

-- name: UpdateCourse :exec
UPDATE courses
SET
    title = ?,
    slug = ?,
    description = ?,
    metadata = ?
WHERE id = ?;

-- name: DeleteCourse :exec
DELETE FROM courses
WHERE id = ?;

-- Student Enrollment --

-- name: EnrollStudentInCourse :execresult
INSERT INTO student_courses (
    student_id, course_id
) VALUES (
    ?, ?
);

-- name: GetStudentCoursesByUserID :many
SELECT
    sc.student_id,
    sc.course_id,
    sc.enrolled_at,
    c.title AS course_title,
    c.slug AS course_slug,
    c.description AS course_description,
    c.owner_id AS course_owner_id,
    c.metadata AS course_metadata
FROM student_courses sc
JOIN courses c ON sc.course_id = c.id
WHERE sc.student_id = ?
ORDER BY c.title ASC;

-- name: UnenrollStudentFromCourse :exec
DELETE FROM student_courses
WHERE student_id = ? AND course_id = ?;

-- name: CountCoursesByOwnerID :one
SELECT count(*) FROM courses
WHERE owner_id = ?;