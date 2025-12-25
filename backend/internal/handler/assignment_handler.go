package handler

import (
	"net/http"
	"strconv"
	"time"

	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/middleware"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"

	"github.com/gin-gonic/gin"
)

// AssignmentHandler handles HTTP requests for assignments
type AssignmentHandler struct {
	assignmentService *services.AssignmentService
	authz             *services.AuthzService
}

// NewAssignmentHandler creates a new AssignmentHandler
func NewAssignmentHandler(assignmentService *services.AssignmentService, authz *services.AuthzService) *AssignmentHandler {
	return &AssignmentHandler{assignmentService: assignmentService, authz: authz}
}

// CreateAssignment handles the creation of a new assignment
// @Summary Create a new assignment
// @Description Creates a new assignment with the provided details.
// @Tags assignments
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param assignment body dtos.CreateAssignmentRequestDTO true "Assignment details"
// @Success 201 {object} dtos.APIResponse{data=dtos.AssignmentDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /assignments [post]
func (h *AssignmentHandler) CreateAssignment(c *gin.Context) {
	var req dtos.CreateAssignmentRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error()) // FIX
		return
	}

	// Authorization: admin allowed; teacher must own the course
	role := middleware.GetRole(c)
	userID := middleware.GetUserID(c)
	if role == "guru" {
		if err := h.authz.CheckCourseOwner(c.Request.Context(), userID, req.CourseID); err != nil {
			if err == services.ErrForbidden {
				response.Error(c, http.StatusForbidden, "forbidden")
				return
			}
			response.Error(c, http.StatusInternalServerError, err.Error())
			return
		}
	}

	assignment, err := h.assignmentService.CreateAssignment(c.Request.Context(), req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error()) // FIX
		return
	}

	response.Success(c, http.StatusCreated, "Assignment created successfully", assignment)
}

// GetAssignmentByID retrieves an assignment by its ID
// @Summary Get assignment by ID
// @Description Retrieves a single assignment by its unique identifier.
// @Tags assignments
// @Security BearerAuth
// @Produce json
// @Param id path int true "Assignment ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.AssignmentDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /assignments/{id} [get]
func (h *AssignmentHandler) GetAssignmentByID(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid assignment ID") // FIX
		return
	}

	assignment, err := h.assignmentService.GetAssignmentByID(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error()) // FIX
		return
	}
	if assignment.ID == 0 { // Assuming ID will be 0 if not found
		response.Error(c, http.StatusNotFound, "Assignment not found") // FIX
		return
	}

	response.Success(c, http.StatusOK, "Assignment found successfully", assignment)
}

// GetAssignments retrieves a list of assignments
// @Summary Get all assignments
// @Description Retrieves a list of all assignments, with optional filtering by course ID.
// @Tags assignments
// @Security BearerAuth
// @Produce json
// @Param course_id query int false "Filter by Course ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.ListAssignmentsResponseDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /assignments [get]
func (h *AssignmentHandler) GetAssignments(c *gin.Context) {
	var courseIDFilter *int64
	if c.Query("course_id") != "" {
		id, err := strconv.ParseInt(c.Query("course_id"), 10, 64)
		if err != nil {
			response.Error(c, http.StatusBadRequest, "Invalid course ID filter") // FIX
			return
		}
		courseIDFilter = &id
	}

	// Get user ID and role from context (set by authentication middleware)
	userID := middleware.GetUserID(c)
	userRole := middleware.GetRole(c)

	// Return general assignments list for all roles; service applies visibility filter for students
	assignments, err := h.assignmentService.GetAssignments(c.Request.Context(), userID, userRole, courseIDFilter)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error()) // FIX
		return
	}

	response.Success(c, http.StatusOK, "Assignments found successfully", dtos.ListAssignmentsResponseDTO{
		Assignments: assignments,
		Total:       len(assignments),
	})
}

