package handler

import (
	"net/http"
	"strconv"

	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/middleware"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"

	"github.com/gin-gonic/gin"
)

// AssignmentHandler handles HTTP requests for assignments
type AssignmentHandler struct {
	assignmentService *services.AssignmentService
}

// NewAssignmentHandler creates a new AssignmentHandler
func NewAssignmentHandler(assignmentService *services.AssignmentService) *AssignmentHandler {
	return &AssignmentHandler{assignmentService: assignmentService}
}

// CreateAssignment handles the creation of a new assignment
// @Summary Create a new assignment
// @Description Creates a new assignment with the provided details.
// @Tags Assignments
// @Accept json
// @Produce json
// @Param assignment body dtos.CreateAssignmentRequestDTO true "Assignment details"
// @Success 201 {object} dtos.AssignmentDTO
// @Failure 400 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
// @Router /assignments [post]
func (h *AssignmentHandler) CreateAssignment(c *gin.Context) {
	var req dtos.CreateAssignmentRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error()) // FIX
		return
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
// @Tags Assignments
// @Produce json
// @Param id path int true "Assignment ID"
// @Success 200 {object} dtos.AssignmentDTO
// @Failure 404 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
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
// @Tags Assignments
// @Produce json
// @Param course_id query int false "Filter by Course ID"
// @Success 200 {object} dtos.ListAssignmentsResponseDTO
// @Failure 500 {object} dtos.ErrorResponse
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
// @Tags Assignments
// @Accept json
// @Produce json
// @Param id path int true "Assignment ID"
// @Param assignment body dtos.UpdateAssignmentRequestDTO true "Updated assignment details"
// @Success 200 {object} dtos.AssignmentDTO
// @Failure 400 {object} dtos.ErrorResponse
// @Failure 404 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
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
// @Tags Assignments
// @Produce json
// @Param id path int true "Assignment ID"
// @Success 204 "No Content"
// @Failure 400 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
// @Router /assignments/{id} [delete]
func (h *AssignmentHandler) DeleteAssignment(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid assignment ID") // FIX
		return
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
// @Tags Student Assignments
// @Accept json
// @Produce json
// @Param studentAssignment body dtos.CreateStudentAssignmentRequestDTO true "Student Assignment details"
// @Success 201 {object} dtos.StudentAssignmentDTO
// @Failure 400 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
// @Router /student-assignments [post]
func (h *AssignmentHandler) CreateStudentAssignment(c *gin.Context) {
	var req dtos.CreateStudentAssignmentRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error()) // FIX
		return
	}

	studentAssignment, err := h.assignmentService.CreateStudentAssignment(c.Request.Context(), req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error()) // FIX
		return
	}

	response.Success(c, http.StatusCreated, "Student assignment created successfully", studentAssignment)
}

// GetStudentAssignmentByID retrieves a specific student's assignment record by ID
// @Summary Get student assignment by ID
// @Description Retrieves a specific student's assignment record by its unique identifier.
// @Tags Student Assignments
// @Produce json
// @Param id path int true "Student Assignment ID"
// @Success 200 {object} dtos.StudentAssignmentDTO
// @Failure 404 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
// @Router /student-assignments/{id} [get]
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
// @Tags Student Assignments
// @Produce json
// @Param student_id path int true "Student ID"
// @Success 200 {object} dtos.ListStudentAssignmentsResponseDTO
// @Failure 500 {object} dtos.ErrorResponse
// @Router /students/{student_id}/assignments [get]
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
// @Summary Get student assignments by assignment ID
// @Description Retrieves all student assignment records for a specific assignment.
// @Tags Student Assignments
// @Produce json
// @Param assignment_id path int true "Assignment ID"
// @Success 200 {object} dtos.ListStudentAssignmentsResponseDTO
// @Failure 500 {object} dtos.ErrorResponse
// @Router /assignments/{assignment_id}/submissions [get]
func (h *AssignmentHandler) GetStudentAssignmentsByAssignmentID(c *gin.Context) {
	assignmentID, err := strconv.ParseInt(c.Param("assignment_id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid assignment ID") // FIX
		return
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
// @Summary Update a student's assignment record
// @Description Updates an existing student's assignment record identified by its ID.
// @Tags Student Assignments
// @Accept json
// @Produce json
// @Param id path int true "Student Assignment ID"
// @Param studentAssignment body dtos.UpdateStudentAssignmentRequestDTO true "Updated student assignment details"
// @Success 200 {object} dtos.StudentAssignmentDTO
// @Failure 400 {object} dtos.ErrorResponse
// @Failure 404 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
// @Router /student-assignments/{id} [put]
func (h *AssignmentHandler) UpdateStudentAssignment(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid student assignment ID") // FIX
		return
	}

	var req dtos.UpdateStudentAssignmentRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error()) // FIX
		return
	}

	studentAssignment, err := h.assignmentService.UpdateStudentAssignment(c.Request.Context(), id, req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error()) // FIX
		return
	}
	if studentAssignment.ID == 0 { // Assuming ID will be 0 if not found
		response.Error(c, http.StatusNotFound, "Student assignment not found") // FIX
		return
	}

	response.Success(c, http.StatusOK, "Student assignment updated successfully", studentAssignment)
}

// DeleteStudentAssignment handles the deletion of a student's assignment record
// @Summary Delete a student's assignment record
// @Description Deletes a student's assignment record by its ID.
// @Tags Student Assignments
// @Produce json
// @Param id path int true "Student Assignment ID"
// @Success 204 "No Content"
// @Failure 400 {object} dtos.ErrorResponse
// @Failure 500 {object} dtos.ErrorResponse
// @Router /student-assignments/{id} [delete]
func (h *AssignmentHandler) DeleteStudentAssignment(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
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
