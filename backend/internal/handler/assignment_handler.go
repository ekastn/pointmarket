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
	assignments, err := h.assignmentService.GetAllAssignments()
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	var assignmentListDTO dtos.AssignmentListResponse
	assignmentListDTO.FromAssignments(assignments)
	response.Success(c, http.StatusOK, "Assignments retrieved successfully", assignmentListDTO)
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
	id, _ := strconv.Atoi(c.Param("id"))
	assignment, err := h.assignmentService.GetAssignmentByID(uint(id))
	if err != nil {
		response.Error(c, http.StatusNotFound, "Assignment not found")
		return
	}
	var assignmentDTO dtos.AssignmentResponse
	assignmentDTO.FromAssignment(assignment)
	response.Success(c, http.StatusOK, "Assignment retrieved successfully", assignmentDTO)
}

func (h *AssignmentHandler) UpdateAssignment(c *gin.Context) {
	id, _ := strconv.Atoi(c.Param("id"))
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
