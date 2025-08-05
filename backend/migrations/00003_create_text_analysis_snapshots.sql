-- +goose Up
CREATE TABLE text_analysis_snapshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    original_text TEXT NOT NULL,
    word_count INT NOT NULL,
    sentence_count INT NOT NULL,
    total_score DOUBLE NOT NULL,
    grammar_score DOUBLE NOT NULL,
    structure_score DOUBLE NOT NULL,
    readability_score DOUBLE NOT NULL,
    sentiment_score DOUBLE NOT NULL,
    complexity_score DOUBLE NOT NULL,
    learning_preference_type VARCHAR(50) NOT NULL,
    learning_preference_label VARCHAR(100) NOT NULL,
    learning_preference_combined_vark JSON,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- +goose Down
DROP TABLE text_analysis_snapshots;
