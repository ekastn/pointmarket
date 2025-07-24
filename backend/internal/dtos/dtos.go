package dtos

import "time"

// LoginRequest represents the request body for a login attempt
type LoginRequest struct {
	Username string `json:"username" binding:"required"`
	Password string `json:"password" binding:"required"`
	Role     string `json:"role" binding:"required"`
}

// LoginResponse represents the response body for a successful login
type LoginResponse struct {
	Token string `json:"token"`
}

// AssignmentRequest represents the request body for creating/updating an assignment
type AssignmentRequest struct {
	Title       string    `json:"title" binding:"required"`
	Description *string   `json:"description"`
	Subject     string    `json:"subject" binding:"required"`
	TeacherID   int       `json:"teacher_id" binding:"required"`
	Points      int       `json:"points"`
	DueDate     *time.Time `json:"due_date"`
	Status      string    `json:"status"`
}

// QuizRequest represents the request body for creating/updating a quiz
type QuizRequest struct {
	Title       string    `json:"title" binding:"required"`
	Description *string   `json:"description"`
	Subject     string    `json:"subject" binding:"required"`
	TeacherID   int       `json:"teacher_id" binding:"required"`
	Points      int       `json:"points"`
	Duration    *int      `json:"duration"`
	Status      string    `json:"status"`
}

// SubmitQuestionnaireRequest represents the request body for submitting a questionnaire
type SubmitQuestionnaireRequest struct {
	QuestionnaireID int            `json:"questionnaire_id" binding:"required"`
	Answers         map[string]int `json:"answers" binding:"required"`
	WeekNumber      int            `json:"week_number"`
	Year            int            `json:"year"`
}

// VARKQuestion represents a VARK question with its options
type VARKQuestion struct {
	ID           int    `json:"id"`
	QuestionNumber int    `json:"question_number"`
	QuestionText string `json:"question_text"`
	Options      []VARKAnswerOption `json:"options"`
}

// VARKAnswerOption represents an answer option for a VARK question
type VARKAnswerOption struct {
	ID          int    `json:"id"`
	OptionLetter string `json:"option_letter"`
	OptionText  string `json:"option_text"`
	LearningStyle string `json:"learning_style"`
}

// SubmitVARKRequest represents the request body for submitting a VARK assessment
type SubmitVARKRequest struct {
	Answers map[int]string `json:"answers" binding:"required"` // Map of question ID to selected option letter
}

// AnalyzeTextRequest represents the request body for NLP text analysis
type AnalyzeTextRequest struct {
	Text         string  `json:"text" binding:"required"`
	ContextType  string  `json:"context_type"`
	AssignmentID *int    `json:"assignment_id"`
	QuizID       *int    `json:"quiz_id"`
}

// NLPAnalysisResponse represents the response body for NLP text analysis
type NLPAnalysisResponse struct {
	TotalScore       float64 `json:"total_score"`
	GrammarScore     float64 `json:"grammar_score"`
	KeywordScore     float64 `json:"keyword_score"`
	StructureScore   float64 `json:"structure_score"`
	ReadabilityScore float64 `json:"readability_score"`
	SentimentScore   float64 `json:"sentiment_score"`
	ComplexityScore  float64 `json:"complexity_score"`
	Feedback         []string `json:"feedback"`
	PersonalizedFeedback []string `json:"personalized_feedback"`
}

// NLPStatsResponse represents the response body for NLP statistics
type NLPStatsResponse struct {
	TotalAnalyses      int     `json:"total_analyses"`
	AverageScore       float64 `json:"average_score"`
	BestScore          float64 `json:"best_score"`
	GrammarImprovement float64 `json:"grammar_improvement"`
	KeywordImprovement float64 `json:"keyword_improvement"`
	StructureImprovement float64 `json:"structure_improvement"`
}