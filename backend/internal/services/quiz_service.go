package services

import (
	"context"
	"database/sql"
	"errors"
	"fmt"
	"log"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
	"pointmarket/backend/internal/utils"
	"time"
)
import mysql "github.com/go-sql-driver/mysql"

// QuizService provides business logic for quizzes, quiz questions, and student quizzes
type QuizService struct {
	q      gen.Querier
	points *PointsService
}

// NewQuizService creates a new QuizService
func NewQuizService(q gen.Querier, ps *PointsService) *QuizService {
	return &QuizService{q: q, points: ps}
}

// CreateQuiz creates a new quiz
func (s *QuizService) CreateQuiz(ctx context.Context, req dtos.CreateQuizRequestDTO) (dtos.QuizDTO, error) {
	result, err := s.q.CreateQuiz(ctx, gen.CreateQuizParams{
		Title:           req.Title,
		Description:     utils.NullString(req.Description),
		CourseID:        req.CourseID,
		RewardPoints:    req.RewardPoints,
		DurationMinutes: utils.NullInt32(req.DurationMinutes),
		Status:          gen.NullQuizzesStatus{QuizzesStatus: gen.QuizzesStatus(req.Status), Valid: req.Status != ""},
	})
	if err != nil {
		return dtos.QuizDTO{}, err
	}

	id, err := result.LastInsertId()
	if err != nil {
		return dtos.QuizDTO{}, err
	}

	quiz, err := s.q.GetQuizByID(ctx, id)
	if err != nil {
		return dtos.QuizDTO{}, err
	}

	var quizDTO dtos.QuizDTO
	quizDTO.FromQuizModel(quiz)
	return quizDTO, nil
}

// GetQuizByID retrieves a single quiz by its ID
func (s *QuizService) GetQuizByID(ctx context.Context, id int64) (dtos.QuizDTO, error) {
	quiz, err := s.q.GetQuizByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.QuizDTO{}, nil // Quiz not found
	}
	if err != nil {
		return dtos.QuizDTO{}, err
	}

	var quizDTO dtos.QuizDTO
	quizDTO.FromQuizModel(quiz)
	return quizDTO, nil
}

// GetQuizzes retrieves a list of quizzes based on filters
func (s *QuizService) GetQuizzes(ctx context.Context, userID int64, userRole string, courseIDFilter *int64) ([]dtos.QuizDTO, error) {
	var quizzes []gen.Quiz
	var err error

	switch userRole {
	case "admin":
		if courseIDFilter != nil {
			quizzes, err = s.q.GetQuizzesByCourseID(ctx, *courseIDFilter)
		} else {
			quizzes, err = s.q.GetQuizzes(ctx)
		}
	case "guru": // Teacher
		quizzes, err = s.q.GetQuizzesByOwnerID(ctx, userID)
	case "siswa": // Student
		// Students get all general quizzes.
		// For student-specific quiz details (score, status), use GetStudentQuizzesList.
		quizzes, err = s.q.GetQuizzes(ctx)
	default:
		return nil, fmt.Errorf("unsupported user role: %s", userRole)
	}

	if err != nil {
		return nil, err
	}

	var quizDTOs []dtos.QuizDTO
	for _, quiz := range quizzes {
		var quizDTO dtos.QuizDTO
		quizDTO.FromQuizModel(quiz)
		quizDTOs = append(quizDTOs, quizDTO)
	}
	return quizDTOs, nil
}

