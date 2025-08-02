package models

import "time"

// User represents a user in the database
type User struct {
	ID        int        `db:"id"`
	Username  string     `db:"username"`
	Password  string     `db:"password"`
	Name      string     `db:"name"`
	Email     string     `db:"email"`
	Role      string     `db:"role"`
	Avatar    *string    `db:"avatar"`
	CreatedAt time.Time  `db:"created_at"`
	UpdatedAt time.Time  `db:"updated_at"`
	LastLogin *time.Time `db:"last_login"`
}

// Assignment represents an assignment in the database
type Assignment struct {
	ID          int        `db:"id"`
	Title       string     `db:"title"`
	Description *string    `db:"description"`
	Subject     string     `db:"subject"`
	TeacherID   int        `db:"teacher_id"`
	Points      int        `db:"points"`
	DueDate     *time.Time `db:"due_date"`
	Status      string     `db:"status"`
	CreatedAt   time.Time  `db:"created_at"`
	UpdatedAt   time.Time  `db:"updated_at"`
}

// StudentAssignment represents a student's specific assignment record
type StudentAssignment struct {
	ID           int        `db:"id"`
	StudentID    int        `db:"student_id"`
	AssignmentID int        `db:"assignment_id"`
	Status       string     `db:"status"` // e.g., not_started, in_progress, completed
	Score        *float64   `db:"score"`
	Submission   *string    `db:"submission"`
	SubmittedAt  *time.Time `db:"submitted_at"`
	GradedAt     *time.Time `db:"graded_at"`
	CreatedAt    time.Time  `db:"created_at"`
	UpdatedAt    time.Time  `db:"updated_at"`
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
	ID             int       `db:"id"`
	Type           string    `db:"type"`
	Name           string    `db:"name"`
	Description    *string   `db:"description"`
	TotalQuestions int       `db:"total_questions"`
	Status         string    `db:"status"`
	CreatedAt      time.Time `db:"created_at"`
	UpdatedAt      time.Time `db:"updated_at"`
}

// QuestionnaireQuestion represents a question within a questionnaire
type QuestionnaireQuestion struct {
	ID              int       `db:"id"`
	QuestionnaireID int       `db:"questionnaire_id"`
	QuestionNumber  int       `db:"question_number"`
	QuestionText    string    `db:"question_text"`
	Subscale        *string   `db:"subscale"`
	ReverseScored   bool      `db:"reverse_scored"`
	CreatedAt       time.Time `db:"created_at"`
	UpdatedAt       time.Time `db:"updated_at"`
}

// QuestionnaireResult represents a student's submission for a questionnaire
type QuestionnaireResult struct {
	ID                       int       `db:"id"`
	StudentID                int       `db:"student_id"`
	QuestionnaireID          int       `db:"questionnaire_id"`
	Answers                  string    `db:"answers"` // Stored as JSON string
	TotalScore               *float64  `db:"total_score"`
	SubscaleScores           *string   `db:"subscale_scores"` // Stored as JSON string
	CompletedAt              time.Time `db:"completed_at"`
	WeekNumber               int       `db:"week_number"`
	Year                     int       `db:"year"`
	QuestionnaireName        string    `db:"questionnaire_name"`
	QuestionnaireType        string    `db:"questionnaire_type"`
	QuestionnaireDescription *string   `db:"questionnaire_description"`
}

// QuestionnaireStat represents aggregated statistics for a questionnaire type for a student
type QuestionnaireStat struct {
	Type           string     `db:"type" json:"type"`
	Name           string     `db:"name" json:"name"`
	TotalCompleted int        `db:"total_completed" json:"total_completed"`
	AverageScore   *float64   `db:"average_score" json:"average_score"`
	BestScore      *float64   `db:"best_score" json:"best_score"`
	LowestScore    *float64   `db:"lowest_score" json:"lowest_score"`
	LastCompleted  *time.Time `db:"last_completed" json:"last_completed"`
}

// StudentDashboardStats represents aggregated statistics for a student's dashboard
type StudentDashboardStats struct {
	TotalPoints            float64  `db:"total_points" json:"total_points"`
	CompletedAssignments   int      `db:"completed_assignments" json:"completed_assignments"`
	MSLQScore              *float64 `db:"mslq_score" json:"mslq_score"`
	AMSScore               *float64 `db:"ams_score" json:"ams_score"`
	VARKDominantStyle      *string  `db:"vark_dominant_style" json:"vark_dominant_style"`
	VARKLearningPreference *string  `db:"vark_learning_preference" json:"vark_learning_preference"`
}

