package dtos

import "pointmarket/backend/internal/models"

// ==================
//     Requests
// ==================

type CreateMaterialRequest struct {
	Title       string `json:"title" binding:"required"`
	Description string `json:"description"`
	Subject     string `json:"subject" binding:"required"`
	FilePath    string `json:"file_path"`
	FileType    string `json:"file_type"`
}

type UpdateMaterialRequest struct {
	Title       string `json:"title"`
	Description string `json:"description"`
	Subject     string `json:"subject"`
	FilePath    string `json:"file_path"`
	FileType    string `json:"file_type"`
	Status      string `json:"status"`
}

// ==================
//     Responses
// ==================

type MaterialResponse struct {
	ID          int     `json:"id"`
	Title       string  `json:"title"`
	Description *string `json:"description"`
	Subject     string  `json:"subject"`
	TeacherID   int     `json:"teacher_id"`
	FilePath    *string `json:"file_path"`
	FileType    *string `json:"file_type"`
	Status      string  `json:"status"`
}

// FromMaterial converts a models.Material to a MaterialResponse DTO.
func (dto *MaterialResponse) FromMaterial(material models.Material) {
	dto.ID = material.ID
	dto.Title = material.Title
	dto.Description = material.Description
	dto.Subject = material.Subject
	dto.TeacherID = material.TeacherID
	dto.FilePath = material.FilePath
	dto.FileType = material.FileType
	dto.Status = material.Status
}
