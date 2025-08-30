-- Quizzes --

-- name: CreateQuiz :execresult
INSERT INTO quizzes (
    title, description, course_id, reward_points, duration_minutes, status
) VALUES (
    ?, ?, ?, ?, ?, ?
);

-- name: GetQuizByID :one
SELECT * FROM quizzes
WHERE id = ?;

-- name: GetQuizzes :many
SELECT * FROM quizzes
ORDER BY created_at DESC;

-- name: GetQuizzesByCourseID :many
SELECT * FROM quizzes
WHERE course_id = ?
ORDER BY created_at DESC;

-- name: GetQuizzesByOwnerID :many
SELECT q.* FROM quizzes q
JOIN courses c ON q.course_id = c.id
WHERE c.owner_id = ?
ORDER BY q.created_at DESC;

-- name: UpdateQuiz :exec
UPDATE quizzes
SET
    title = ?,
    description = ?,
    course_id = ?,
    reward_points = ?,
    duration_minutes = ?,
    status = ?
WHERE id = ?;

-- name: DeleteQuiz :exec
DELETE FROM quizzes
WHERE id = ?;

-- Quiz Questions --

-- name: CreateQuizQuestion :execresult
INSERT INTO quiz_questions (
    quiz_id, question_text, question_type, answer_options, correct_answer
) VALUES (
    ?, ?, ?, ?, ?
);

-- name: GetQuizQuestionByID :one
SELECT * FROM quiz_questions
WHERE id = ?;

-- name: GetQuizQuestionsByQuizID :many
SELECT * FROM quiz_questions
WHERE quiz_id = ?
ORDER BY created_at ASC;

-- name: UpdateQuizQuestion :exec
UPDATE quiz_questions
SET
    quiz_id = ?,
    question_text = ?,
    question_type = ?,
    answer_options = ?,
    correct_answer = ?
WHERE id = ?;

-- name: DeleteQuizQuestion :exec
DELETE FROM quiz_questions
WHERE id = ?;

-- Student Quizzes --

-- name: CreateStudentQuiz :execresult
INSERT INTO student_quizzes (
    student_id, quiz_id, status, started_at
) VALUES (
    (SELECT student_id FROM students WHERE user_id = ?), ?, ?, ?
);

-- name: GetStudentQuizByID :one
SELECT * FROM student_quizzes
WHERE id = ?;

-- name: GetStudentQuizzesByStudentID :many
SELECT sq.id, sq.student_id, sq.quiz_id, sq.score, sq.status, sq.started_at, sq.completed_at, sq.created_at, sq.updated_at,
       q.title AS quiz_title, q.description AS quiz_description, q.course_id AS quiz_course_id, q.reward_points AS quiz_reward_points, q.duration_minutes AS quiz_duration_minutes
FROM student_quizzes sq
JOIN quizzes q ON sq.quiz_id = q.id
WHERE sq.student_id = (SELECT student_id FROM students WHERE user_id = ?)
ORDER BY sq.created_at DESC;

-- name: GetStudentQuizzesByQuizID :many
SELECT sq.id, sq.student_id, sq.quiz_id, sq.score, sq.status, sq.started_at, sq.completed_at, sq.created_at, sq.updated_at,
       u.display_name AS student_name, u.email AS student_email
FROM student_quizzes sq
JOIN students s ON sq.student_id = s.student_id
JOIN users u ON s.user_id = u.id
WHERE sq.quiz_id = ?
ORDER BY sq.created_at DESC;

-- name: UpdateStudentQuiz :exec
UPDATE student_quizzes
SET
    score = ?,
    status = ?,
    started_at = ?,
    completed_at = ?
WHERE id = ?;

-- name: DeleteStudentQuiz :exec
DELETE FROM student_quizzes
WHERE id = ?;

-- name: GetStudentQuizByIDs :one
SELECT * FROM student_quizzes
WHERE student_id = (SELECT student_id FROM students WHERE user_id = ?) AND quiz_id = ?;