// AdminDashboardCounts represents counts for admin dashboard
type AdminDashboardCounts struct {
	TotalUsers       int `db:"total_users" json:"total_users"`
	TotalAssignments int `db:"total_assignments" json:"total_assignments"`
	TotalMaterials   int `db:"total_materials" json:"total_materials"`
}

// TeacherDashboardCounts represents counts for teacher dashboard
type TeacherDashboardCounts struct {
	MyAssignments int `db:"my_assignments" json:"my_assignments"`
	MyMaterials   int `db:"my_materials" json:"my_materials"`
	TotalStudents int `db:"total_students" json:"total_students"`
}

// StudentEvaluationStatus represents a student's weekly evaluation status for teachers
type StudentEvaluationStatus struct {
	StudentID         int        `db:"student_id" json:"student_id"`
	StudentName       string     `db:"student_name" json:"student_name"`
	StudentEmail      string     `db:"student_email" json:"student_email"`
	CompletedThisWeek int        `db:"completed_this_week" json:"completed_this_week"`
	PendingThisWeek   int        `db:"pending_this_week" json:"pending_this_week"`
	OverdueThisWeek   int        `db:"overdue_this_week" json:"overdue_this_week"`
	MSLQScoreThisWeek *float64   `db:"mslq_score_this_week" json:"mslq_score_this_week"`
	AMSScoreThisWeek  *float64   `db:"ams_score_this_week" json:"ams_score_this_week"`
	LastEvaluation    *time.Time `db:"last_evaluation" json:"last_evaluation"`
}

// WeeklyEvaluationOverview represents aggregated weekly progress for teachers
type WeeklyEvaluationOverview struct {
	WeekNumber        int      `db:"week_number" json:"week_number"`
	Year              int      `db:"year" json:"year"`
	QuestionnaireType string   `db:"questionnaire_type" json:"questionnaire_type"`
	CompletedCount    int      `db:"completed_count" json:"completed_count"`
	PendingCount      int      `db:"pending_count" json:"pending_count"`
	OverdueCount      int      `db:"overdue_count" json:"overdue_count"`
	TotalCount        int      `db:"total_count" json:"total_count"`
	AverageScore      *float64 `db:"average_score" json:"average_score"`
}

// Material represents a study material in the database
type Material struct {
	ID          int       `db:"id"`
	Title       string    `db:"title"`
	Description *string   `db:"description"`
	Subject     string    `db:"subject"`
	TeacherID   int       `db:"teacher_id"`
	FilePath    *string   `db:"file_path"`
	FileType    *string   `db:"file_type"`
	Status      string    `db:"status"`
	CreatedAt   time.Time `db:"created_at"`
	UpdatedAt   time.Time `db:"updated_at"`
}

// NLPFeedbackTemplate represents a template for NLP feedback
type NLPFeedbackTemplate struct {
	ID            int       `db:"id"`
	TemplateName  string    `db:"template_name"`
	ScoreRangeMin int       `db:"score_range_min"`
	ScoreRangeMax int       `db:"score_range_max"`
	Component     string    `db:"component"`
	VARKStyle     string    `db:"vark_style"`
	MSLQProfile   string    `db:"mslq_profile"`
	FeedbackText  string    `db:"feedback_text"`
	IsActive      bool      `db:"is_active"`
	CreatedAt     time.Time `db:"created_at"`
	UpdatedAt     time.Time `db:"updated_at"`
}

// AssignmentStats represents aggregated statistics for a student's assignments
type AssignmentStats struct {
	TotalAssignments int     `db:"total_assignments" json:"total_assignments"`
	AvgScore         float64 `db:"avg_score" json:"avg_score"`
	BestScore        float64 `db:"best_score" json:"best_score"`
	LowestScore      float64 `db:"lowest_score" json:"lowest_score"`
	HighScores       int     `db:"high_scores" json:"high_scores"`
	LateSubmissions  int     `db:"late_submissions" json:"late_submissions"`
}

// ActivityLog represents a log entry for user activity
type ActivityLog struct {
	ID          int       `db:"id" json:"id"`
	UserID      *int      `db:"user_id" json:"user_id"`
	Action      string    `db:"action" json:"action"`
	Description *string   `db:"description" json:"description"`
	IPAddress   *string   `db:"ip_address" json:"ip_address"`
	UserAgent   *string   `db:"user_agent" json:"user_agent"`
	CreatedAt   time.Time `db:"created_at" json:"created_at"`
}

