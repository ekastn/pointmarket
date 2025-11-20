package services

import (
	"context"
	"database/sql"
	"errors"
	mysql "github.com/go-sql-driver/mysql"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
	"pointmarket/backend/internal/utils"
	"strings"
)

// CourseService provides business logic for courses and student enrollment
type CourseService struct {
	q gen.Querier
}

// NewCourseService creates a new CourseService
func NewCourseService(q gen.Querier) *CourseService {
	return &CourseService{q: q}
}

// CreateCourse creates a new course
func (s *CourseService) CreateCourse(ctx context.Context, req dtos.CreateCourseRequestDTO) (dtos.CourseDTO, error) {
	result, err := s.q.CreateCourse(ctx, gen.CreateCourseParams{
		Title:       req.Title,
		Slug:        req.Slug,
		Description: utils.NullString(req.Description),
		OwnerID:     req.OwnerID,
		Metadata:    req.Metadata,
	})
	if err != nil {
		return dtos.CourseDTO{}, err
	}

	id, err := result.LastInsertId()
	if err != nil {
		return dtos.CourseDTO{}, err
	}

	course, err := s.q.GetCourseByID(ctx, id)
	if err != nil {
		return dtos.CourseDTO{}, err
	}

	var courseDTO dtos.CourseDTO
	courseDTO.FromCourseModel(course)
	// Enrich owner display name
	if u, err2 := s.q.GetUserByID(ctx, course.OwnerID); err2 == nil {
		courseDTO.OwnerDisplayName = u.DisplayName
		courseDTO.OwnerRole = string(u.Role)
	}
	return courseDTO, nil
}

// GetCourseByID retrieves a single course by its ID
func (s *CourseService) GetCourseByID(ctx context.Context, id int64) (dtos.CourseDTO, error) {
	course, err := s.q.GetCourseByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.CourseDTO{}, nil // Course not found
	}
	if err != nil {
		return dtos.CourseDTO{}, err
	}

	var courseDTO dtos.CourseDTO
	courseDTO.FromCourseModel(course)
	// Enrich owner display name
	if u, err2 := s.q.GetUserByID(ctx, course.OwnerID); err2 == nil {
		courseDTO.OwnerDisplayName = u.DisplayName
		courseDTO.OwnerRole = string(u.Role)
	}
	return courseDTO, nil
}

