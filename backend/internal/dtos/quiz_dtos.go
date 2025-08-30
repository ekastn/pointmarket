package dtos

import (
	"encoding/json"
	"time"

	"pointmarket/backend/internal/store/gen"
)

// --- Quiz DTOs ---

// QuizDTO represents a quiz for API responses
type QuizDTO struct {
	ID              int64     `json:"id"`
	Title           string    `json:"title"`
	Description     *string   `json:"description"`
	CourseID        int64     `json:"course_id"`
	RewardPoints    int32     `json:"reward_points"`
	DurationMinutes *int32    `json:"duration_minutes"`
	Status          string    `json:"status"`
	CreatedAt       time.Time `json:"created_at"`
	UpdatedAt       time.Time `json:"updated_at"`
}

// FromQuizModel converts a gen.Quiz model to a QuizDTO
func (dto *QuizDTO) FromQuizModel(m gen.Quiz) {
	dto.ID = m.ID
	dto.Title = m.Title
	if m.Description.Valid {
		dto.Description = &m.Description.String
	} else {
		dto.Description = nil
	}
	dto.CourseID = m.CourseID
	dto.RewardPoints = m.RewardPoints
	if m.DurationMinutes.Valid {
		dto.DurationMinutes = &m.DurationMinutes.Int32
	} else {
		dto.DurationMinutes = nil
	}
	if m.Status.Valid {
		dto.Status = string(m.Status.QuizzesStatus)
	} else {
		dto.Status = ""
	}
	dto.CreatedAt = m.CreatedAt
	dto.UpdatedAt = m.UpdatedAt
}

// CreateQuizRequestDTO for creating a new quiz
type CreateQuizRequestDTO struct {
	Title           string  `json:"title" binding:"required"`
	Description     *string `json:"description"`
	CourseID        int64   `json:"course_id" binding:"required"`
	RewardPoints    int32   `json:"reward_points" binding:"required"`
	DurationMinutes *int32  `json:"duration_minutes"`
	Status          string  `json:"status"`
}

// UpdateQuizRequestDTO for updating an existing quiz
type UpdateQuizRequestDTO struct {
	Title           *string `json:"title"`
	Description     *string `json:"description"`
	CourseID        *int64  `json:"course_id"`
	RewardPoints    *int32  `json:"reward_points"`
	DurationMinutes *int32  `json:"duration_minutes"`
	Status          *string `json:"status"`
}

// ListQuizzesResponseDTO contains a list of QuizDTOs
type ListQuizzesResponseDTO struct {
	Quizzes []QuizDTO `json:"quizzes"`
	Total   int       `json:"total"`
}

// --- Quiz Question DTOs ---

// QuizQuestionDTO represents a quiz question for API responses
type QuizQuestionDTO struct {
	ID            int64           `json:"id"`
	QuizID        int64           `json:"quiz_id"`
	QuestionText  string          `json:"question_text"`
	QuestionType  string          `json:"question_type"`
	AnswerOptions json.RawMessage `json:"answer_options"`
	CorrectAnswer *string         `json:"correct_answer"`
}

// FromQuizQuestionModel converts a gen.QuizQuestion model to a QuizQuestionDTO
func (dto *QuizQuestionDTO) FromQuizQuestionModel(m gen.QuizQuestion) {
	dto.ID = m.ID
	dto.QuizID = m.QuizID
	dto.QuestionText = m.QuestionText
	dto.QuestionType = m.QuestionType
	dto.AnswerOptions = m.AnswerOptions
	if m.CorrectAnswer.Valid {
		dto.CorrectAnswer = &m.CorrectAnswer.String
	} else {
		dto.CorrectAnswer = nil
	}
}

// CreateQuizQuestionRequestDTO for creating a new quiz question
type CreateQuizQuestionRequestDTO struct {
	QuizID        int64           `json:"quiz_id" binding:"required"`
	QuestionText  string          `json:"question_text" binding:"required"`
	QuestionType  string          `json:"question_type" binding:"required"`
	AnswerOptions json.RawMessage `json:"answer_options"`
	CorrectAnswer *string         `json:"correct_answer"`
}

// UpdateQuizQuestionRequestDTO for updating an existing quiz question
type UpdateQuizQuestionRequestDTO struct {
	QuizID        *int64          `json:"quiz_id"`
	QuestionText  *string         `json:"question_text"`
	QuestionType  *string         `json:"question_type"`
	AnswerOptions json.RawMessage `json:"answer_options"`
	CorrectAnswer *string         `json:"correct_answer"`
}

// ListQuizQuestionsResponseDTO contains a list of QuizQuestionDTOs
type ListQuizQuestionsResponseDTO struct {
	QuizQuestions []QuizQuestionDTO `json:"quiz_questions"`
	Total         int               `json:"total"`
}

// --- Student Quiz DTOs ---

// StudentQuizDTO represents a student's quiz record for API responses
type StudentQuizDTO struct {
	ID          int64      `json:"id"`
	StudentID   string     `json:"student_id"`
	QuizID      int64      `json:"quiz_id"`
	Score       *int32     `json:"score"`
	Status      string     `json:"status"`
	StartedAt   *time.Time `json:"started_at"`
	CompletedAt *time.Time `json:"completed_at"`
	CreatedAt   time.Time  `json:"created_at"`
	UpdatedAt   time.Time  `json:"updated_at"`

	// Joined quiz details
	QuizTitle           string  `json:"quiz_title"`
	QuizDescription     *string `json:"quiz_description"`
	QuizCourseID        int64   `json:"quiz_course_id"`
	QuizRewardPoints    int32   `json:"quiz_reward_points"`
	QuizDurationMinutes *int32  `json:"quiz_duration_minutes"`

	// Joined student details (for GetStudentQuizzesByQuizID)
	StudentName  *string `json:"student_name"`
	StudentEmail *string `json:"student_email"`
}

