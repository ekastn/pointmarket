package handler

import (
	"net/http"
	"pointmarket/backend/internal/dtos"
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

	// Infer OwnerID from authenticated user if not admin
	userRole, exists := c.Get("userRole")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User role not found in context")
		return
	}
	authUserID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User ID not found in context")
		return
	}

	if userRole.(string) == "guru" {
		req.OwnerID = authUserID.(int64)
	} else if userRole.(string) == "admin" {
		// Admin can specify owner_id, or it can be inferred from admin's ID if not provided
		if req.OwnerID == 0 {
			req.OwnerID = authUserID.(int64)
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
	authUserID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User ID not found in context")
		return
	}
	userRole, exists := c.Get("userRole")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User role not found in context")
		return
	}

	var filterUserID *int64
	userIDStr := c.Query("user_id")

	if userRole.(string) == "admin" {
		if userIDStr != "" {
			parsedUserID, err := strconv.ParseInt(userIDStr, 10, 64)
			if err != nil {
				response.Error(c, http.StatusBadRequest, "Invalid user ID parameter")
				return
			}
			filterUserID = &parsedUserID
		}
	} else if userRole.(string) == "siswa" {
		// Students automatically get their own enrolled courses
		actualAuthUserID := authUserID.(int64) // Corrected type assertion
		filterUserID = &actualAuthUserID
	} else if userRole.(string) == "guru" {
		// Teachers get courses they own
		actualAuthUserID := authUserID.(int64) // Corrected type assertion
		filterUserID = &actualAuthUserID
		// TODO: This needs a GetCoursesByOwnerID query in service
		// For now, it will return all courses if no specific query for owner_id exists
	}

	courses, err := h.courseService.GetCourses(c.Request.Context(), authUserID.(int64), userRole.(string), filterUserID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to retrieve courses: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Courses retrieved successfully", dtos.ListCoursesResponseDTO{
		Courses: courses,
		Total:   len(courses),
	})
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
	authUserID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User ID not found in context")
		return
	}
	userRole, exists := c.Get("userRole")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User role not found in context")
		return
	}

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

	if userRole.(string) != "admin" && course.OwnerID != authUserID.(int64) {
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
	authUserID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User ID not found in context")
		return
	}
	userRole, exists := c.Get("userRole")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User role not found in context")
		return
	}

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

	if userRole.(string) != "admin" && course.OwnerID != authUserID.(int64) {
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
		authUserID, exists := c.Get("userID")
		if !exists {
			response.Error(c, http.StatusUnauthorized, "User ID not found in context")
			return
		}
		req.UserID = authUserID.(int64)
	} else {
		// If UserID is provided, ensure only admin can specify it
		userRole, exists := c.Get("userRole")
		if !exists || userRole.(string) != "admin" {
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

	err = h.courseService.EnrollStudentInCourse(c.Request.Context(), req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to enroll student: "+err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Student enrolled successfully", nil)
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
		authUserID, exists := c.Get("userID")
		if !exists {
			response.Error(c, http.StatusUnauthorized, "User ID not found in context")
			return
		}
		req.UserID = authUserID.(int64)
	} else {
		// If UserID is provided, ensure only admin can specify it
		userRole, exists := c.Get("userRole")
		if !exists || userRole.(string) != "admin" {
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
