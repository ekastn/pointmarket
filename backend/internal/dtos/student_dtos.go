package dtos

import "time"

type ProgramDTO struct {
	ID        int64     `json:"id"`
	Name      string    `json:"name"`
	FacultyID *int64    `json:"faculty_id,omitempty"`
	CreatedAt time.Time `json:"created_at"`
	UpdatedAt time.Time `json:"updated_at"`
}

type StudentDTO struct {
	UserID     int64      `json:"user_id"`
	StudentID  string     `json:"student_id"`
	Program    ProgramDTO `json:"program"`
	CohortYear *int32     `json:"cohort_year,omitempty"`
	Status     string     `json:"status"`
	BirthDate  *time.Time `json:"birth_date,omitempty"`
	Gender     *string    `json:"gender,omitempty"`
	Phone      *string    `json:"phone,omitempty"`
	CreatedAt  time.Time  `json:"created_at"`
	UpdatedAt  time.Time  `json:"updated_at"`
}

type StudentListItem struct {
	UserID     int64      `json:"user_id"`
	Name       string     `json:"name"`
	Email      string     `json:"email"`
	StudentID  string     `json:"student_id"`
	Program    ProgramDTO `json:"program"`
	CohortYear *int32     `json:"cohort_year,omitempty"`
	Status     string     `json:"status"`
	CreatedAt  time.Time  `json:"created_at"`
	UpdatedAt  time.Time  `json:"updated_at"`
}

type UpsertStudentRequest struct {
	StudentID  string     `json:"student_id" binding:"required"`
	ProgramID  int64      `json:"program_id" binding:"required"`
	CohortYear *int32     `json:"cohort_year"`
	Status     *string    `json:"status"`
	BirthDate  *time.Time `json:"birth_date"`
	Gender     *string    `json:"gender"`
	Phone      *string    `json:"phone"`
}

type StudentSearchRequest struct {
	Search     string `form:"search"`
	ProgramID  *int64 `form:"program_id"`
	CohortYear *int32 `form:"cohort_year"`
	Status     string `form:"status"`
	Page       int    `form:"page"`
	Limit      int    `form:"limit"`
}

type StudentDetailsDTO struct {
	UserID      int64            `json:"user_id"`
	StudentID   string           `json:"student_id"`
	DisplayName string           `json:"display_name"`
	Email       string           `json:"email"`
	ProgramName string           `json:"program_name"`
	CohortYear  *int32           `json:"cohort_year,omitempty"`
	Status      string           `json:"status"`
	BirthDate   *time.Time       `json:"birth_date,omitempty"`
	Gender      *string          `json:"gender,omitempty"`
	Phone       *string          `json:"phone,omitempty"`
	VARKResult     *StudentLearningStyle   `json:"vark_result,omitempty"`
	MSLQResult     *LikertResultDTO `json:"mslq_result,omitempty"`
	AMSResult      *LikertResultDTO `json:"ams_result,omitempty"`
	AcademicScore  float64          `json:"academic_score"`
}