// WeeklyEvaluationProgress represents a student's weekly evaluation progress
type WeeklyEvaluationProgress struct {
	ID                int        `db:"id" json:"id"`
	StudentID         int        `db:"student_id" json:"student_id"`
	QuestionnaireID   int        `db:"questionnaire_id" json:"questionnaire_id"`
	WeekNumber        int        `db:"week_number" json:"week_number"`
	Year              int        `db:"year" json:"year"`
	QuestionnaireType string     `db:"questionnaire_type" json:"questionnaire_type"`
	QuestionnaireName string     `db:"questionnaire_name" json:"questionnaire_name"`
	Status            string     `db:"status" json:"status"`
	DueDate           *time.Time `db:"due_date" json:"due_date"`
	CompletedAt       *time.Time `db:"completed_at" json:"completed_at"`
	MSLQScore         *float64   `db:"mslq_score" json:"mslq_score"`
	AMSScore          *float64   `db:"ams_score" json:"ams_score"`
	CreatedAt         time.Time  `db:"created_at" json:"created_at"`
	UpdatedAt         time.Time  `db:"updated_at" json:"updated_at"`
}

// VARKAnswerOption represents an answer option for a VARK question
type VARKAnswerOption struct {
	ID            int    `db:"id"`
	QuestionID    int    `db:"question_id"`
	OptionLetter  string `db:"option_letter"`
	OptionText    string `db:"option_text"`
	LearningStyle string `db:"learning_style"`
}

// VARKResult represents a student's VARK assessment result
type VARKResult struct {
	ID                 int       `db:"id"`
	StudentID          int       `db:"student_id"`
	VisualScore        int       `db:"visual_score"`
	AuditoryScore      int       `db:"auditory_score"`
	ReadingScore       int       `db:"reading_score"`
	KinestheticScore   int       `db:"kinesthetic_score"`
	DominantStyle      string    `db:"dominant_style"`
	LearningPreference *string   `db:"learning_preference"`
	Answers            string    `db:"answers"` // Stored as JSON string
	CompletedAt        time.Time `db:"completed_at"`
	WeekNumber         *int      `db:"week_number"`
	Year               *int      `db:"year"`
}

// NLPAnalysisResult represents the result of an NLP analysis
type NLPAnalysisResult struct {
	ID                   int       `db:"id"`
	StudentID            int       `db:"student_id"`
	AssignmentID         *int      `db:"assignment_id"`
	QuizID               *int      `db:"quiz_id"`
	OriginalText         string    `db:"original_text"`
	CleanText            *string   `db:"clean_text"`
	WordCount            int       `db:"word_count"`
	SentenceCount        int       `db:"sentence_count"`
	TotalScore           float64   `db:"total_score"`
	GrammarScore         float64   `db:"grammar_score"`
	KeywordScore         float64   `db:"keyword_score"`
	StructureScore       float64   `db:"structure_score"`
	ReadabilityScore     float64   `db:"readability_score"`
	SentimentScore       float64   `db:"sentiment_score"`
	ComplexityScore      float64   `db:"complexity_score"`
	Feedback             *string   `db:"feedback"`              // Stored as JSON string
	PersonalizedFeedback *string   `db:"personalized_feedback"` // Stored as JSON string
	ContextType          string    `db:"context_type"`
	AnalysisVersion      string    `db:"analysis_version"`
	CreatedAt            time.Time `db:"created_at"`
	UpdatedAt            time.Time `db:"updated_at"`
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
	ID                   int       `db:"id"`
	StudentID            int       `db:"student_id"`
	Month                int       `db:"month"`
	Year                 int       `db:"year"`
	TotalAnalyses        int       `db:"total_analyses"`
	AverageScore         float64   `db:"average_score"`
	BestScore            float64   `db:"best_score"`
	ImprovementRate      float64   `db:"improvement_rate"`
	GrammarImprovement   float64   `db:"grammar_improvement"`
	KeywordImprovement   float64   `db:"keyword_improvement"`
	StructureImprovement float64   `db:"structure_improvement"`
	CreatedAt            time.Time `db:"created_at"`
	UpdatedAt            time.Time `db:"updated_at"`
}
