package services

import (
	"context"
	"database/sql"
	"fmt"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
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
		Description: sql.NullString{String: *req.Description, Valid: req.Description != nil},
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
	return courseDTO, nil
}

// GetCourses retrieves a list of courses based on role and filters
func (s *CourseService) GetCourses(ctx context.Context, userID int64, userRole string, filterUserID *int64) ([]dtos.CourseDTO, error) {
	var courses []gen.Course
	var err error

	switch userRole {
	case "admin":
		if filterUserID != nil {
			// Admin filtering for a specific user's enrolled courses
			studentCourses, err := s.q.GetStudentCoursesByUserID(ctx, *filterUserID)
			if err != nil {
				return nil, err
			}
			var courseDTOs []dtos.CourseDTO
			for _, sc := range studentCourses {
				var courseDesc *string
				if sc.CourseDescription.Valid {
					courseDesc = &sc.CourseDescription.String
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
			return courseDTOs, nil
		} else {
			// Admin getting all courses
			courses, err = s.q.GetCourses(ctx)
		}
	case "guru": // Teacher
		// Teachers get courses they own
		courses, err = s.q.GetCourses(ctx) // This needs to be filtered by owner_id
		// TODO: Add GetCoursesByOwnerID query
		// For now, returning all courses, will refine later
	case "siswa": // Student
		// Students get courses they are enrolled in
		studentCourses, err := s.q.GetStudentCoursesByUserID(ctx, userID)
		if err != nil {
			return nil, err
		}
		var courseDTOs []dtos.CourseDTO
		for _, sc := range studentCourses {
			var courseDesc *string
			if sc.CourseDescription.Valid {
				courseDesc = &sc.CourseDescription.String
			}
			courseDTOs = append(courseDTOs, dtos.CourseDTO{
				ID:          sc.CourseID,
				Title:       sc.CourseTitle,
				Slug:        sc.CourseSlug,
				Description: courseDesc,
				OwnerID:     sc.CourseOwnerID,
				Metadata:    sc.CourseMetadata,
				// CreatedAt and UpdatedAt are not in GetStudentCoursesByUserIDRow
			})
		}
		return courseDTOs, nil
	default:
		return nil, fmt.Errorf("unsupported user role: %s", userRole)
	}

	if err != nil {
		return nil, err
	}

	var courseDTOs []dtos.CourseDTO
	for _, course := range courses {
		var courseDTO dtos.CourseDTO
		courseDTO.FromCourseModel(course)
		courseDTOs = append(courseDTOs, courseDTO)
	}
	return courseDTOs, nil
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
	return courseDTO, nil
}

// DeleteCourse deletes a course by its ID
func (s *CourseService) DeleteCourse(ctx context.Context, id int64) error {
	return s.q.DeleteCourse(ctx, id)
}

// EnrollStudentInCourse enrolls a student in a course
func (s *CourseService) EnrollStudentInCourse(ctx context.Context, req dtos.EnrollStudentRequestDTO) error {
	_, err := s.q.EnrollStudentInCourse(ctx, gen.EnrollStudentInCourseParams{
		StudentID: req.UserID,
		CourseID:  req.CourseID,
	})
	return err
}

// UnenrollStudentFromCourse unenrolls a student from a course
func (s *CourseService) UnenrollStudentFromCourse(ctx context.Context, userID, courseID int64) error {
	return s.q.UnenrollStudentFromCourse(ctx, gen.UnenrollStudentFromCourseParams{
		StudentID: userID,
		CourseID:  courseID,
	})
}
