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
