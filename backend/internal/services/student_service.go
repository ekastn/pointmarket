package services

import (
	"context"
	"database/sql"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
	"time"
)

type StudentService struct {
	q gen.Querier
}

func NewStudentService(q gen.Querier) *StudentService {
	return &StudentService{q: q}
}

func (s *StudentService) toDTO(row gen.GetStudentByUserIDRow) dtos.StudentDTO {
	var cohort *int32
	if row.CohortYear.Valid {
		cohort = &row.CohortYear.Int32
	}
	var birth *time.Time
	if row.BirthDate.Valid {
		t := row.BirthDate.Time
		birth = &t
	}
	var gender *string
	if row.Gender.Valid {
		g := string(row.Gender.StudentsGender)
		gender = &g
	}
	var phone *string
	if row.Phone.Valid {
		p := row.Phone.String
		phone = &p
	}
	created := row.CreatedAt
	updated := row.UpdatedAt
	return dtos.StudentDTO{
		UserID:    row.UserID,
		StudentID: row.StudentID,
		Program: dtos.ProgramDTO{
			ID:   row.ProgramID,
			Name: row.ProgramName,
		},
		CohortYear: cohort,
		Status:     string(row.Status),
		BirthDate:  birth,
		Gender:     gender,
		Phone:      phone,
		CreatedAt:  created,
		UpdatedAt:  updated,
	}
}

func (s *StudentService) GetByUserID(ctx context.Context, userID int64) (*dtos.StudentDTO, error) {
	row, err := s.q.GetStudentByUserID(ctx, userID)
	if err != nil {
		return nil, err
	}
	dto := s.toDTO(row)
	return &dto, nil
}

func (s *StudentService) GetByStudentID(ctx context.Context, sid string) (*dtos.StudentDTO, error) {
	// reuse the same mapper since row fields are identical
	row, err := s.q.GetStudentByStudentID(ctx, sid)
	if err != nil {
		return nil, err
	}
	dto := dtos.StudentDTO{}
	{
		// Manual mapping to avoid needing a second mapper type
		tmp := gen.GetStudentByUserIDRow{
			UserID:      row.UserID,
			StudentID:   row.StudentID,
			ProgramID:   row.ProgramID,
			ProgramName: row.ProgramName,
			CohortYear:  row.CohortYear,
			Status:      row.Status,
			BirthDate:   row.BirthDate,
			Gender:      row.Gender,
			Phone:       row.Phone,
			CreatedAt:   row.CreatedAt,
			UpdatedAt:   row.UpdatedAt,
		}
		dto = s.toDTO(tmp)
	}
	return &dto, nil
}

func (s *StudentService) Upsert(ctx context.Context, userID int64, req dtos.UpsertStudentRequest) error {
	// Try update first
	err := s.q.UpdateStudentByUserID(ctx, gen.UpdateStudentByUserIDParams{
		StudentID:  req.StudentID,
		ProgramID:  req.ProgramID,
		CohortYear: nullInt32(req.CohortYear),
		Status:     statusOrDefault(req.Status),
		BirthDate:  nullTime(req.BirthDate),
		Gender:     genderNull(req.Gender),
		Phone:      nullString(req.Phone),
		UserID:     userID,
	})
	if err == nil {
		return nil
	}
	// If no rows exist, fallback to insert
	return s.q.InsertStudent(ctx, gen.InsertStudentParams{
		UserID:     userID,
		StudentID:  req.StudentID,
		ProgramID:  req.ProgramID,
		CohortYear: nullInt32(req.CohortYear),
		Status:     statusOrDefault(req.Status),
		BirthDate:  nullTime(req.BirthDate),
		Gender:     genderNull(req.Gender),
		Phone:      nullString(req.Phone),
	})
}

func (s *StudentService) ListPrograms(ctx context.Context) ([]dtos.ProgramDTO, error) {
	rows, err := s.q.ListPrograms(ctx)
	if err != nil {
		return nil, err
	}
	out := make([]dtos.ProgramDTO, 0, len(rows))
	for _, r := range rows {
		var facID *int64
		if r.FacultyID.Valid {
			v := r.FacultyID.Int64
			facID = &v
		}
		out = append(out, dtos.ProgramDTO{
			ID:        r.ID,
			Name:      r.Name,
			FacultyID: facID,
			CreatedAt: r.CreatedAt,
			UpdatedAt: r.UpdatedAt,
		})
	}
	return out, nil
}

// StudentListItem response mapping for listing
// Implemented via dto in dtos.StudentListItem
func (s *StudentService) Search(ctx context.Context, req dtos.StudentSearchRequest) ([]dtos.StudentListItem, int64, error) {
	if req.Page <= 0 {
		req.Page = 1
	}
	if req.Limit <= 0 {
		req.Limit = 10
	}
	offset := (req.Page - 1) * req.Limit

	// count first
	total, err := s.q.CountStudents(ctx, gen.CountStudentsParams{
		Search:     req.Search,
		ProgramID:  nullInt64(req.ProgramID),
		CohortYear: nullInt32(req.CohortYear),
		Status:     gen.StudentsStatus(req.Status),
	})
	if err != nil {
		return nil, 0, err
	}

	rows, err := s.q.SearchStudents(ctx, gen.SearchStudentsParams{
		Search:     req.Search,
		ProgramID:  nullInt64(req.ProgramID),
		CohortYear: nullInt32(req.CohortYear),
		Status:     gen.StudentsStatus(req.Status),
		Limit:      int32(req.Limit),
		Offset:     int32(offset),
	})
	if err != nil {
		return nil, 0, err
	}

	out := make([]dtos.StudentListItem, 0, len(rows))
	for _, r := range rows {
		var cohort *int32
		if r.CohortYear.Valid {
			cohort = &r.CohortYear.Int32
		}
		out = append(out, dtos.StudentListItem{
			UserID:     r.UserID,
			Name:       r.DisplayName,
			Email:      r.Email,
			StudentID:  r.StudentID,
			Program:    dtos.ProgramDTO{ID: r.ProgramID, Name: r.ProgramName},
			CohortYear: cohort,
			Status:     string(r.Status),
			CreatedAt:  r.CreatedAt,
			UpdatedAt:  r.UpdatedAt,
		})
	}

	return out, total, nil
}

// helpers for nullable types
func nullInt32(v *int32) sql.NullInt32 {
	if v == nil {
		return sql.NullInt32{}
	}
	return sql.NullInt32{Int32: *v, Valid: true}
}
func nullInt64(v *int64) sql.NullInt64 {
	if v == nil {
		return sql.NullInt64{}
	}
	return sql.NullInt64{Int64: *v, Valid: true}
}
func nullString(v *string) sql.NullString {
	if v == nil {
		return sql.NullString{}
	}
	return sql.NullString{String: *v, Valid: true}
}
func nullTime(v *time.Time) sql.NullTime {
	if v == nil {
		return sql.NullTime{}
	}
	return sql.NullTime{Time: *v, Valid: true}
}
func statusOrDefault(v *string) gen.StudentsStatus {
	if v == nil || *v == "" {
		return gen.StudentsStatusActive
	}
	return gen.StudentsStatus(*v)
}
func genderNull(v *string) gen.NullStudentsGender {
	if v == nil || *v == "" {
		return gen.NullStudentsGender{}
	}
	return gen.NullStudentsGender{StudentsGender: gen.StudentsGender(*v), Valid: true}
}
