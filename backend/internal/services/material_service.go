package services

import (
	"database/sql"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store"
)

// MaterialService provides business logic for materials
type MaterialService struct {
	materialStore *store.MaterialStore
}

// NewMaterialService creates a new MaterialService
func NewMaterialService(materialStore *store.MaterialStore) *MaterialService {
	return &MaterialService{materialStore: materialStore}
}

// GetAllMaterials retrieves all materials
func (s *MaterialService) GetAllMaterials() ([]models.Material, error) {
	return s.materialStore.GetAllMaterials()
}

// GetMaterialByID retrieves a material by its ID
func (s *MaterialService) GetMaterialByID(id uint) (*models.Material, error) {
	return s.materialStore.GetMaterialByID(int(id))
}

// CreateMaterial creates a new material
func (s *MaterialService) CreateMaterial(req dtos.CreateMaterialRequest, teacherID uint) error {
	material := models.Material{
		Title:       req.Title,
		Description: &req.Description,
		Subject:     req.Subject,
		TeacherID:   int(teacherID),
		FilePath:    &req.FilePath,
		FileType:    &req.FileType,
		Status:      "active",
	}
	return s.materialStore.CreateMaterial(&material)
}

// UpdateMaterial updates an existing material
func (s *MaterialService) UpdateMaterial(id uint, req dtos.UpdateMaterialRequest) error {
	material, err := s.materialStore.GetMaterialByID(int(id))
	if err != nil {
		return err
	}
	if material == nil {
		return sql.ErrNoRows // Material not found
	}

	material.Title = req.Title
	material.Description = &req.Description
	material.Subject = req.Subject
	material.FilePath = &req.FilePath
	material.FileType = &req.FileType
	material.Status = req.Status

	return s.materialStore.UpdateMaterial(material)
}

// DeleteMaterial deletes a material (sets status to inactive)
func (s *MaterialService) DeleteMaterial(id uint) error {
	return s.materialStore.DeleteMaterial(int(id))
}