// UpdateAssignment handles the update of an existing assignment
// @Summary Update an assignment
// @Description Updates an existing assignment identified by its ID.
// @Tags assignments
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param id path int true "Assignment ID"
// @Param assignment body dtos.UpdateAssignmentRequestDTO true "Updated assignment details"
// @Success 200 {object} dtos.APIResponse{data=dtos.AssignmentDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /assignments/{id} [put]
func (h *AssignmentHandler) UpdateAssignment(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid assignment ID") // FIX
		return
	}

	var req dtos.UpdateAssignmentRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error()) // FIX
		return
	}

	// Authorization: admin allowed; teacher must own the assignment
	role := middleware.GetRole(c)
	userID := middleware.GetUserID(c)
	if role == "guru" {
		if err := h.authz.CheckAssignmentOwner(c.Request.Context(), userID, id); err != nil {
			if err == services.ErrForbidden {
				response.Error(c, http.StatusForbidden, "forbidden")
				return
			}
			response.Error(c, http.StatusInternalServerError, err.Error())
			return
		}
	}

	assignment, err := h.assignmentService.UpdateAssignment(c.Request.Context(), id, req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error()) // FIX
		return
	}
	if assignment.ID == 0 { // Assuming ID will be 0 if not found
		response.Error(c, http.StatusNotFound, "Assignment not found") // FIX
		return
	}

	response.Success(c, http.StatusOK, "Assignment updated successfully", assignment)
}

// DeleteAssignment handles the deletion of an assignment
// @Summary Delete an assignment
// @Description Deletes an assignment by its ID.
// @Tags assignments
// @Security BearerAuth
// @Produce json
// @Param id path int true "Assignment ID"
// @Success 204 {object} dtos.APIResponse{data=dtos.NullData}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /assignments/{id} [delete]
func (h *AssignmentHandler) DeleteAssignment(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid assignment ID") // FIX
		return
	}

	// Authorization
	role := middleware.GetRole(c)
	userID := middleware.GetUserID(c)
	if role == "guru" {
		if err := h.authz.CheckAssignmentOwner(c.Request.Context(), userID, id); err != nil {
			if err == services.ErrForbidden {
				response.Error(c, http.StatusForbidden, "forbidden")
				return
			}
			response.Error(c, http.StatusInternalServerError, err.Error())
			return
		}
	}

	err = h.assignmentService.DeleteAssignment(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error()) // FIX
		return
	}

	response.Success(c, http.StatusNoContent, "Assignment deleted successfully", nil)
}

// CreateStudentAssignment handles recording a student starting an assignment
// @Summary Record student starting an assignment
// @Description Records that a student has started a specific assignment.
// @Tags assignments
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param id path int true "Assignment ID"
// @Param studentAssignment body dtos.CreateStudentAssignmentRequestDTO false "Optional payload; assignment_id and student_id are derived"
// @Success 201 {object} dtos.APIResponse{data=dtos.StudentAssignmentDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 409 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /assignments/{id}/start [post]
func (h *AssignmentHandler) CreateStudentAssignment(c *gin.Context) {
	// Path: /assignments/:id/start → :id is assignment_id
	assignmentID, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid assignment ID")
		return
	}

	// Derive current user (student) from JWT
	userID := middleware.GetUserID(c)

	var req dtos.CreateStudentAssignmentRequestDTO
	// Body is optional; we ignore any provided IDs for safety
	_ = c.ShouldBindJSON(&req)
	req.AssignmentID = assignmentID
	req.StudentID = userID
	if req.Status == "" {
		req.Status = "in_progress"
	}
	// Submission should be nil on start
	req.Submission = nil

	studentAssignment, err := h.assignmentService.CreateStudentAssignment(c.Request.Context(), req)
	if err != nil {
		if err == services.ErrAlreadyStarted {
			response.Error(c, http.StatusConflict, "assignment already started")
			return
		}
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Student assignment created successfully", studentAssignment)
}

