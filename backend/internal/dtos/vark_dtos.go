package dtos

import "pointmarket/backend/internal/models"

// ==================
//     Requests
// ==================

type SubmitVARKRequest struct {
	Answers map[string]string `json:"answers" binding:"required"`
}

// ==================
//     Responses
// ==================

type VARKResultResponse struct {
	VarkType    string  `json:"vark_type"`
	Description *string `json:"description"`
}

// FromVARKResult converts a models.VARKResult to a VARKResultResponse DTO.
func (dto *VARKResultResponse) FromVARKResult(result models.VARKResult) {
	dto.VarkType = result.DominantStyle
	dto.Description = result.LearningPreference
}