// GetCourses retrieves a list of courses based on role and filters
func (s *CourseService) GetCourses(ctx context.Context, filterUserID *int64, page, limit int, search string) ([]dtos.CourseDTO, int64, error) {
	offset := (page - 1) * limit

	var courses []gen.Course
	var totalCourses int64
	var err error
	listParams := gen.GetCoursesParams{
		Limit:  int32(limit),
		Offset: int32(offset),
	}

	if filterUserID != nil {
		// Admin filtering for a specific user's enrolled courses
		studentCourses, err := s.q.GetStudentCoursesByUserID(ctx, *filterUserID)
		if err != nil {
			return nil, 0, err
		}
		var courseDTOs []dtos.CourseDTO
		for _, sc := range studentCourses {
			var courseDesc *string
			if sc.CourseDescription.Valid {
				courseDesc = &sc.CourseDescription.String
			}
			if search != "" {
				title := strings.ToLower(sc.CourseTitle)
				var descVal string
				if courseDesc != nil {
					descVal = strings.ToLower(*courseDesc)
				}
				if !strings.Contains(title, strings.ToLower(search)) && (courseDesc == nil || !strings.Contains(descVal, strings.ToLower(search))) {
					continue
				}
			}
			courseDTOs = append(courseDTOs, dtos.CourseDTO{
				ID:          sc.CourseID,
				Title:       sc.CourseTitle,
				Slug:        sc.CourseSlug,
				Description: courseDesc,
				OwnerID:     sc.CourseOwnerID,
				Metadata:    sc.CourseMetadata,
				// CreatedAt and UpdatedAt are not in GetStudentCoursesByUserIDRow
				// This means we might need to fetch full course details if needed
			})
		}
		totalCourses = int64(len(courseDTOs))
		// Manual pagination post-filter
		start := offset
		if start > len(courseDTOs) {
			start = len(courseDTOs)
		}
		end := start + limit
		if end > len(courseDTOs) {
			end = len(courseDTOs)
		}
		return courseDTOs[start:end], totalCourses, nil
	} else {
		// Admin getting all courses
		if search != "" {
			rows, err2 := s.q.GetCoursesWithEnrollmentStatus(ctx, gen.GetCoursesWithEnrollmentStatusParams{
				UserID: 0,
				Search: search,
				Limit:  int32(limit),
				Offset: int32(offset),
			})
			if err2 != nil {
				return nil, 0, err2
			}
			total, err2 := s.q.CountCoursesWithEnrollmentStatus(ctx, gen.CountCoursesWithEnrollmentStatusParams{Search: search})
			if err2 != nil {
				return nil, 0, err2
			}
			totalCourses = total
			var courseDTOs []dtos.CourseDTO
			for _, r := range rows {
				var desc *string
				if r.Description.Valid {
					desc = &r.Description.String
				}
				dto := dtos.CourseDTO{
					ID:          r.ID,
					Title:       r.Title,
					Slug:        r.Slug,
					Description: desc,
					OwnerID:     r.OwnerID,
					Metadata:    r.Metadata,
					CreatedAt:   r.CreatedAt,
					UpdatedAt:   r.UpdatedAt,
				}
				if u, err2 := s.q.GetUserByID(ctx, r.OwnerID); err2 == nil {
					dto.OwnerDisplayName = u.DisplayName
					dto.OwnerRole = string(u.Role)
				}
				courseDTOs = append(courseDTOs, dto)
			}
			return courseDTOs, totalCourses, nil
		}
		courses, err = s.q.GetCourses(ctx, listParams)
		if err != nil {
			return nil, 0, err
		}
		totalCourses, err = s.q.CountCourses(ctx)
	}

	if err != nil {
		return nil, 0, err
	}

	var courseDTOs []dtos.CourseDTO
	for _, course := range courses {
		var courseDTO dtos.CourseDTO
		courseDTO.FromCourseModel(course)
		if u, err2 := s.q.GetUserByID(ctx, course.OwnerID); err2 == nil {
			courseDTO.OwnerDisplayName = u.DisplayName
			courseDTO.OwnerRole = string(u.Role)
		}
		courseDTOs = append(courseDTOs, courseDTO)
	}
	return courseDTOs, totalCourses, nil
}

