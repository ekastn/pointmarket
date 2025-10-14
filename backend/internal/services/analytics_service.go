package services

import (
	"context"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
)

// AnalyticsService provides aggregated insights for teacher dashboards
type AnalyticsService struct {
	q gen.Querier
}

func NewAnalyticsService(q gen.Querier) *AnalyticsService {
	return &AnalyticsService{q: q}
}

func (s *AnalyticsService) GetTeacherCourseInsights(ctx context.Context, teacherID int64, limit int32) ([]dtos.CourseInsightsDTO, error) {
	if limit <= 0 {
		limit = 10
	}
	rows, err := s.q.GetTeacherCourseInsights(ctx, gen.GetTeacherCourseInsightsParams{teacherID, limit})
	if err != nil {
		return nil, err
	}
	out := make([]dtos.CourseInsightsDTO, 0, len(rows))
	for _, r := range rows {
		var dto dtos.CourseInsightsDTO
		dto.CourseID = r.CourseID
		dto.CourseTitle = r.CourseTitle
		dto.AvgMSLQ = r.AvgMslq
		dto.AvgAMS = r.AvgAms
		dto.VarkAvg.Visual = r.AvgVisual
		dto.VarkAvg.Auditory = r.AvgAuditory
		dto.VarkAvg.Reading = r.AvgReading
		dto.VarkAvg.Kinesthetic = r.AvgKinesthetic
		out = append(out, dto)
	}
	return out, nil
}