// GetStudentAssignmentByID retrieves a specific student's assignment record by ID.
//
// Deprecated: this handler remains for backward compatibility but is not routed
// in `cmd/api/main.go`.
func (h *AssignmentHandler) GetStudentAssignmentByID(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid student assignment ID") // FIX
		return
	}

	studentAssignment, err := h.assignmentService.GetStudentAssignmentByID(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error()) // FIX
		return
	}
	if studentAssignment.ID == 0 { // Assuming ID will be 0 if not found
		response.Error(c, http.StatusNotFound, "Student assignment not found") // FIX
		return
	}

	response.Success(c, http.StatusOK, "Student assignment found successfully", studentAssignment)
}

// GetStudentAssignmentsList retrieves a list of student assignments for a specific student
// @Summary Get student assignments list
// @Description Retrieves a list of all assignments for a specific student, including their progress.
// @Tags assignments
// @Security BearerAuth
// @Produce json
// @Param user_id path int true "User ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.ListStudentAssignmentsResponseDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /students/{user_id}/assignments [get]
func (h *AssignmentHandler) GetStudentAssignmentsList(c *gin.Context) {
	studentID, err := strconv.ParseInt(c.Param("user_id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid student ID") // FIX
		return
	}

	studentAssignments, err := h.assignmentService.GetStudentAssignmentsList(c.Request.Context(), studentID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error()) // FIX
		return
	}

	response.Success(c, http.StatusOK, "Student assignments found successfully", dtos.ListStudentAssignmentsResponseDTO{
		StudentAssignments: studentAssignments,
		Total:              len(studentAssignments),
	})
}

// GetStudentAssignmentsByAssignmentID retrieves all student records for a specific assignment
// @Summary Get assignment submissions
// @Description Retrieves all student assignment records for a specific assignment.
// @Tags assignments
// @Security BearerAuth
// @Produce json
// @Param id path int true "Assignment ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.ListStudentAssignmentsResponseDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /assignments/{id}/submissions [get]
func (h *AssignmentHandler) GetStudentAssignmentsByAssignmentID(c *gin.Context) {
	// Route uses /assignments/:id/submissions
	assignmentID, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid assignment ID") // FIX
		return
	}

	// Authorization: admin or owning teacher
	role := middleware.GetRole(c)
	userID := middleware.GetUserID(c)
	if role == "guru" {
		if err := h.authz.CheckAssignmentOwner(c.Request.Context(), userID, assignmentID); err != nil {
			if err == services.ErrForbidden {
				response.Error(c, http.StatusForbidden, "forbidden")
				return
			}
			response.Error(c, http.StatusInternalServerError, err.Error())
			return
		}
	}

	studentAssignments, err := h.assignmentService.GetStudentAssignmentsByAssignmentID(c.Request.Context(), assignmentID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error()) // FIX
		return
	}

	response.Success(c, http.StatusOK, "Student assignments found successfully", dtos.ListStudentAssignmentsResponseDTO{
		StudentAssignments: studentAssignments,
		Total:              len(studentAssignments),
	})
}