// UpdateQuiz updates an existing quiz
func (s *QuizService) UpdateQuiz(ctx context.Context, id int64, req dtos.UpdateQuizRequestDTO) (dtos.QuizDTO, error) {
	existingQuiz, err := s.q.GetQuizByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.QuizDTO{}, nil // Quiz not found
	}
	if err != nil {
		return dtos.QuizDTO{}, err
	}

	title := existingQuiz.Title
	if req.Title != nil {
		title = *req.Title
	}

	description := existingQuiz.Description
	if req.Description != nil {
		description = sql.NullString{String: *req.Description, Valid: true}
	}

	courseID := existingQuiz.CourseID
	if req.CourseID != nil {
		courseID = *req.CourseID
	}

	rewardPoints := existingQuiz.RewardPoints
	if req.RewardPoints != nil {
		rewardPoints = *req.RewardPoints
	}

	durationMinutes := existingQuiz.DurationMinutes
	if req.DurationMinutes != nil {
		durationMinutes = sql.NullInt32{Int32: *req.DurationMinutes, Valid: true}
	}

	status := existingQuiz.Status
	if req.Status != nil {
		status = gen.NullQuizzesStatus{QuizzesStatus: gen.QuizzesStatus(*req.Status), Valid: *req.Status != ""}
	}

	err = s.q.UpdateQuiz(ctx, gen.UpdateQuizParams{
		Title:           title,
		Description:     description,
		CourseID:        courseID,
		RewardPoints:    rewardPoints,
		DurationMinutes: durationMinutes,
		Status:          status,
		ID:              id,
	})
	if err != nil {
		return dtos.QuizDTO{}, err
	}

	updatedQuiz, err := s.q.GetQuizByID(ctx, id)
	if err != nil {
		return dtos.QuizDTO{}, err
	}

	var quizDTO dtos.QuizDTO
	quizDTO.FromQuizModel(updatedQuiz)
	return quizDTO, nil
}

// DeleteQuiz deletes a quiz by its ID
func (s *QuizService) DeleteQuiz(ctx context.Context, id int64) error {
	return s.q.DeleteQuiz(ctx, id)
}

// CreateQuizQuestion creates a new quiz question
func (s *QuizService) CreateQuizQuestion(ctx context.Context, req dtos.CreateQuizQuestionRequestDTO) (dtos.QuizQuestionDTO, error) {
	// Use provided ordinal if present; otherwise 0 to let trigger assign max+1
	ordinal := int32(0)
	if req.Ordinal != nil {
		ordinal = *req.Ordinal
	}

	result, err := s.q.CreateQuizQuestion(ctx, gen.CreateQuizQuestionParams{
		QuizID:        req.QuizID,
		QuestionText:  req.QuestionText,
		QuestionType:  req.QuestionType,
		AnswerOptions: req.AnswerOptions,
		CorrectAnswer: utils.NullString(req.CorrectAnswer),
		Ordinal:       ordinal,
	})
	if err != nil {
		var me *mysql.MySQLError
		if errors.As(err, &me) && me.Number == 1062 {
			return dtos.QuizQuestionDTO{}, ErrDuplicateOrdinal
		}
		return dtos.QuizQuestionDTO{}, err
	}

	id, err := result.LastInsertId()
	if err != nil {
		return dtos.QuizQuestionDTO{}, err
	}

	question, err := s.q.GetQuizQuestionByID(ctx, id)
	if err != nil {
		return dtos.QuizQuestionDTO{}, err
	}

	var questionDTO dtos.QuizQuestionDTO
	questionDTO.FromQuizQuestionModel(question)
	return questionDTO, nil
}

// GetQuizQuestionByID retrieves a single quiz question by its ID
func (s *QuizService) GetQuizQuestionByID(ctx context.Context, id int64) (dtos.QuizQuestionDTO, error) {
	question, err := s.q.GetQuizQuestionByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.QuizQuestionDTO{}, nil // FIX: Return QuizQuestionDTO{}
	}
	if err != nil {
		return dtos.QuizQuestionDTO{}, err
	}

	var questionDTO dtos.QuizQuestionDTO
	questionDTO.FromQuizQuestionModel(question)
	return questionDTO, nil
}

// GetQuizQuestionsByQuizID retrieves all questions for a specific quiz
func (s *QuizService) GetQuizQuestionsByQuizID(ctx context.Context, quizID int64) ([]dtos.QuizQuestionDTO, error) {
	questions, err := s.q.GetQuizQuestionsByQuizID(ctx, quizID)
	if err != nil {
		return nil, err
	}

	var questionDTOs []dtos.QuizQuestionDTO
	for _, q := range questions {
		var questionDTO dtos.QuizQuestionDTO
		questionDTO.FromQuizQuestionModel(q)
		questionDTOs = append(questionDTOs, questionDTO)
	}
	return questionDTOs, nil
}

