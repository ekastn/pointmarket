package handler

import (
	"database/sql"
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"strconv"

	"github.com/gin-gonic/gin"
)

type MaterialHandler struct {
	materialService services.MaterialService
}

func NewMaterialHandler(materialService services.MaterialService) *MaterialHandler {
	return &MaterialHandler{materialService: materialService}
}

// GetAllMaterials handles fetching all materials
func (h *MaterialHandler) GetAllMaterials(c *gin.Context) {
	materials, err := h.materialService.GetAllMaterials()
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Materials retrieved successfully", materials)
}

// GetMaterialByID handles fetching a material by ID
func (h *MaterialHandler) GetMaterialByID(c *gin.Context) {
	id, err := strconv.ParseUint(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid material ID")
		return
	}
	material, err := h.materialService.GetMaterialByID(uint(id))
	if err == sql.ErrNoRows {
		response.Error(c, http.StatusNotFound, "Material not found")
		return
	}
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Material retrieved successfully", material)
}

// CreateMaterial handles creating a new material
func (h *MaterialHandler) CreateMaterial(c *gin.Context) {
	var req dtos.CreateMaterialRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}
	teacherID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "Teacher ID not found in context")
		return
	}

	err := h.materialService.CreateMaterial(req, teacherID.(uint))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusCreated, "Material created successfully", nil)
}

// UpdateMaterial handles updating an existing material
func (h *MaterialHandler) UpdateMaterial(c *gin.Context) {
	id, err := strconv.ParseUint(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid material ID")
		return
	}
	var req dtos.UpdateMaterialRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	err = h.materialService.UpdateMaterial(uint(id), req)
	if err == sql.ErrNoRows {
		response.Error(c, http.StatusNotFound, "Material not found")
		return
	}
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Material updated successfully", nil)
}

// DeleteMaterial handles deleting a material (sets status to inactive)
func (h *MaterialHandler) DeleteMaterial(c *gin.Context) {
	id, err := strconv.ParseUint(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid material ID")
		return
	}
	err = h.materialService.DeleteMaterial(uint(id))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Material deleted successfully", nil)
}
