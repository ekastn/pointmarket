package handler

import (
	"log"
	"net/http"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"strconv"

	"github.com/gin-gonic/gin"
)

type RecommendationHandler struct {
	recService *services.RecommendationService
	stdService *services.StudentService
}

func NewRecommendationHandler(recService *services.RecommendationService, stdService *services.StudentService) *RecommendationHandler {
	return &RecommendationHandler{recService: recService, stdService: stdService}
}

// GetStudentRecommendations godoc
func (h *RecommendationHandler) GetStudentRecommendations(c *gin.Context) {
	userID := c.Param("user_id")
	if userID == "" {
		response.Error(c, http.StatusBadRequest, "missing user_id")
		return
	}

	userIDInt, err := strconv.ParseInt(userID, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "invalid user_id")
		return
	}

	student, err := h.stdService.GetByUserID(c.Request.Context(), userIDInt)
	if err != nil {
		response.Error(c, http.StatusNotFound, "student not found")
		return
	}

	recs, err := h.recService.GetStudentRecommendations(c.Request.Context(), student.StudentID)
	if err != nil {
		response.Error(c, http.StatusBadGateway, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Recommendations retrieved successfully", recs)
}

// GetRecommendationsTrace godoc (admin-only)
func (h *RecommendationHandler) GetRecommendationsTrace(c *gin.Context) {
	studentID := c.Query("student_id")
	if studentID == "" {
		response.Error(c, http.StatusBadRequest, "missing student_id")
		return
	}
	payload, err := h.recService.GetStudentRecommendationsTrace(c.Request.Context(), studentID)
	if err != nil {
		response.Error(c, http.StatusBadGateway, err.Error())
		return
	}

	log.Printf("trace: %v", payload)

	response.Success(c, http.StatusOK, "Trace retrieved successfully", payload)
}
