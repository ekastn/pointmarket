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
    (SELECT student_id FROM students WHERE user_id = ?), ?
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
WHERE sc.student_id = (SELECT student_id FROM students WHERE user_id = ?)
ORDER BY c.title ASC;

-- name: UnenrollStudentFromCourse :exec
DELETE FROM student_courses
WHERE student_id = (SELECT student_id FROM students WHERE user_id = ?) AND course_id = ?;

-- name: CountCoursesByOwnerID :one
SELECT count(*) FROM courses
WHERE owner_id = ?;

-- name: GetCoursesWithEnrollmentStatus :many
SELECT
    c.*,
    CASE WHEN sc.student_id IS NOT NULL THEN TRUE ELSE FALSE END AS is_enrolled
FROM courses AS c
LEFT JOIN student_courses AS sc ON c.id = sc.course_id AND sc.student_id = (SELECT student_id FROM students WHERE user_id = sqlc.arg('user_id'))
WHERE
    (c.title LIKE CONCAT('%', sqlc.arg('search'), '%') OR
     c.description LIKE CONCAT('%', sqlc.arg('search'), '%'))
    OR sqlc.arg('search') = '' -- If search is empty, return all
ORDER BY c.created_at DESC
LIMIT ? OFFSET ?;

-- name: CountCoursesWithEnrollmentStatus :one
SELECT
    COUNT(c.id)
FROM courses AS c
WHERE
    (c.title LIKE CONCAT('%', sqlc.arg('search'), '%') OR
     c.description LIKE CONCAT('%', sqlc.arg('search'), '%'))
    OR sqlc.arg('search') = '';

-- name: GetCoursesWithOwnershipStatus :many
SELECT
    c.*,
    CASE WHEN c.owner_id = sqlc.arg('user_id') THEN TRUE ELSE FALSE END AS is_owner
FROM courses AS c
WHERE
    (c.title LIKE CONCAT('%', sqlc.arg('search'), '%') OR
     c.description LIKE CONCAT('%', sqlc.arg('search'), '%'))
    OR sqlc.arg('search') = ''
ORDER BY c.created_at DESC
LIMIT ? OFFSET ?;

-- name: CountCoursesWithOwnershipStatus :one
SELECT
    COUNT(c.id)
FROM courses AS c
WHERE
    (c.title LIKE CONCAT('%', sqlc.arg('search'), '%') OR
     c.description LIKE CONCAT('%', sqlc.arg('search'), '%'))
    OR sqlc.arg('search') = '';

-- name: GetCourseBySlug :one
SELECT * FROM courses
WHERE slug = ?
LIMIT 1;

-- name: GetEnrolledStudentsByCourseID :many
SELECT
    u.id as user_id,
    u.display_name,
    u.email,
    s.student_id
FROM student_courses sc
JOIN students s ON sc.student_id = s.student_id
JOIN users u ON s.user_id = u.id
WHERE sc.course_id = ?
ORDER BY u.display_name;