// UpdateQuizQuestion updates an existing quiz question
func (s *QuizService) UpdateQuizQuestion(ctx context.Context, id int64, req dtos.UpdateQuizQuestionRequestDTO) (dtos.QuizQuestionDTO, error) {
	existingQuestion, err := s.q.GetQuizQuestionByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.QuizQuestionDTO{}, nil // Not found
	}
	if err != nil {
		return dtos.QuizQuestionDTO{}, err
	}

	quizID := existingQuestion.QuizID
	if req.QuizID != nil {
		quizID = *req.QuizID
	}

	questionText := existingQuestion.QuestionText
	if req.QuestionText != nil {
		questionText = *req.QuestionText
	}

	questionType := existingQuestion.QuestionType
	if req.QuestionType != nil {
		questionType = *req.QuestionType
	}

	answerOptions := existingQuestion.AnswerOptions
	if req.AnswerOptions != nil {
		answerOptions = req.AnswerOptions
	}

	correctAnswer := existingQuestion.CorrectAnswer
	if req.CorrectAnswer != nil {
		correctAnswer = sql.NullString{String: *req.CorrectAnswer, Valid: true}
	}

	ordinal := existingQuestion.Ordinal
	if req.Ordinal != nil {
		ordinal = *req.Ordinal
	}

	err = s.q.UpdateQuizQuestion(ctx, gen.UpdateQuizQuestionParams{
		QuizID:        quizID,
		QuestionText:  questionText,
		QuestionType:  questionType,
		AnswerOptions: answerOptions,
		CorrectAnswer: correctAnswer,
		Ordinal:       ordinal,
		ID:            id,
	})
	if err != nil {
		var me *mysql.MySQLError
		if errors.As(err, &me) && me.Number == 1062 {
			return dtos.QuizQuestionDTO{}, ErrDuplicateOrdinal
		}
		return dtos.QuizQuestionDTO{}, err
	}

	updatedQuestion, err := s.q.GetQuizQuestionByID(ctx, id)
	if err != nil {
		return dtos.QuizQuestionDTO{}, err
	}

	var questionDTO dtos.QuizQuestionDTO
	questionDTO.FromQuizQuestionModel(updatedQuestion)
	return questionDTO, nil
}

// DeleteQuizQuestion deletes a quiz question by its ID
func (s *QuizService) DeleteQuizQuestion(ctx context.Context, id int64) error {
	return s.q.DeleteQuizQuestion(ctx, id)
}

// CreateStudentQuiz records a student starting a quiz
func (s *QuizService) CreateStudentQuiz(ctx context.Context, req dtos.CreateStudentQuizRequestDTO) (dtos.StudentQuizDTO, error) {
	// Idempotency: if exists, report conflict
	if existing, err := s.q.GetStudentQuizByIDs(ctx, gen.GetStudentQuizByIDsParams{UserID: req.StudentID, QuizID: req.QuizID}); err == nil && existing.ID != 0 {
		return dtos.StudentQuizDTO{}, ErrAlreadyStarted
	}

	result, err := s.q.CreateStudentQuiz(ctx, gen.CreateStudentQuizParams{
		UserID:    req.StudentID,
		QuizID:    req.QuizID,
		Status:    gen.NullStudentQuizzesStatus{StudentQuizzesStatus: gen.StudentQuizzesStatus(req.Status), Valid: req.Status != ""},
		StartedAt: sql.NullTime{Time: time.Now(), Valid: true},
	})
	if err != nil {
		var me *mysql.MySQLError
		if errors.As(err, &me) && me.Number == 1062 {
			return dtos.StudentQuizDTO{}, ErrAlreadyStarted
		}
		return dtos.StudentQuizDTO{}, err
	}

	id, err := result.LastInsertId()
	if err != nil {
		return dtos.StudentQuizDTO{}, err
	}

	studentQuiz, err := s.q.GetStudentQuizByID(ctx, id)
	if err != nil {
		return dtos.StudentQuizDTO{}, err
	}

	var studentQuizDTO dtos.StudentQuizDTO
	studentQuizDTO.FromStudentQuizModel(studentQuiz)
	return studentQuizDTO, nil
}

