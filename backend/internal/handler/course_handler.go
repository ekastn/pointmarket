package handler

import (
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/middleware"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"strconv"

	"github.com/gin-gonic/gin"
)

// CourseHandler handles course-related HTTP requests
type CourseHandler struct {
	courseService services.CourseService
}

// NewCourseHandler creates a new CourseHandler
func NewCourseHandler(courseService services.CourseService) *CourseHandler {
	return &CourseHandler{courseService: courseService}
}

// CreateCourse handles creating a new course (Admin/Teacher-only)
func (h *CourseHandler) CreateCourse(c *gin.Context) {
	var req dtos.CreateCourseRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request body: "+err.Error())
		return
	}

	userRole := middleware.GetRole(c)
	authUserID := middleware.GetUserID(c)

	if userRole == "guru" {
		req.OwnerID = authUserID
	} else if userRole == "admin" {
		// Admin can specify owner_id, or it can be inferred from admin's ID if not provided
		if req.OwnerID == 0 {
			req.OwnerID = authUserID
		}
	} else {
		response.Error(c, http.StatusForbidden, "Forbidden: Only teachers and admins can create courses")
		return
	}

	course, err := h.courseService.CreateCourse(c.Request.Context(), req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to create course: "+err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Course created successfully", course)
}

// GetCourseByID handles fetching a single course by ID
func (h *CourseHandler) GetCourseByID(c *gin.Context) {
	idParam := c.Param("id")
	id, err := strconv.ParseInt(idParam, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid course ID")
		return
	}

	course, err := h.courseService.GetCourseByID(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to retrieve course: "+err.Error())
		return
	}

	if course.ID == 0 { // Assuming ID will be 0 if not found
		response.Error(c, http.StatusNotFound, "Course not found")
		return
	}

	response.Success(c, http.StatusOK, "Course retrieved successfully", course)
}

// GetCourses handles fetching a list of courses based on user role and filters
func (h *CourseHandler) GetCourses(c *gin.Context) {
	authUserID := middleware.GetUserID(c)
	userRole := middleware.GetRole(c)

	page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
	limit, _ := strconv.Atoi(c.DefaultQuery("limit", "10"))
	search := c.Query("search")
	slug := c.Query("slug")

	// If slug query is provided, return exact match as a single-item list (or empty)
	if slug != "" {
		course, err := h.courseService.GetCourseBySlug(c.Request.Context(), slug)
		if err != nil {
			response.Error(c, http.StatusInternalServerError, "Failed to retrieve course by slug: "+err.Error())
			return
		}
		var courses []dtos.CourseDTO
		var total int64 = 0
		if course.ID != 0 {
			courses = []dtos.CourseDTO{course}
			total = 1
		} else {
			courses = []dtos.CourseDTO{}
			total = 0
		}
		response.Paginated(c, http.StatusOK, "Courses retrieved successfully", courses, total, page, limit)
		return
	}

	var filterUserID *int64
	userIDStr := c.Query("user_id")
	parseUserID, err := strconv.ParseInt(userIDStr, 10, 64)
	if err == nil {
		filterUserID = &parseUserID
	}

	switch userRole {
	case "guru":
		coursesDTOs, totalCourses, err := h.courseService.GetTeacherViewableCourses(c.Request.Context(), authUserID, page, limit, search)
		if err != nil {
			response.Error(c, http.StatusInternalServerError, "Failed to retrieve courses: "+err.Error())
			return
		}
		response.Paginated(c, http.StatusOK, "Courses retrieved successfully", coursesDTOs, totalCourses, page, limit)
		return
	case "admin":
		coursesDTOs, totalCourses, err := h.courseService.GetCourses(c.Request.Context(), filterUserID, page, limit, search)
		if err != nil {
			response.Error(c, http.StatusInternalServerError, "Failed to retrieve courses: "+err.Error())
			return
		}
		response.Paginated(c, http.StatusOK, "Courses retrieved successfully", coursesDTOs, totalCourses, page, limit)
		return
	case "siswa":
		coursesDTOs, totalCourses, err := h.courseService.GetStudentViewableCourses(c.Request.Context(), authUserID, page, limit, search)
		if err != nil {
			response.Error(c, http.StatusInternalServerError, "Failed to retrieve courses: "+err.Error())
			return
		}
		response.Paginated(c, http.StatusOK, "Courses retrieved successfully", coursesDTOs, totalCourses, page, limit)
		return
	}
}

