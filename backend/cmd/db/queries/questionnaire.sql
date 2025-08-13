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
VALUES (?, ?, ?, ?, ?, ?);

-- name: CreateVarkResult :exec
INSERT INTO student_questionnaire_vark_results
  (student_id, questionnaire_id, vark_type, vark_label, score_visual, score_auditory, score_reading, score_kinesthetic, answers)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);

-- name: GetLatestLikertResultByType :one
SELECT r.id, r.student_id, r.questionnaire_id, r.answers, r.total_score, r.subscale_scores, r.created_at, r.weekly_evaluation_id
FROM student_questionnaire_likert_results r
JOIN questionnaires q ON r.questionnaire_id = q.id
WHERE r.student_id = ? AND q.type = ?
ORDER BY r.created_at DESC
LIMIT 1;

-- name: GetLatestVarkResult :one
SELECT r.id, r.student_id, r.questionnaire_id, r.score_visual, r.score_auditory, r.score_reading, r.score_kinesthetic, r.answers, r.created_at
FROM student_questionnaire_vark_results r
WHERE r.student_id = ?
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
  ON q.id = r.questionnaire_id AND r.student_id = ?
WHERE q.type IN ('MSLQ','AMS') AND q.status='active'
GROUP BY q.id, q.type, q.name
ORDER BY q.type;

-- name: GetQuestionnaireByType :one
SELECT id, name FROM questionnaires
WHERE type = ? AND status = 'active';
