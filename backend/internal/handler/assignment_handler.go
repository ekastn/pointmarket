package handler

import (
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"strconv"

	"github.com/gin-gonic/gin"
)

type AssignmentHandler struct {
	assignmentService services.AssignmentService
}

func NewAssignmentHandler(assignmentService services.AssignmentService) *AssignmentHandler {
	return &AssignmentHandler{assignmentService: assignmentService}
}

func (h *AssignmentHandler) GetAllAssignments(c *gin.Context) {
	userID, exists := c.Get("userID")
	var userIDPtr *uint
	if exists {
		val := userID.(uint)
		userIDPtr = &val
	}
	assignments, err := h.assignmentService.GetAllAssignments(userIDPtr)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Assignments retrieved successfully", assignments)
}

func (h *AssignmentHandler) CreateAssignment(c *gin.Context) {
	var createDTO dtos.CreateAssignmentRequest
	if err := c.ShouldBindJSON(&createDTO); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	userID, _ := c.Get("userID")
	assignment, err := h.assignmentService.CreateAssignment(createDTO, userID.(uint))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	var assignmentDTO dtos.AssignmentResponse
	assignmentDTO.FromAssignment(assignment)
	response.Success(c, http.StatusCreated, "Assignment created successfully", assignmentDTO)
}

func (h *AssignmentHandler) GetAssignmentByID(c *gin.Context) {
	id, err := strconv.ParseUint(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid assignment ID")
		return
	}
	userID, exists := c.Get("userID")
	var userIDPtr *uint
	if exists {
		val := userID.(uint)
		userIDPtr = &val
	}

	assignment, err := h.assignmentService.GetAssignmentByID(uint(id), userIDPtr)
	if err != nil {
		response.Error(c, http.StatusNotFound, "Assignment not found")
		return
	}
	response.Success(c, http.StatusOK, "Assignment retrieved successfully", assignment)
}

func (h *AssignmentHandler) UpdateAssignment(c *gin.Context) {
	id, err := strconv.ParseUint(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid assignment ID")
		return
	}
	var updateDTO dtos.UpdateAssignmentRequest
	if err := c.ShouldBindJSON(&updateDTO); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	assignment, err := h.assignmentService.UpdateAssignment(uint(id), updateDTO)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	var assignmentDTO dtos.AssignmentResponse
	assignmentDTO.FromAssignment(assignment)
	response.Success(c, http.StatusOK, "Assignment updated successfully", assignmentDTO)
}

func (h *AssignmentHandler) DeleteAssignment(c *gin.Context) {
	id, _ := strconv.Atoi(c.Param("id"))
	err := h.assignmentService.DeleteAssignment(uint(id))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Assignment deleted successfully", nil)
}

// StartAssignment handles marking an assignment as in_progress for a student
func (h *AssignmentHandler) StartAssignment(c *gin.Context) {
	assignmentID, err := strconv.ParseUint(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid assignment ID")
		return
	}
	userID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User not found in context")
		return
	}

	err = h.assignmentService.StartStudentAssignment(userID.(uint), uint(assignmentID))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Assignment started successfully", nil)
}

// SubmitAssignment handles the submission of an assignment by a student
func (h *AssignmentHandler) SubmitAssignment(c *gin.Context) {
	assignmentID, err := strconv.ParseUint(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid assignment ID")
		return
	}
	userID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User not found in context")
		return
	}

	var req struct {
		SubmissionContent string `json:"submission_content" binding:"required"`
	}
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	score, err := h.assignmentService.SubmitStudentAssignment(userID.(uint), uint(assignmentID), req.SubmissionContent)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Assignment submitted successfully", gin.H{"score": score})
}
