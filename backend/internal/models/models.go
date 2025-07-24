package models

import "time"

// User represents a user in the database
type User struct {
	ID        int       `db:"id"`
	Username  string    `db:"username"`
	Password  string    `db:"password"`
	Name      string    `db:"name"`
	Email     string    `db:"email"`
	Role      string    `db:"role"`
	Avatar    *string   `db:"avatar"`
	CreatedAt time.Time `db:"created_at"`
	UpdatedAt time.Time `db:"updated_at"`
	LastLogin *time.Time `db:"last_login"`
}

// Assignment represents an assignment in the database
type Assignment struct {
	ID          int       `db:"id"`
	Title       string    `db:"title"`
	Description *string   `db:"description"`
	Subject     string    `db:"subject"`
	TeacherID   int       `db:"teacher_id"`
	Points      int       `db:"points"`
	DueDate     *time.Time `db:"due_date"`
	Status      string    `db:"status"`
	CreatedAt   time.Time `db:"created_at"`
	UpdatedAt   time.Time `db:"updated_at"`
}

// Quiz represents a quiz in the database
type Quiz struct {
	ID          int       `db:"id"`
	Title       string    `db:"title"`
	Description *string   `db:"description"`
	Subject     string    `db:"subject"`
	TeacherID   int       `db:"teacher_id"`
	Points      int       `db:"points"`
	Duration    *int      `db:"duration"`
	Status      string    `db:"status"`
	CreatedAt   time.Time `db:"created_at"`
	UpdatedAt   time.Time `db:"updated_at"`
}

// Questionnaire represents a questionnaire (MSLQ, AMS, VARK)
type Questionnaire struct {
	ID           int       `db:"id"`
	Type         string    `db:"type"`
	Name         string    `db:"name"`
	Description  *string   `db:"description"`
	TotalQuestions int       `db:"total_questions"`
	Status       string    `db:"status"`
	CreatedAt    time.Time `db:"created_at"`
}

// QuestionnaireQuestion represents a question within a questionnaire
type QuestionnaireQuestion struct {
	ID           int       `db:"id"`
	QuestionnaireID int       `db:"questionnaire_id"`
	QuestionNumber int       `db:"question_number"`
	QuestionText string    `db:"question_text"`
	Subscale     *string   `db:"subscale"`
	ReverseScored bool      `db:"reverse_scored"`
	CreatedAt    time.Time `db:"created_at"`
}

// QuestionnaireResult represents a student's submission for a questionnaire
type QuestionnaireResult struct {
	ID            int       `db:"id"`
	StudentID     int       `db:"student_id"`
	QuestionnaireID int       `db:"questionnaire_id"`
	Answers       string    `db:"answers"` // Stored as JSON string
	TotalScore    *float64  `db:"total_score"`
	SubscaleScores *string   `db:"subscale_scores"` // Stored as JSON string
	CompletedAt   time.Time `db:"completed_at"`
	WeekNumber    int       `db:"week_number"`
	Year          int       `db:"year"`
}

// VARKAnswerOption represents an answer option for a VARK question
type VARKAnswerOption struct {
	ID          int    `db:"id"`
	QuestionID  int    `db:"question_id"`
	OptionLetter string `db:"option_letter"`
	OptionText  string `db:"option_text"`
	LearningStyle string `db:"learning_style"`
}

// VARKResult represents a student's VARK assessment result
type VARKResult struct {
	ID            int       `db:"id"`
	StudentID     int       `db:"student_id"`
	VisualScore   int       `db:"visual_score"`
	AuditoryScore int       `db:"auditory_score"`
	ReadingScore  int       `db:"reading_score"`
	KinestheticScore int       `db:"kinesthetic_score"`
	DominantStyle string    `db:"dominant_style"`
	LearningPreference *string   `db:"learning_preference"`
	Answers       string    `db:"answers"` // Stored as JSON string
	CompletedAt   time.Time `db:"completed_at"`
	WeekNumber    *int      `db:"week_number"`
	Year          *int      `db:"year"`
}

// NLPAnalysisResult represents the result of an NLP analysis
type NLPAnalysisResult struct {
	ID               int       `db:"id"`
	StudentID        int       `db:"student_id"`
	AssignmentID     *int      `db:"assignment_id"`
	QuizID           *int      `db:"quiz_id"`
	OriginalText     string    `db:"original_text"`
	CleanText        *string   `db:"clean_text"`
	WordCount        int       `db:"word_count"`
	SentenceCount    int       `db:"sentence_count"`
	TotalScore       float64   `db:"total_score"`
	GrammarScore     float64   `db:"grammar_score"`
	KeywordScore     float64   `db:"keyword_score"`
	StructureScore   float64   `db:"structure_score"`
	ReadabilityScore float64   `db:"readability_score"`
	SentimentScore   float64   `db:"sentiment_score"`
	ComplexityScore  float64   `db:"complexity_score"`
	Feedback         *string   `db:"feedback"` // Stored as JSON string
	PersonalizedFeedback *string   `db:"personalized_feedback"` // Stored as JSON string
	ContextType      string    `db:"context_type"`
	AnalysisVersion  string    `db:"analysis_version"`
	CreatedAt        time.Time `db:"created_at"`
	UpdatedAt        time.Time `db:"updated_at"`
}

// NLPKeyword represents a keyword for NLP analysis
type NLPKeyword struct {
	ID        int       `db:"id"`
	Context   string    `db:"context"`
	Keyword   string    `db:"keyword"`
	Weight    float64   `db:"weight"`
	Category  string    `db:"category"`
	CreatedAt time.Time `db:"created_at"`
	UpdatedAt time.Time `db:"updated_at"`
}

// NLPProgress represents a student's NLP progress over time
type NLPProgress struct {
	ID                 int       `db:"id"`
	StudentID          int       `db:"student_id"`
	Month              int       `db:"month"`
	Year               int       `db:"year"`
	TotalAnalyses      int       `db:"total_analyses"`
	AverageScore       float64   `db:"average_score"`
	BestScore          float64   `db:"best_score"`
	ImprovementRate    float64   `db:"improvement_rate"`
	GrammarImprovement float64   `db:"grammar_improvement"`
	KeywordImprovement float64   `db:"keyword_improvement"`
	StructureImprovement float64   `db:"structure_improvement"`
	CreatedAt          time.Time `db:"created_at"`
	UpdatedAt          time.Time `db:"updated_at"`
}

// NLPFeedbackTemplate represents a template for NLP feedback
type NLPFeedbackTemplate struct {
	ID          int       `db:"id"`
	TemplateName string    `db:"template_name"`
	ScoreRangeMin int       `db:"score_range_min"`
	ScoreRangeMax int       `db:"score_range_max"`
	Component   string    `db:"component"`
	VARKStyle   string    `db:"vark_style"`
	MSLQProfile string    `db:"mslq_profile"`
	FeedbackText string    `db:"feedback_text"`
	IsActive    bool      `db:"is_active"`
	CreatedAt   time.Time `db:"created_at"`
	UpdatedAt   time.Time `db:"updated_at"`
}