// GetStudentQuizByID retrieves a specific student's quiz record
func (s *QuizService) GetStudentQuizByID(ctx context.Context, id int64) (dtos.StudentQuizDTO, error) {
	studentQuiz, err := s.q.GetStudentQuizByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.StudentQuizDTO{}, nil
	}
	if err != nil {
		return dtos.StudentQuizDTO{}, err
	}

	var studentQuizDTO dtos.StudentQuizDTO
	studentQuizDTO.FromStudentQuizModel(studentQuiz)
	return studentQuizDTO, nil
}

// GetStudentQuizzesByStudentID retrieves all quizzes for a specific student
func (s *QuizService) GetStudentQuizzesByStudentID(ctx context.Context, studentID int64) ([]dtos.StudentQuizDTO, error) {
	studentQuizzes, err := s.q.GetStudentQuizzesByStudentID(ctx, studentID)
	if err != nil {
		return nil, err
	}

	var studentQuizDTOs []dtos.StudentQuizDTO
	for _, sq := range studentQuizzes {
		var studentQuizDTO dtos.StudentQuizDTO
		studentQuizDTO.FromGetStudentQuizzesByStudentIDRow(sq)
		studentQuizDTOs = append(studentQuizDTOs, studentQuizDTO)
	}
	return studentQuizDTOs, nil
}

// GetStudentQuizzesByQuizID retrieves all student records for a specific quiz
func (s *QuizService) GetStudentQuizzesByQuizID(ctx context.Context, quizID int64) ([]dtos.StudentQuizDTO, error) {
	studentQuizzes, err := s.q.GetStudentQuizzesByQuizID(ctx, quizID)
	if err != nil {
		return nil, err
	}

	var studentQuizDTOs []dtos.StudentQuizDTO
	for _, sq := range studentQuizzes {
		var studentQuizDTO dtos.StudentQuizDTO
		studentQuizDTO.FromGetStudentQuizzesByQuizIDRow(sq)
		studentQuizDTOs = append(studentQuizDTOs, studentQuizDTO)
	}
	return studentQuizDTOs, nil
}

// UpdateStudentQuiz updates a student's quiz record
func (s *QuizService) UpdateStudentQuiz(ctx context.Context, id int64, req dtos.UpdateStudentQuizRequestDTO) (dtos.StudentQuizDTO, error) {
	existingSQ, err := s.q.GetStudentQuizByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.StudentQuizDTO{}, nil // Not found
	}
	if err != nil {
		return dtos.StudentQuizDTO{}, err
	}

	score := existingSQ.Score
	if req.Score != nil {
		score = sql.NullInt32{Int32: *req.Score, Valid: true}
	}

	status := existingSQ.Status
	if req.Status != nil {
		status = gen.NullStudentQuizzesStatus{StudentQuizzesStatus: gen.StudentQuizzesStatus(*req.Status), Valid: *req.Status != ""}
	}

	startedAt := existingSQ.StartedAt
	if req.StartedAt != nil {
		startedAt = sql.NullTime{Time: *req.StartedAt, Valid: true}
	}

	completedAt := existingSQ.CompletedAt
	if req.CompletedAt != nil {
		completedAt = sql.NullTime{Time: *req.CompletedAt, Valid: true}
	}

	err = s.q.UpdateStudentQuiz(ctx, gen.UpdateStudentQuizParams{
		Score:       score,
		Status:      status,
		StartedAt:   startedAt,
		CompletedAt: completedAt,
		ID:          id,
	})
	if err != nil {
		return dtos.StudentQuizDTO{}, err
	}

	updatedSQ, err := s.q.GetStudentQuizByID(ctx, id)
	if err != nil {
		return dtos.StudentQuizDTO{}, err
	}

	// Award points on transition to completed
	if s.points != nil {
		prevCompleted := existingSQ.Status.Valid && string(existingSQ.Status.StudentQuizzesStatus) == "completed"
		nowCompleted := updatedSQ.Status.Valid && string(updatedSQ.Status.StudentQuizzesStatus) == "completed"
		if !prevCompleted && nowCompleted {
			qz, err2 := s.q.GetQuizByID(ctx, updatedSQ.QuizID)
			if err2 == nil && qz.RewardPoints > 0 {
				refID := id
				// Resolve userID from student_id
				if stRow, err3 := s.q.GetStudentByStudentID(ctx, updatedSQ.StudentID); err3 == nil {
					if _, err4 := s.points.Add(ctx, stRow.UserID, int64(qz.RewardPoints), "quiz_completed", "quiz", &refID); err4 != nil {
						log.Printf("points award failed: context=quiz_completed user_id=%d ref_id=%d error=%v", stRow.UserID, refID, err4)
					}
				} else if err3 != nil {
					log.Printf("points award skipped: cannot resolve user_id from student_id=%s error=%v", updatedSQ.StudentID, err3)
				}
			}
		}
	}

	var studentQuizDTO dtos.StudentQuizDTO
	studentQuizDTO.FromStudentQuizModel(updatedSQ)
	return studentQuizDTO, nil
}

