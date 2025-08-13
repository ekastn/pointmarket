-- name: CreateTextAnalysisSnapshot :exec
INSERT INTO text_analysis_snapshots (
    student_id,
    original_text,
	average_word_length,
	reading_time,
    count_words,
    count_sentences,
    score_total,
    score_grammar,
    score_structure,
    score_readability,
    score_sentiment,
    score_complexity,
    learning_preference_type,
    learning_preference_label,
    learning_preference_combined_vark
) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );

-- name: UpdateWeeklyEvaluationStatus :exec
UPDATE weekly_evaluations
SET status = 'completed', completed_at = NOW()
WHERE id = ? AND student_id = ?;

-- name: GetWeeklyEvaluationsByStudentID :many
SELECT * FROM weekly_evaluations
WHERE student_id = ? AND due_date >= DATE_SUB(CURDATE(), INTERVAL ? WEEK) AND due_date <= CURDATE()
ORDER BY due_date DESC;

-- name: GetWeeklyEvaluationsForTeacherDashboard :many
SELECT
    we.id,
    we.status,
    we.due_date,
    we.completed_at,
    u.id as student_id,
    u.display_name as student_name
FROM weekly_evaluations we
JOIN users u ON we.student_id = u.id
WHERE we.due_date >= DATE_SUB(CURDATE(), INTERVAL ? WEEK) AND we.due_date <= CURDATE();

-- name: CreateWeeklyEvaluation :exec
INSERT INTO weekly_evaluations
  (student_id, questionnaire_id, status, due_date)
VALUES (?, ?, ?, ?);

-- name: MarkOverdueWeeklyEvaluations :exec
UPDATE weekly_evaluations
SET status = 'overdue'
WHERE status = 'pending' AND due_date < NOW();

-- name: GetWeeklyEvaluationByStudentAndQuestionnaireAndDueDate :one
SELECT id FROM weekly_evaluations
WHERE student_id = ? AND questionnaire_id = ? AND due_date = ?;
