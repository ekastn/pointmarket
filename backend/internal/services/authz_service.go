package services

import (
	"context"
	"database/sql"
	"pointmarket/backend/internal/store/gen"
)

// AuthzService centralizes owner-based authorization checks
type AuthzService struct {
	q gen.Querier
}

func NewAuthzService(q gen.Querier) *AuthzService {
	return &AuthzService{q: q}
}

// CheckCourseOwner allows admin (handled by caller) and verifies teacher owns the course
func (a *AuthzService) CheckCourseOwner(ctx context.Context, userID, courseID int64) error {
	course, err := a.q.GetCourseByID(ctx, courseID)
	if err == sql.ErrNoRows {
		return ErrForbidden
	}
	if err != nil {
		return err
	}
	if course.OwnerID != userID {
		return ErrForbidden
	}
	return nil
}

// CheckAssignmentOwner verifies teacher owns the course associated with the assignment
func (a *AuthzService) CheckAssignmentOwner(ctx context.Context, userID, assignmentID int64) error {
	// Reuse existing query that ensures ownership
	_, err := a.q.GetAssignmentByCourseIDAndOwnerID(ctx, gen.GetAssignmentByCourseIDAndOwnerIDParams{ID: assignmentID, OwnerID: userID})
	if err == sql.ErrNoRows {
		return ErrForbidden
	}
	return err
}

// CheckQuizOwner verifies teacher owns the course associated with the quiz
func (a *AuthzService) CheckQuizOwner(ctx context.Context, userID, quizID int64) error {
	quiz, err := a.q.GetQuizByID(ctx, quizID)
	if err == sql.ErrNoRows {
		return ErrForbidden
	}
	if err != nil {
		return err
	}
	return a.CheckCourseOwner(ctx, userID, quiz.CourseID)
}

// CheckLessonOwner verifies teacher owns the course associated with the lesson
func (a *AuthzService) CheckLessonOwner(ctx context.Context, userID, lessonID int64) error {
	lesson, err := a.q.GetLessonByID(ctx, lessonID)
	if err == sql.ErrNoRows {
		return ErrForbidden
	}
	if err != nil {
		return err
	}
	return a.CheckCourseOwner(ctx, userID, lesson.CourseID)
}