// UpdateStudentAssignment handles the update of a student's assignment record
// @Summary Submit an assignment or grade a submission
// @Description Students submit via `/assignments/{id}/submit`; teachers/admin grade via `/assignments/{id}/submissions/{student_assignment_id}`.
// @Tags assignments
// @Security BearerAuth
// @Accept json
// @Produce json
// @Param id path int true "Assignment ID"
// @Param student_assignment_id path int false "Student Assignment ID (grading route only)"
// @Param studentAssignment body dtos.UpdateStudentAssignmentRequestDTO true "Student assignment update"
// @Success 200 {object} dtos.APIResponse{data=dtos.StudentAssignmentDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /assignments/{id}/submit [post]
// @Router /assignments/{id}/submissions/{student_assignment_id} [put]
func (h *AssignmentHandler) UpdateStudentAssignment(c *gin.Context) {
	// Two route patterns call this handler:
	// 1) POST /assignments/:id/submit            → :id is assignment_id (student self-submit)
	// 2) PUT /assignments/:id/submissions/:student_assignment_id → grading by admin/teacher; use :student_assignment_id

	// If student_assignment_id path param exists, treat as grading update
	if sid := c.Param("student_assignment_id"); sid != "" {
		studentAssignmentID, err := strconv.ParseInt(sid, 10, 64)
		if err != nil {
			response.Error(c, http.StatusBadRequest, "Invalid student assignment ID")
			return
		}

		// Authorization for grading: admin or owning teacher
		role := middleware.GetRole(c)
		userID := middleware.GetUserID(c)
		if role == "guru" {
			// assignment id is present in route as :id
			assignmentID, _ := strconv.ParseInt(c.Param("id"), 10, 64)
			if assignmentID > 0 {
				if err := h.authz.CheckAssignmentOwner(c.Request.Context(), userID, assignmentID); err != nil {
					if err == services.ErrForbidden {
						response.Error(c, http.StatusForbidden, "forbidden")
						return
					}
					response.Error(c, http.StatusInternalServerError, err.Error())
					return
				}
			}
		}
		var req dtos.UpdateStudentAssignmentRequestDTO
		if err := c.ShouldBindJSON(&req); err != nil {
			response.Error(c, http.StatusBadRequest, err.Error())
			return
		}
		// Set grader_user_id from JWT if not provided
		if req.GraderUserID == nil {
			u := middleware.GetUserID(c)
			req.GraderUserID = &u
		}
		// Default graded_at to now on grading if not provided
		if req.GradedAt == nil {
			now := time.Now()
			req.GradedAt = &now
		}
		studentAssignment, err := h.assignmentService.UpdateStudentAssignment(c.Request.Context(), studentAssignmentID, req)
		if err != nil {
			response.Error(c, http.StatusInternalServerError, err.Error())
			return
		}
		if studentAssignment.ID == 0 {
			response.Error(c, http.StatusNotFound, "Student assignment not found")
			return
		}
		response.Success(c, http.StatusOK, "Student assignment updated successfully", studentAssignment)
		return
	}

	// Otherwise, it's student submit by assignment ID
	assignmentID, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid assignment ID")
		return
	}
	userID := middleware.GetUserID(c)

	var req dtos.UpdateStudentAssignmentRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}
	// Force status to completed on submit if not provided
	completed := "completed"
	if req.Status == nil {
		req.Status = &completed
	}

	studentAssignment, err := h.assignmentService.SubmitOwnAssignment(c.Request.Context(), userID, assignmentID, req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	if studentAssignment.ID == 0 {
		response.Error(c, http.StatusNotFound, "Student assignment not found")
		return
	}
	response.Success(c, http.StatusOK, "Assignment submitted successfully", studentAssignment)
}

// DeleteStudentAssignment handles the deletion of a student's assignment record
// @Summary Delete an assignment submission
// @Description Deletes a student's assignment record by its ID.
// @Tags assignments
// @Security BearerAuth
// @Produce json
// @Param id path int true "Assignment ID"
// @Param student_assignment_id path int true "Student Assignment ID"
// @Success 204 {object} dtos.APIResponse{data=dtos.NullData}
// @Failure 400 {object} dtos.APIError
// @Failure 401 {object} dtos.APIError
// @Failure 403 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /assignments/{id}/submissions/{student_assignment_id} [delete]
func (h *AssignmentHandler) DeleteStudentAssignment(c *gin.Context) {
	// Support both top-level delete (/student-assignments/:id) and submissions route delete (/assignments/:id/submissions/:student_assignment_id)
	idStr := c.Param("student_assignment_id")
	if idStr == "" {
		idStr = c.Param("id")
	}
	id, err := strconv.ParseInt(idStr, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid student assignment ID") // FIX
		return
	}

	err = h.assignmentService.DeleteStudentAssignment(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error()) // FIX
		return
	}

	response.Success(c, http.StatusNoContent, "Student assignment deleted successfully", nil)
}
