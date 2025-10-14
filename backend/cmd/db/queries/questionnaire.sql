-- name: GetQuestionnaires :many
SELECT *
FROM questionnaires;

-- name: GetActiveQuestionnaires :many
SELECT *
FROM questionnaires
WHERE status = 'active'
ORDER BY type, id;

-- name: GetQuestionnaireByID :one
SELECT *
FROM questionnaires
WHERE id = ? LIMIT 1;

-- name: GetQuestionsByQuestionnaireID :many
SELECT *
FROM questionnaire_questions
WHERE questionnaire_id = ?
ORDER BY question_number;

-- name: GetVarkOptionsByQuestionnaireID :many
SELECT *
FROM questionnaire_vark_options
WHERE question_id IN (
    SELECT id
    FROM questionnaire_questions
    WHERE questionnaire_id = ?
);

-- name: CreateLikertResult :exec
INSERT INTO student_questionnaire_likert_results
  (student_id, questionnaire_id, answers, total_score, subscale_scores, weekly_evaluation_id)
VALUES ((SELECT student_id FROM students WHERE user_id = ?), ?, ?, ?, ?, ?);

-- name: CreateVarkResult :exec
INSERT INTO student_questionnaire_vark_results
  (student_id, questionnaire_id, vark_type, vark_label, score_visual, score_auditory, score_reading, score_kinesthetic, answers)
VALUES ((SELECT student_id FROM students WHERE user_id = ?), ?, ?, ?, ?, ?, ?, ?, ?);

-- name: GetLatestLikertResultByType :one
SELECT r.id, r.student_id, r.questionnaire_id, r.answers, r.total_score, r.subscale_scores, r.created_at, r.weekly_evaluation_id
FROM student_questionnaire_likert_results r
JOIN questionnaires q ON r.questionnaire_id = q.id
WHERE r.student_id = (SELECT student_id FROM students WHERE user_id = ?) AND q.type = ?
ORDER BY r.created_at DESC
LIMIT 1;

-- name: GetLatestVarkResult :one
SELECT r.id, r.student_id, r.questionnaire_id, r.score_visual, r.score_auditory, r.score_reading, r.score_kinesthetic, r.answers, r.created_at
FROM student_questionnaire_vark_results r
WHERE r.student_id = (SELECT student_id FROM students WHERE user_id = ?)
ORDER BY r.created_at DESC
LIMIT 1;

-- name: GetLikertStatsByStudent :many
SELECT q.id AS questionnaire_id, q.type, q.name,
       COUNT(r.id) AS attempts,
       AVG(r.total_score) AS average_score,
       MAX(r.total_score) AS best_score,
       MIN(r.total_score) AS lowest_score
FROM questionnaires q
LEFT JOIN student_questionnaire_likert_results r
  ON q.id = r.questionnaire_id AND r.student_id = (SELECT student_id FROM students WHERE user_id = ?)
WHERE q.type IN ('MSLQ','AMS') AND q.status='active'
GROUP BY q.id, q.type, q.name
ORDER BY q.type;

-- name: GetQuestionnaireByType :one
SELECT * FROM questionnaires
WHERE type = ? AND status = 'active';

-- name: CreateQuestionnaire :execresult
INSERT INTO questionnaires (type, name, description, total_questions, status)
VALUES (?, ?, ?, ?, ?);

-- name: UpdateQuestionnaire :exec
UPDATE questionnaires
SET
  name = ?,
  description = ?,
  total_questions = ?,
  status = ?
WHERE id = ?;

-- name: DeleteQuestionnaire :exec
DELETE FROM questionnaires
WHERE id = ?;

-- name: CreateQuestion :execresult
INSERT INTO questionnaire_questions (questionnaire_id, question_number, question_text, subscale)
VALUES (?, ?, ?, ?);

-- name: UpdateQuestion :exec
UPDATE questionnaire_questions
SET
  question_number = ?,
  question_text = ?,
  subscale = ?
WHERE id = ?;

-- name: DeleteQuestion :exec
DELETE FROM questionnaire_questions
WHERE id = ?;

-- name: CreateVarkOption :execresult
INSERT INTO questionnaire_vark_options (question_id, option_text, option_letter, learning_style)
VALUES (?, ?, ?, ?);

-- name: UpdateVarkOption :exec
UPDATE questionnaire_vark_options
SET
  option_text = ?,
  option_letter = ?,
  learning_style = ?
WHERE id = ?;

-- name: DeleteVarkOption :exec
DELETE FROM questionnaire_vark_options
WHERE id = ?;

-- name: GetLikertTypeStatsByStudent :many
SELECT
  q.type AS type,
  CAST(COUNT(r.id) AS SIGNED) AS total_completed,
  CAST(AVG(r.total_score) AS DOUBLE) AS average_score,
  CAST(MAX(r.total_score) AS DOUBLE) AS best_score,
  CAST(MIN(r.total_score) AS DOUBLE) AS lowest_score,
  CAST(MAX(r.created_at) AS DATETIME) AS last_completed
FROM questionnaires q
LEFT JOIN student_questionnaire_likert_results r
  ON q.id = r.questionnaire_id
  AND r.student_id = (SELECT student_id FROM students WHERE user_id = ?)
WHERE q.type IN ('MSLQ','AMS') AND q.status = 'active'
GROUP BY q.type
ORDER BY q.type;

-- name: CountVarkResultsByStudent :one
SELECT COUNT(*) AS total
FROM student_questionnaire_vark_results r
WHERE r.student_id = (SELECT student_id FROM students WHERE user_id = ?);

-- name: GetLikertHistoryByStudent :many
SELECT
  r.id,
  r.student_id,
  r.questionnaire_id,
  r.total_score,
  r.subscale_scores,
  r.created_at,
  r.weekly_evaluation_id,
  q.name AS questionnaire_name,
  q.description AS questionnaire_description,
  q.type AS questionnaire_type
FROM student_questionnaire_likert_results r
JOIN questionnaires q ON q.id = r.questionnaire_id
WHERE r.student_id = (SELECT student_id FROM students WHERE user_id = sqlc.arg('user_id'))
  AND (sqlc.arg('type_filter_is_empty') = 1 OR q.type = sqlc.arg('type_filter'))
ORDER BY r.created_at DESC
LIMIT ? OFFSET ?;

-- name: CountLikertHistoryByStudent :one
SELECT COUNT(*) AS total
FROM student_questionnaire_likert_results r
JOIN questionnaires q ON q.id = r.questionnaire_id
WHERE r.student_id = (SELECT student_id FROM students WHERE user_id = sqlc.arg('user_id'))
  AND (sqlc.arg('type_filter_is_empty') = 1 OR q.type = sqlc.arg('type_filter'));

-- name: GetVarkHistoryByStudent :many
SELECT
  r.id,
  r.student_id,
  r.questionnaire_id,
  r.vark_type,
  r.vark_label,
  r.score_visual,
  r.score_auditory,
  r.score_reading,
  r.score_kinesthetic,
  r.created_at,
  q.name  AS questionnaire_name,
  q.description AS questionnaire_description,
  q.type  AS questionnaire_type
FROM student_questionnaire_vark_results r
JOIN questionnaires q ON q.id = r.questionnaire_id
WHERE r.student_id = (SELECT student_id FROM students WHERE user_id = ?)
ORDER BY r.created_at DESC
LIMIT ? OFFSET ?;

-- name: CountVarkHistoryByStudent :one
SELECT COUNT(*) AS total
FROM student_questionnaire_vark_results r
WHERE r.student_id = (SELECT student_id FROM students WHERE user_id = ?);