// UpdateCourse handles updating an existing course (Admin/Teacher-only, owner only)
func (h *CourseHandler) UpdateCourse(c *gin.Context) {
	idParam := c.Param("id")
	id, err := strconv.ParseInt(idParam, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid course ID")
		return
	}

	var req dtos.UpdateCourseRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request body: "+err.Error())
		return
	}

	// Authorization check: Only owner or admin can update
	authUserID := middleware.GetUserID(c)
	userRole := middleware.GetRole(c)

	// Get the course to check ownership
	course, err := h.courseService.GetCourseByID(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to retrieve course for authorization: "+err.Error())
		return
	}
	if course.ID == 0 {
		response.Error(c, http.StatusNotFound, "Course not found")
		return
	}

	if userRole != "admin" && course.OwnerID != authUserID {
		response.Error(c, http.StatusForbidden, "Forbidden: You do not own this course or are not an admin")
		return
	}

	updatedCourse, err := h.courseService.UpdateCourse(c.Request.Context(), id, req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to update course: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Course updated successfully", updatedCourse)
}

// DeleteCourse handles deleting a course by its ID (Admin/Teacher-only, owner only)
func (h *CourseHandler) DeleteCourse(c *gin.Context) {
	idParam := c.Param("id")
	id, err := strconv.ParseInt(idParam, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid course ID")
		return
	}

	// Authorization check: Only owner or admin can delete
	authUserID := middleware.GetUserID(c)
	userRole := middleware.GetRole(c)

	// Get the course to check ownership
	course, err := h.courseService.GetCourseByID(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to retrieve course for authorization: "+err.Error())
		return
	}
	if course.ID == 0 {
		response.Error(c, http.StatusNotFound, "Course not found")
		return
	}

	if userRole != "admin" && course.OwnerID != authUserID {
		response.Error(c, http.StatusForbidden, "Forbidden: You do not own this course or are not an admin")
		return
	}

	err = h.courseService.DeleteCourse(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to delete course: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Course deleted successfully", nil)
}

// EnrollStudent handles enrolling a student in a course
func (h *CourseHandler) EnrollStudent(c *gin.Context) {
	courseIDParam := c.Param("id")
	courseID, err := strconv.ParseInt(courseIDParam, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid course ID")
		return
	}

	var req dtos.EnrollStudentRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request body: "+err.Error())
		return
	}

	// If UserID is not provided in the request body, use the authenticated user's ID
	if req.UserID == 0 {
		authUserID := middleware.GetUserID(c)
		req.UserID = authUserID
	} else {
		// If UserID is provided, ensure only admin can specify it
		userRole := middleware.GetRole(c)
		if userRole != "admin" {
			response.Error(c, http.StatusForbidden, "Forbidden: Only admins can enroll other users")
			return
		}
	}

	// Ensure courseID from path matches the one in the request body if provided
	if req.CourseID != 0 && req.CourseID != courseID {
		response.Error(c, http.StatusBadRequest, "Course ID in path and body do not match")
		return
	}
	req.CourseID = courseID // Use ID from path

	course, err := h.courseService.EnrollStudentInCourse(c.Request.Context(), req)
	if err != nil {
		if err == services.ErrAlreadyEnrolled {
			response.Error(c, http.StatusConflict, "Student is already enrolled in this course")
			return
		}
		response.Error(c, http.StatusInternalServerError, "Failed to enroll student: "+err.Error())
		return
	}

	var courseDTO dtos.CourseDTO
	courseDTO.FromCourseModel(course)
	response.Success(c, http.StatusCreated, "Student enrolled successfully", courseDTO)
}

// UnenrollStudent handles unenrolls a student from a course
func (h *CourseHandler) UnenrollStudent(c *gin.Context) {
	courseIDParam := c.Param("id")
	courseID, err := strconv.ParseInt(courseIDParam, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid course ID")
		return
	}

	var req dtos.EnrollStudentRequestDTO // Reusing DTO for UserID
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request body: "+err.Error())
		return
	}

	// If UserID is not provided in the request body, use the authenticated user's ID
	if req.UserID == 0 {
		authUserID := middleware.GetUserID(c)
		req.UserID = authUserID
	} else {
		// If UserID is provided, ensure only admin can specify it
		userRole := middleware.GetRole(c)
		if userRole != "admin" {
			response.Error(c, http.StatusForbidden, "Forbidden: Only admins can unenroll other users")
			return
		}
	}

	// Ensure courseID from path matches the one in the request body if provided
	if req.CourseID != 0 && req.CourseID != courseID {
		response.Error(c, http.StatusBadRequest, "Course ID in path and body do not match")
		return
	}
	req.CourseID = courseID // Use ID from path

	err = h.courseService.UnenrollStudentFromCourse(c.Request.Context(), req.UserID, req.CourseID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to unenroll student: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Student unenrolled successfully", nil)
}