// GetCoursesByOwnerID retrieves a list of courses owned by a specific user
func (s *CourseService) GetCoursesByOwnerID(ctx context.Context, ownerID int64, page, limit int, search string) ([]dtos.CourseDTO, int64, error) {
	offset := (page - 1) * limit
	if search == "" {
		courses, err := s.q.GetCoursesByOwnerID(ctx, gen.GetCoursesByOwnerIDParams{
			OwnerID: ownerID,
			Limit:   int32(limit),
			Offset:  int32(offset),
		})
		if err != nil {
			return nil, 0, err
		}
		totalCourses, err := s.q.CountCoursesByOwnerID(ctx, ownerID)
		if err != nil {
			return nil, 0, err
		}
		var courseDTOs []dtos.CourseDTO
		for _, course := range courses {
			var courseDTO dtos.CourseDTO
			courseDTO.FromCourseModel(course)
			if u, err2 := s.q.GetUserByID(ctx, course.OwnerID); err2 == nil {
				courseDTO.OwnerDisplayName = u.DisplayName
				courseDTO.OwnerRole = string(u.Role)
			}
			courseDTOs = append(courseDTOs, courseDTO)
		}
		return courseDTOs, totalCourses, nil
	}
	// With search: reuse search-enabled query then filter by owner and paginate in memory
	rows, err := s.q.GetCoursesWithEnrollmentStatus(ctx, gen.GetCoursesWithEnrollmentStatusParams{
		UserID: 0,
		Search: search,
		Limit:  int32(10000),
		Offset: 0,
	})
	if err != nil {
		return nil, 0, err
	}
	var filtered []dtos.CourseDTO
	for _, r := range rows {
		if r.OwnerID != ownerID {
			continue
		}
		var desc *string
		if r.Description.Valid {
			desc = &r.Description.String
		}
		dto := dtos.CourseDTO{
			ID:          r.ID,
			Title:       r.Title,
			Slug:        r.Slug,
			Description: desc,
			OwnerID:     r.OwnerID,
			Metadata:    r.Metadata,
			CreatedAt:   r.CreatedAt,
			UpdatedAt:   r.UpdatedAt,
		}
		if u, err2 := s.q.GetUserByID(ctx, r.OwnerID); err2 == nil {
			dto.OwnerDisplayName = u.DisplayName
			dto.OwnerRole = string(u.Role)
		}
		filtered = append(filtered, dto)
	}
	total := int64(len(filtered))
	start := offset
	if start > len(filtered) {
		start = len(filtered)
	}
	end := start + limit
	if end > len(filtered) {
		end = len(filtered)
	}
	return filtered[start:end], total, nil
}

// GetStudentViewableCourses retrieves a list of courses for students with enrollment status
func (s *CourseService) GetStudentViewableCourses(ctx context.Context, studentID int64, page, limit int, search string) ([]dtos.StudentCoursesDTO, int64, error) {
	offset := (page - 1) * limit

	courses, err := s.q.GetCoursesWithEnrollmentStatus(ctx, gen.GetCoursesWithEnrollmentStatusParams{
		UserID: studentID,
		Search: search,
		Limit:  int32(limit),
		Offset: int32(offset),
	})
	if err != nil {
		return nil, 0, err
	}

	totalCourses, err := s.q.CountCoursesWithEnrollmentStatus(ctx, gen.CountCoursesWithEnrollmentStatusParams{Search: search})
	if err != nil {
		return nil, 0, err
	}

	var courseDTOs []dtos.StudentCoursesDTO
	for _, course := range courses {
		var courseDTO dtos.StudentCoursesDTO
		courseDTO.FromStudentCoursesModel(course)
		if u, err2 := s.q.GetUserByID(ctx, course.OwnerID); err2 == nil {
			courseDTO.OwnerDisplayName = u.DisplayName
			courseDTO.OwnerRole = string(u.Role)
		}
		courseDTOs = append(courseDTOs, courseDTO)
	}

	return courseDTOs, totalCourses, nil
}

// GetTeacherViewableCourses retrieves a list of courses for teachers with ownership status
func (s *CourseService) GetTeacherViewableCourses(ctx context.Context, teacherID int64, page, limit int, search string) ([]dtos.TeacherCoursesDTO, int64, error) {
	offset := (page - 1) * limit

	// Use the new query to get courses with ownership status
	courses, err := s.q.GetCoursesWithOwnershipStatus(ctx, gen.GetCoursesWithOwnershipStatusParams{
		UserID: teacherID,
		Search: search,
		Limit:  int32(limit),
		Offset: int32(offset),
	})
	if err != nil {
		return nil, 0, err
	}

	// Use the new count query
	totalCourses, err := s.q.CountCoursesWithOwnershipStatus(ctx, gen.CountCoursesWithOwnershipStatusParams{Search: search})
	if err != nil {
		return nil, 0, err
	}

	var teacherCourseDTOs []dtos.TeacherCoursesDTO
	for _, course := range courses {
		var courseDTO dtos.TeacherCoursesDTO
		courseDTO.FromTeacherCoursesModel(course)
		// Enrich owner display name and role
		if u, err2 := s.q.GetUserByID(ctx, course.OwnerID); err2 == nil {
			courseDTO.OwnerDisplayName = u.DisplayName
			courseDTO.OwnerRole = string(u.Role)
		}
		teacherCourseDTOs = append(teacherCourseDTOs, courseDTO)
	}

	return teacherCourseDTOs, totalCourses, nil
}

