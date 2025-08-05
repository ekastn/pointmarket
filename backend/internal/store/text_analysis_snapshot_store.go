package store

import (
	"database/sql"
	"pointmarket/backend/internal/models"

	"github.com/jmoiron/sqlx"
)

// TextAnalysisSnapshotStore handles database operations for text analysis snapshots
type TextAnalysisSnapshotStore struct {
	db *sqlx.DB
}

// NewTextAnalysisSnapshotStore creates a new TextAnalysisSnapshotStore
func NewTextAnalysisSnapshotStore(db *sqlx.DB) *TextAnalysisSnapshotStore {
	return &TextAnalysisSnapshotStore{db: db}
}

// CreateTextAnalysisSnapshot saves a comprehensive text analysis snapshot
func (s *TextAnalysisSnapshotStore) CreateTextAnalysisSnapshot(snapshot *models.TextAnalysisSnapshot) error {
	query := `
		INSERT INTO text_analysis_snapshots (
			student_id, original_text, word_count, sentence_count, total_score, grammar_score,
			structure_score, readability_score, sentiment_score, complexity_score,
			learning_preference_type, learning_preference_label, learning_preference_combined_vark,
			created_at, updated_at
		) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
	`
	_, err := s.db.Exec(query,
		snapshot.StudentID,
		snapshot.OriginalText,
		snapshot.WordCount,
		snapshot.SentenceCount,
		snapshot.TotalScore,
		snapshot.GrammarScore,
		snapshot.StructureScore,
		snapshot.ReadabilityScore,
		snapshot.SentimentScore,
		snapshot.ComplexityScore,
		snapshot.LearningPreferenceType,
		snapshot.LearningPreferenceLabel,
		snapshot.LearningPreferenceCombinedVARK,
		snapshot.CreatedAt,
		snapshot.UpdatedAt,
	)
	return err
}

// GetLatestTextAnalysisSnapshot retrieves the most recent text analysis snapshot for a student
func (s *TextAnalysisSnapshotStore) GetLatestTextAnalysisSnapshot(studentID int) (*models.TextAnalysisSnapshot, error) {
	var snapshot models.TextAnalysisSnapshot
	query := `
		SELECT *
		FROM text_analysis_snapshots
		WHERE student_id = ?
		ORDER BY created_at DESC
		LIMIT 1
	`
	err := s.db.Get(&snapshot, query, studentID)
	if err == sql.ErrNoRows {
		return nil, nil
	}
	if err != nil {
		return nil, err
	}
	return &snapshot, nil
}
