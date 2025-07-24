package store

import (
	"database/sql"
	"pointmarket/backend/internal/models"

	"github.com/jmoiron/sqlx"
)

// NLPStore handles database operations for NLP features
type NLPStore struct {
	db *sqlx.DB
}

// NewNLPStore creates a new NLPStore
func NewNLPStore(db *sqlx.DB) *NLPStore {
	return &NLPStore{db: db}
}

// GetNLPKeywords retrieves NLP keywords for a given context
func (s *NLPStore) GetNLPKeywords(context string) ([]models.NLPKeyword, error) {
	var keywords []models.NLPKeyword
	query := `SELECT id, context, keyword, weight, category FROM nlp_keywords WHERE context = ? OR context = 'assignment'`
	err := s.db.Select(&keywords, query, context)
	if err != nil {
		return nil, err
	}
	return keywords, nil
}

// GetNLPFeedbackTemplates retrieves NLP feedback templates based on criteria
func (s *NLPStore) GetNLPFeedbackTemplates(component, varkStyle, mslqProfile string, score int) ([]models.NLPFeedbackTemplate, error) {
	var templates []models.NLPFeedbackTemplate
	query := `
		SELECT id, template_name, score_range_min, score_range_max, component, vark_style, mslq_profile, feedback_text, is_active
		FROM nlp_feedback_templates
		WHERE is_active = 1
		AND (component = ? OR component = 'general')
		AND (vark_style = ? OR vark_style = 'all')
		AND (mslq_profile = ? OR mslq_profile = 'all')
		AND (? BETWEEN score_range_min AND score_range_max)
	`
	err := s.db.Select(&templates, query, component, varkStyle, mslqProfile, score)
	if err != nil {
		return nil, err
	}
	return templates, nil
}

// SaveNLPAnalysisResult saves an NLP analysis result
func (s *NLPStore) SaveNLPAnalysisResult(result *models.NLPAnalysisResult) error {
	query := `
		INSERT INTO nlp_analysis_results (
			student_id, assignment_id, quiz_id, original_text, clean_text, word_count, sentence_count,
			total_score, grammar_score, keyword_score, structure_score, readability_score, sentiment_score, complexity_score,
			feedback, personalized_feedback, context_type, analysis_version
		) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
	`
	_, err := s.db.Exec(query,
		result.StudentID,
		result.AssignmentID,
		result.QuizID,
		result.OriginalText,
		result.CleanText,
		result.WordCount,
		result.SentenceCount,
		result.TotalScore,
		result.GrammarScore,
		result.KeywordScore,
		result.StructureScore,
		result.ReadabilityScore,
		result.SentimentScore,
		result.ComplexityScore,
		result.Feedback,
		result.PersonalizedFeedback,
		result.ContextType,
		result.AnalysisVersion,
	)
	return err
}

// GetNLPProgress retrieves NLP progress for a student for a given month and year
func (s *NLPStore) GetNLPProgress(studentID, month, year int) (*models.NLPProgress, error) {
	var progress models.NLPProgress
	query := `SELECT id, student_id, month, year, total_analyses, average_score, best_score, improvement_rate, grammar_improvement, keyword_improvement, structure_improvement, created_at, updated_at FROM nlp_progress WHERE student_id = ? AND month = ? AND year = ?`
	err := s.db.Get(&progress, query, studentID, month, year)
	if err == sql.ErrNoRows {
		return nil, nil
	}
	if err != nil {
		return nil, err
	}
	return &progress, nil
}

// SaveNLPProgress saves or updates NLP progress for a student
func (s *NLPStore) SaveNLPProgress(progress *models.NLPProgress) error {
	query := `
		INSERT INTO nlp_progress (student_id, month, year, total_analyses, average_score, best_score, improvement_rate, grammar_improvement, keyword_improvement, structure_improvement)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
		ON DUPLICATE KEY UPDATE
			total_analyses = VALUES(total_analyses),
			average_score = VALUES(average_score),
			best_score = VALUES(best_score),
			improvement_rate = VALUES(improvement_rate),
			grammar_improvement = VALUES(grammar_improvement),
			keyword_improvement = VALUES(keyword_improvement),
			structure_improvement = VALUES(structure_improvement)
	`
	_, err := s.db.Exec(query,
		progress.StudentID,
		progress.Month,
		progress.Year,
		progress.TotalAnalyses,
		progress.AverageScore,
		progress.BestScore,
		progress.ImprovementRate,
		progress.GrammarImprovement,
		progress.KeywordImprovement,
		progress.StructureImprovement,
	)
	return err
}

// GetOverallNLPStats retrieves overall NLP statistics for a student
func (s *NLPStore) GetOverallNLPStats(studentID int) (*models.NLPProgress, error) {
	var stats models.NLPProgress
	query := `
		SELECT
			student_id,
			SUM(total_analyses) as total_analyses,
			AVG(average_score) as average_score,
			MAX(best_score) as best_score,
			AVG(grammar_improvement) as grammar_improvement,
			AVG(keyword_improvement) as keyword_improvement,
			AVG(structure_improvement) as structure_improvement
		FROM nlp_progress
		WHERE student_id = ?
		GROUP BY student_id
	`
	err := s.db.Get(&stats, query, studentID)
	if err == sql.ErrNoRows {
		return nil, nil
	}
	if err != nil {
		return nil, err
	}
	return &stats, nil
}