// UpdateCourse updates an existing course
func (s *CourseService) UpdateCourse(ctx context.Context, id int64, req dtos.UpdateCourseRequestDTO) (dtos.CourseDTO, error) {
	// Get existing course to apply partial updates
	existingCourse, err := s.q.GetCourseByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.CourseDTO{}, nil // Course not found
	}
	if err != nil {
		return dtos.CourseDTO{}, err
	}

	// Apply updates
	title := existingCourse.Title
	if req.Title != nil {
		title = *req.Title
	}

	slug := existingCourse.Slug
	if req.Slug != nil {
		slug = *req.Slug
	}

	description := existingCourse.Description
	if req.Description != nil {
		description = sql.NullString{String: *req.Description, Valid: true}
	}

	metadata := existingCourse.Metadata
	if req.Metadata != nil { // Assuming Metadata is always provided if updated
		metadata = req.Metadata
	}

	err = s.q.UpdateCourse(ctx, gen.UpdateCourseParams{
		Title:       title,
		Slug:        slug,
		Description: description,
		Metadata:    metadata,
		ID:          id,
	})
	if err != nil {
		return dtos.CourseDTO{}, err
	}

	updatedCourse, err := s.q.GetCourseByID(ctx, id)
	if err != nil {
		return dtos.CourseDTO{}, err
	}

	var courseDTO dtos.CourseDTO
	courseDTO.FromCourseModel(updatedCourse)
	if u, err2 := s.q.GetUserByID(ctx, updatedCourse.OwnerID); err2 == nil {
		courseDTO.OwnerDisplayName = u.DisplayName
		courseDTO.OwnerRole = string(u.Role)
	}
	return courseDTO, nil
}

// DeleteCourse deletes a course by its ID
func (s *CourseService) DeleteCourse(ctx context.Context, id int64) error {
	return s.q.DeleteCourse(ctx, id)
}

// EnrollStudentInCourse enrolls a student in a course
func (s *CourseService) EnrollStudentInCourse(ctx context.Context, req dtos.EnrollStudentRequestDTO) error {
	_, err := s.q.EnrollStudentInCourse(ctx, gen.EnrollStudentInCourseParams{
		UserID:   req.UserID,
		CourseID: req.CourseID,
	})
	if err != nil {
		var me *mysql.MySQLError
		if errors.As(err, &me) && me.Number == 1062 {
			return ErrAlreadyEnrolled
		}
		return err
	}
	return nil
}

// UnenrollStudentFromCourse unenrolls a student from a course
func (s *CourseService) UnenrollStudentFromCourse(ctx context.Context, userID, courseID int64) error {
	return s.q.UnenrollStudentFromCourse(ctx, gen.UnenrollStudentFromCourseParams{
		UserID:   userID,
		CourseID: courseID,
	})
}

// ErrAlreadyEnrolled is returned when attempting to enroll a student who is already enrolled
var ErrAlreadyEnrolled = errors.New("student already enrolled in course")

// GetCourseBySlug retrieves a course by its slug; returns empty DTO if not found
func (s *CourseService) GetCourseBySlug(ctx context.Context, slug string) (dtos.CourseDTO, error) {
	course, err := s.q.GetCourseBySlug(ctx, slug)
	if err == sql.ErrNoRows {
		return dtos.CourseDTO{}, nil
	}
	if err != nil {
		return dtos.CourseDTO{}, err
	}
	var dto dtos.CourseDTO
	dto.FromCourseModel(course)
	if u, err2 := s.q.GetUserByID(ctx, course.OwnerID); err2 == nil {
		dto.OwnerDisplayName = u.DisplayName
		dto.OwnerRole = string(u.Role)
	}
	return dto, nil
}