// DeleteStudentQuiz deletes a student's quiz record
func (s *QuizService) DeleteStudentQuiz(ctx context.Context, id int64) error {
	return s.q.DeleteStudentQuiz(ctx, id)
}

// SubmitOwnQuiz updates the current user's quiz record by quiz ID
func (s *QuizService) SubmitOwnQuiz(ctx context.Context, userID, quizID int64, req dtos.UpdateStudentQuizRequestDTO) (dtos.StudentQuizDTO, error) {
	sq, err := s.q.GetStudentQuizByIDs(ctx, gen.GetStudentQuizByIDsParams{UserID: userID, QuizID: quizID})
	if err == sql.ErrNoRows {
		return dtos.StudentQuizDTO{}, nil
	}
	if err != nil {
		return dtos.StudentQuizDTO{}, err
	}

	status := sq.Status
	if req.Status != nil {
		status = gen.NullStudentQuizzesStatus{StudentQuizzesStatus: gen.StudentQuizzesStatus(*req.Status), Valid: *req.Status != ""}
	} else {
		status = gen.NullStudentQuizzesStatus{StudentQuizzesStatus: gen.StudentQuizzesStatus("completed"), Valid: true}
	}
	startedAt := sq.StartedAt
	if req.StartedAt != nil {
		startedAt = sql.NullTime{Time: *req.StartedAt, Valid: true}
	}
	completedAt := sq.CompletedAt
	if req.CompletedAt != nil {
		completedAt = sql.NullTime{Time: *req.CompletedAt, Valid: true}
	} else {
		completedAt = sql.NullTime{Time: time.Now(), Valid: true}
	}

	err = s.q.UpdateStudentQuiz(ctx, gen.UpdateStudentQuizParams{
		Score:       sq.Score,
		Status:      status,
		StartedAt:   startedAt,
		CompletedAt: completedAt,
		ID:          sq.ID,
	})
	if err != nil {
		return dtos.StudentQuizDTO{}, err
	}

	updated, err := s.q.GetStudentQuizByID(ctx, sq.ID)
	if err != nil {
		return dtos.StudentQuizDTO{}, err
	}

	// Award points on transition to completed (mirror existing logic)
	if s.points != nil {
		prevCompleted := sq.Status.Valid && string(sq.Status.StudentQuizzesStatus) == "completed"
		nowCompleted := updated.Status.Valid && string(updated.Status.StudentQuizzesStatus) == "completed"
		if !prevCompleted && nowCompleted {
			qz, err2 := s.q.GetQuizByID(ctx, updated.QuizID)
			if err2 == nil && qz.RewardPoints > 0 {
				refID := updated.ID
				if stRow, err3 := s.q.GetStudentByStudentID(ctx, updated.StudentID); err3 == nil {
					if _, err4 := s.points.Add(ctx, stRow.UserID, int64(qz.RewardPoints), "quiz_completed", "quiz", &refID); err4 != nil {
						log.Printf("points award failed: context=quiz_completed user_id=%d ref_id=%d error=%v", stRow.UserID, refID, err4)
					}
				} else if err3 != nil {
					log.Printf("points award skipped: cannot resolve user_id from student_id=%s error=%v", updated.StudentID, err3)
				}
			}
		}
	}

	var dto dtos.StudentQuizDTO
	dto.FromStudentQuizModel(updated)
	return dto, nil
}
