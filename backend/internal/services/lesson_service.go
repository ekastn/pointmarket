package services

import (
	"context"
	"database/sql"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
)

// LessonService provides business logic for lessons
type LessonService struct {
	q gen.Querier
}

func NewLessonService(q gen.Querier) *LessonService {
	return &LessonService{q: q}
}

func (s *LessonService) CreateLesson(ctx context.Context, req dtos.CreateLessonRequestDTO) (dtos.LessonDTO, error) {
	res, err := s.q.CreateLesson(ctx, gen.CreateLessonParams{
		CourseID: req.CourseID,
		Title:    req.Title,
		Ordinal:  req.Ordinal,
		Content:  req.Content,
	})
	if err != nil {
		return dtos.LessonDTO{}, err
	}
	id, err := res.LastInsertId()
	if err != nil {
		return dtos.LessonDTO{}, err
	}
	m, err := s.q.GetLessonByID(ctx, id)
	if err != nil {
		return dtos.LessonDTO{}, err
	}
	var dto dtos.LessonDTO
	dto.FromLessonModel(m)
	return dto, nil
}

func (s *LessonService) GetLessonByID(ctx context.Context, id int64) (dtos.LessonDTO, error) {
	m, err := s.q.GetLessonByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.LessonDTO{}, nil
	}
	if err != nil {
		return dtos.LessonDTO{}, err
	}
	var dto dtos.LessonDTO
	dto.FromLessonModel(m)
	return dto, nil
}

func (s *LessonService) GetLessonsByCourse(ctx context.Context, courseID int64, page, limit int) ([]dtos.LessonDTO, int64, error) {
	offset := (page - 1) * limit
	rows, err := s.q.GetLessonsByCourseID(ctx, gen.GetLessonsByCourseIDParams{
		CourseID: courseID,
		Limit:    int32(limit),
		Offset:   int32(offset),
	})
	if err != nil {
		return nil, 0, err
	}
	total, err := s.q.CountLessonsByCourseID(ctx, courseID)
	if err != nil {
		return nil, 0, err
	}
	dtosOut := make([]dtos.LessonDTO, 0, len(rows))
	for _, m := range rows {
		var dto dtos.LessonDTO
		dto.FromLessonModel(m)
		dtosOut = append(dtosOut, dto)
	}
	return dtosOut, total, nil
}

func (s *LessonService) UpdateLesson(ctx context.Context, id int64, req dtos.UpdateLessonRequestDTO) (dtos.LessonDTO, error) {
	// fetch existing to apply partials
	existing, err := s.q.GetLessonByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.LessonDTO{}, nil
	}
	if err != nil {
		return dtos.LessonDTO{}, err
	}
	title := existing.Title
	if req.Title != nil {
		title = *req.Title
	}
	ordinal := existing.Ordinal
	if req.Ordinal != nil {
		ordinal = *req.Ordinal
	}
	content := existing.Content
	if req.Content != nil {
		content = req.Content
	}
	if err := s.q.UpdateLesson(ctx, gen.UpdateLessonParams{
		Title:   title,
		Ordinal: ordinal,
		Content: content,
		ID:      id,
	}); err != nil {
		return dtos.LessonDTO{}, err
	}
	updated, err := s.q.GetLessonByID(ctx, id)
	if err != nil {
		return dtos.LessonDTO{}, err
	}
	var dto dtos.LessonDTO
	dto.FromLessonModel(updated)
	return dto, nil
}

func (s *LessonService) DeleteLesson(ctx context.Context, id int64) error {
	return s.q.DeleteLesson(ctx, id)
}
