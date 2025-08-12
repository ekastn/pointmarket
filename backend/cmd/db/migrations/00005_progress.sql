-- +goose Up
-- +goose StatementBegin
CREATE TABLE weekly_evaluations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
	student_id BIGINT NOT NULL,
	questionnaire_id INT NOT NULL,
	status ENUM('pending', 'completed', 'overdue') NOT NULL DEFAULT 'pending',
    due_date DATETIME NOT NULL,
    completed_at DATETIME,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (questionnaire_id) REFERENCES questionnaires(id) ON DELETE CASCADE
);

CREATE TABLE text_analysis_snapshots (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    student_id BIGINT NOT NULL,
    original_text TEXT NOT NULL,
	average_word_length DOUBLE NOT NULL,
	reading_time INT NOT NULL,
	count_words INT NOT NULL,
	count_sentences INT NOT NULL,
	score_total DOUBLE NOT NULL,
	score_grammar DOUBLE NOT NULL,
    score_structure DOUBLE NOT NULL,
    score_readability DOUBLE NOT NULL,
    score_sentiment DOUBLE NOT NULL,
    score_complexity DOUBLE NOT NULL,
    learning_preference_type VARCHAR(50) NOT NULL,
    learning_preference_label VARCHAR(100) NOT NULL,
    learning_preference_combined_vark JSON,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);
-- +goose StatementEnd

-- +goose Down
-- +goose StatementBegin
drop table if exists text_analysis_snapshots;
DROP TABLE IF EXISTS weekly_evaluations;
-- +goose StatementEnd
