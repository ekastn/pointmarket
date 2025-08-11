-- Assignments --

-- name: CreateAssignment :execresult
INSERT INTO assignments (
    title, description, course_id, reward_points, due_date, status
) VALUES (
    ?, ?, ?, ?, ?, ?
);

-- name: GetAssignmentByID :one
SELECT * FROM assignments
WHERE id = ?;

-- name: GetAssignments :many
SELECT * FROM assignments
ORDER BY created_at DESC;

-- name: UpdateAssignment :exec
UPDATE assignments
SET
    title = ?,
    description = ?,
    course_id = ?,
    reward_points = ?,
    due_date = ?,
    status = ?
WHERE id = ?;

-- name: DeleteAssignment :exec
DELETE FROM assignments
WHERE id = ?;

-- name: GetAssignmentsByCourseID :many
SELECT * FROM assignments
WHERE course_id = ?
ORDER BY created_at DESC;

-- name: GetAssignmentsByOwnerID :many
SELECT a.* FROM assignments a
JOIN courses c ON a.course_id = c.id
WHERE c.owner_id = ?
ORDER BY a.created_at DESC;

-- name: GetAssignmentByCourseIDAndOwnerID :one
SELECT a.* FROM assignments a
JOIN courses c ON a.course_id = c.id
WHERE a.id = ? AND c.owner_id = ?;

-- Student Assignments --

-- name: CreateStudentAssignment :execresult
INSERT INTO student_assignments (
    student_id, assignment_id, status, submission
) VALUES (
    ?, ?, ?, ?
);

-- name: GetStudentAssignmentByID :one
SELECT * FROM student_assignments
WHERE id = ?;

-- name: GetStudentAssignmentsByStudentID :many
SELECT sa.id, sa.student_id, sa.assignment_id, sa.status, sa.score, sa.submission, sa.submitted_at, sa.graded_at, sa.created_at, sa.updated_at,
       a.title AS assignment_title, a.description AS assignment_description, a.course_id AS assignment_course_id, a.reward_points AS assignment_reward_points, a.due_date AS assignment_due_date
FROM student_assignments sa
JOIN assignments a ON sa.assignment_id = a.id
WHERE sa.student_id = ?
ORDER BY sa.created_at DESC;

-- name: GetStudentAssignmentsByAssignmentID :many
SELECT sa.id, sa.student_id, sa.assignment_id, sa.status, sa.score, sa.submission, sa.submitted_at, sa.graded_at, sa.created_at, sa.updated_at,
       u.display_name AS student_name, u.email AS student_email
FROM student_assignments sa
JOIN users u ON sa.student_id = u.id
WHERE sa.assignment_id = ?
ORDER BY sa.created_at DESC;

-- name: UpdateStudentAssignment :exec
UPDATE student_assignments
SET
    status = ?,
    score = ?,
    submission = ?,
    submitted_at = ?,
    graded_at = ?
WHERE id = ?;

-- name: DeleteStudentAssignment :exec
DELETE FROM student_assignments
WHERE id = ?;

-- name: GetStudentAssignmentByIDs :one
SELECT * FROM student_assignments
WHERE student_id = ? AND assignment_id = ?;