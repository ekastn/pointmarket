package services

import (
	"context"
	"database/sql"
	"encoding/json"
	"fmt"
	"log"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
	"pointmarket/backend/internal/utils"
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
		CohortYear: utils.NullInt32(req.CohortYear),
		Status:     utils.StatusOrDefault(req.Status),
		BirthDate:  utils.NullTime(req.BirthDate),
		Gender:     utils.NullGender(req.Gender),
		Phone:      utils.NullString(req.Phone),
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
		CohortYear: utils.NullInt32(req.CohortYear),
		Status:     utils.StatusOrDefault(req.Status),
		BirthDate:  utils.NullTime(req.BirthDate),
		Gender:     utils.NullGender(req.Gender),
		Phone:      utils.NullString(req.Phone),
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
		ProgramID:  utils.NullInt64(req.ProgramID),
		CohortYear: utils.NullInt32(req.CohortYear),
		Status:     gen.StudentsStatus(req.Status),
	})
	if err != nil {
		return nil, 0, err
	}

	rows, err := s.q.SearchStudents(ctx, gen.SearchStudentsParams{
		Search:     req.Search,
		ProgramID:  utils.NullInt64(req.ProgramID),
		CohortYear: utils.NullInt32(req.CohortYear),
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

func (s *StudentService) GetStudentDetailsByUserID(ctx context.Context, userID int64) (*dtos.StudentDetailsDTO, error) {
	// Fetch basic student info along with user details
	studentRow, err := s.q.GetStudentByUserID(ctx, userID)
	if err != nil {
		return nil, fmt.Errorf("failed to get student by user ID %d: %w", userID, err)
	}

	userRow, err := s.q.GetUserByID(ctx, userID)
	if err != nil {
		return nil, fmt.Errorf("failed to get user by ID %d: %w", userID, err)
	}

	studentDetails := &dtos.StudentDetailsDTO{
		UserID:        studentRow.UserID,
		StudentID:     studentRow.StudentID,
		DisplayName:   userRow.DisplayName,
		Email:         userRow.Email,
		ProgramName:   studentRow.ProgramName,
		Status:        string(studentRow.Status),
		AcademicScore: studentRow.AcademicScore,
	}

	if studentRow.CohortYear.Valid {
		studentDetails.CohortYear = &studentRow.CohortYear.Int32
	}
	if studentRow.BirthDate.Valid {
		studentDetails.BirthDate = &studentRow.BirthDate.Time
	}
	if studentRow.Gender.Valid {
		g := string(studentRow.Gender.StudentsGender)
		studentDetails.Gender = &g
	}
	if studentRow.Phone.Valid {
		p := studentRow.Phone.String
		studentDetails.Phone = &p
	}

	// Fetch latest VARK result (using learning style which includes style type/label)
	varkLearningStyle, err := s.q.GetStudentLearningStyle(ctx, userID)
	if err == nil {
		// Safely dereference score pointers
		scoreVisual := 0.0
		if varkLearningStyle.ScoreVisual != nil {
			scoreVisual = *varkLearningStyle.ScoreVisual
		}
		scoreAuditory := 0.0
		if varkLearningStyle.ScoreAuditory != nil {
			scoreAuditory = *varkLearningStyle.ScoreAuditory
		}
		scoreReading := 0.0
		if varkLearningStyle.ScoreReading != nil {
			scoreReading = *varkLearningStyle.ScoreReading
		}
		scoreKinesthetic := 0.0
		if varkLearningStyle.ScoreKinesthetic != nil {
			scoreKinesthetic = *varkLearningStyle.ScoreKinesthetic
		}

		studentDetails.VARKResult = &dtos.StudentLearningStyle{
			Type:  string(varkLearningStyle.Type),
			Label: varkLearningStyle.Label,
			Scores: dtos.VARKScores{
				Visual:      scoreVisual,
				Auditory:    scoreAuditory,
				Reading:     scoreReading,
				Kinesthetic: scoreKinesthetic,
			},
		}
	} else if err != sql.ErrNoRows {
		log.Printf("error fetching VARK learning style for user %d: %v", userID, err)
	}

	// Fetch latest MSLQ result
	mslqResult, err := s.q.GetLatestLikertResultByType(ctx, gen.GetLatestLikertResultByTypeParams{
		UserID: userID,
		Type:   "MSLQ",
	})
	if err == nil {
		var mslqSubscaleScores map[string]float64
		if len(mslqResult.SubscaleScores) > 0 {
			if err := json.Unmarshal(mslqResult.SubscaleScores, &mslqSubscaleScores); err != nil {
				log.Printf("error unmarshalling MSLQ subscale scores for user %d: %v", userID, err)
				mslqSubscaleScores = make(map[string]float64)
			}
		}
		studentDetails.MSLQResult = &dtos.LikertResultDTO{
			ID:              mslqResult.ID,
			StudentID:       mslqResult.StudentID,
			QuestionnaireID: int64(mslqResult.QuestionnaireID),
			TotalScore:      mslqResult.TotalScore,
			SubscaleScores:  mslqSubscaleScores,
			CreatedAt:       mslqResult.CreatedAt.Time,
		}
	} else if err != sql.ErrNoRows {
		log.Printf("error fetching MSLQ result for user %d: %v", userID, err)
	}

	// Fetch latest AMS result
	amsResult, err := s.q.GetLatestLikertResultByType(ctx, gen.GetLatestLikertResultByTypeParams{
		UserID: userID,
		Type:   "AMS",
	})
	if err == nil {
		var amsSubscaleScores map[string]float64
		if len(amsResult.SubscaleScores) > 0 {
			if err := json.Unmarshal(amsResult.SubscaleScores, &amsSubscaleScores); err != nil {
				log.Printf("error unmarshalling AMS subscale scores for user %d: %v", userID, err)
				amsSubscaleScores = make(map[string]float64)
			}
		}
		studentDetails.AMSResult = &dtos.LikertResultDTO{
			ID:              amsResult.ID,
			StudentID:       amsResult.StudentID,
			QuestionnaireID: int64(amsResult.QuestionnaireID),
			TotalScore:      amsResult.TotalScore,
			SubscaleScores:  amsSubscaleScores,
			CreatedAt:       amsResult.CreatedAt.Time,
		}
	} else if err != sql.ErrNoRows {
		log.Printf("error fetching AMS result for user %d: %v", userID, err)
	}

	return studentDetails, nil
}

func (s *StudentService) recalculateAcademicScore(ctx context.Context, userID int64) error {
	scores, err := s.q.GetStudentAcademicScores(ctx, gen.GetStudentAcademicScoresParams{UserID: userID, UserID_2: userID})
	if err != nil {
		return fmt.Errorf("could not get academic scores for user %d: %w", userID, err)
	}

	if len(scores) == 0 {
		return s.q.UpdateStudentAcademicScore(ctx, gen.UpdateStudentAcademicScoreParams{
			AcademicScore: 0.0,
			UserID:        userID,
		})
	}

	var totalScore float64
	for _, scorePtr := range scores {
		if scorePtr != nil {
			totalScore += *scorePtr
		}
	}
	average := totalScore / float64(len(scores))

	return s.q.UpdateStudentAcademicScore(ctx, gen.UpdateStudentAcademicScoreParams{
		AcademicScore: average,
		UserID:        userID,
	})
}
