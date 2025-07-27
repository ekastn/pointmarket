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
	VisualScore      int     `json:"visual_score"`
	AuditoryScore    int     `json:"auditory_score"`
	ReadingScore     int     `json:"reading_score"`
	KinestheticScore int     `json:"kinesthetic_score"`
	DominantStyle    string  `json:"dominant_style"`
	LearningPreference *string `json:"learning_preference"`
}

// FromVARKResult converts a models.VARKResult to a VARKResultResponse DTO.
func (dto *VARKResultResponse) FromVARKResult(result models.VARKResult) {
	dto.VisualScore = result.VisualScore
	dto.AuditoryScore = result.AuditoryScore
	dto.ReadingScore = result.ReadingScore
	dto.KinestheticScore = result.KinestheticScore
	dto.DominantStyle = result.DominantStyle
	dto.LearningPreference = result.LearningPreference
}