// FromStudentQuizModel converts a gen.StudentQuiz model to a StudentQuizDTO
func (dto *StudentQuizDTO) FromStudentQuizModel(m gen.StudentQuiz) {
	dto.ID = m.ID
	dto.StudentID = m.StudentID
	dto.QuizID = m.QuizID
	if m.Score.Valid {
		dto.Score = &m.Score.Int32
	} else {
		dto.Score = nil
	}
	if m.Status.Valid {
		dto.Status = string(m.Status.StudentQuizzesStatus)
	} else {
		dto.Status = ""
	}
	if m.StartedAt.Valid {
		dto.StartedAt = &m.StartedAt.Time
	} else {
		dto.StartedAt = nil
	}
	if m.CompletedAt.Valid {
		dto.CompletedAt = &m.CompletedAt.Time
	} else {
		dto.CompletedAt = nil
	}
	dto.CreatedAt = m.CreatedAt.Time
	dto.UpdatedAt = m.UpdatedAt.Time
}

// FromGetStudentQuizzesByStudentIDRow converts a gen.GetStudentQuizzesByStudentIDRow to a StudentQuizDTO
func (dto *StudentQuizDTO) FromGetStudentQuizzesByStudentIDRow(m gen.GetStudentQuizzesByStudentIDRow) {
	dto.ID = m.ID
	dto.StudentID = m.StudentID
	dto.QuizID = m.QuizID
	if m.Score.Valid {
		dto.Score = &m.Score.Int32
	} else {
		dto.Score = nil
	}
	if m.Status.Valid {
		dto.Status = string(m.Status.StudentQuizzesStatus)
	} else {
		dto.Status = ""
	}
	if m.StartedAt.Valid {
		dto.StartedAt = &m.StartedAt.Time
	} else {
		dto.StartedAt = nil
	}
	if m.CompletedAt.Valid {
		dto.CompletedAt = &m.CompletedAt.Time
	} else {
		dto.CompletedAt = nil
	}
	dto.CreatedAt = m.CreatedAt.Time
	dto.UpdatedAt = m.UpdatedAt.Time

	// Joined quiz details
	dto.QuizTitle = m.QuizTitle
	if m.QuizDescription.Valid {
		dto.QuizDescription = &m.QuizDescription.String
	} else {
		dto.QuizDescription = nil
	}
	dto.QuizCourseID = m.QuizCourseID
	dto.QuizRewardPoints = m.QuizRewardPoints
	if m.QuizDurationMinutes.Valid {
		dto.QuizDurationMinutes = &m.QuizDurationMinutes.Int32
	} else {
		dto.QuizDurationMinutes = nil
	}
}

// FromGetStudentQuizzesByQuizIDRow converts a gen.GetStudentQuizzesByQuizIDRow to a StudentQuizDTO
func (dto *StudentQuizDTO) FromGetStudentQuizzesByQuizIDRow(m gen.GetStudentQuizzesByQuizIDRow) {
	dto.ID = m.ID
	dto.StudentID = m.StudentID
	dto.QuizID = m.QuizID
	if m.Score.Valid {
		dto.Score = &m.Score.Int32
	} else {
		dto.Score = nil
	}
	if m.Status.Valid {
		dto.Status = string(m.Status.StudentQuizzesStatus)
	} else {
		dto.Status = ""
	}
	if m.StartedAt.Valid {
		dto.StartedAt = &m.StartedAt.Time
	} else {
		dto.StartedAt = nil
	}
	if m.CompletedAt.Valid {
		dto.CompletedAt = &m.CompletedAt.Time
	} else {
		dto.CompletedAt = nil
	}
	dto.CreatedAt = m.CreatedAt.Time
	dto.UpdatedAt = m.UpdatedAt.Time

	// Joined student details
	dto.StudentName = &m.StudentName   // Directly assign address of string
	dto.StudentEmail = &m.StudentEmail // Directly assign address of string
}

// CreateStudentQuizRequestDTO for creating a new student quiz record (e.g., when starting)
type CreateStudentQuizRequestDTO struct {
	StudentID int64  `json:"student_id" binding:"required"`
	QuizID    int64  `json:"quiz_id" binding:"required"`
	Status    string `json:"status"` // e.g., "not_started", "in_progress"
}

// UpdateStudentQuizRequestDTO for updating a student quiz record (e.g., score, status)
type UpdateStudentQuizRequestDTO struct {
	Score       *int32     `json:"score"`
	Status      *string    `json:"status"` // e.g., "in_progress", "completed"
	StartedAt   *time.Time `json:"started_at"`
	CompletedAt *time.Time `json:"completed_at"`
}

// ListStudentQuizzesResponseDTO contains a list of StudentQuizDTOs
type ListStudentQuizzesResponseDTO struct {
	StudentQuizzes []StudentQuizDTO `json:"student_quizzes"`
	Total          int              `json:"total"`
}
