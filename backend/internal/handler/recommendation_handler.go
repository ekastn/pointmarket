package handler

import (
	"io"
	"log"
	"net/http"
	"net/url"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"strconv"
	"sync"

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

// Admin: Items management
func (h *RecommendationHandler) AdminListItems(c *gin.Context) {
	q := url.Values{}
	for k, v := range c.Request.URL.Query() {
		for _, val := range v {
			q.Add(k, val)
		}
	}

	var items, stats map[string]interface{}
	var itemsErr, statsErr error
	var wg sync.WaitGroup
	wg.Add(2)

	go func() {
		defer wg.Done()
		items, itemsErr = h.recService.AdminListItems(c.Request.Context(), q)
	}()

	go func() {
		defer wg.Done()
		stats, statsErr = h.recService.AdminGetItemStats(c.Request.Context())
	}()

	wg.Wait()

	if itemsErr != nil {
		response.Error(c, http.StatusBadGateway, itemsErr.Error())
		return
	}
	if statsErr != nil {
		// Log this but don't fail the request, the main data is the item list
		log.Printf("WARN: failed to get recommendation item stats: %v", statsErr)
	}

	enriched := h.recService.EnrichItemRefsWithTitles(c.Request.Context(), items)
	enriched["stats"] = stats // Add stats to the final response

	response.Success(c, http.StatusOK, "ok", enriched)
}

func (h *RecommendationHandler) AdminCreateItems(c *gin.Context) {
	body, _ := io.ReadAll(c.Request.Body)
	out, err := h.recService.AdminCreateItems(c.Request.Context(), body)
	if err != nil {
		response.Error(c, http.StatusBadGateway, err.Error())
		return
	}
	response.Success(c, http.StatusCreated, "created", out)
}

func (h *RecommendationHandler) AdminUpdateItem(c *gin.Context) {
	idStr := c.Param("id")
	id, err := strconv.ParseInt(idStr, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "invalid id")
		return
	}
	body, _ := io.ReadAll(c.Request.Body)
	out, err := h.recService.AdminUpdateItem(c.Request.Context(), id, body)
	if err != nil {
		response.Error(c, http.StatusBadGateway, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "updated", out)
}

func (h *RecommendationHandler) AdminToggleItem(c *gin.Context) {
	idStr := c.Param("id")
	id, err := strconv.ParseInt(idStr, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "invalid id")
		return
	}
	body, _ := io.ReadAll(c.Request.Body)
	out, err := h.recService.AdminToggleItem(c.Request.Context(), id, body)
	if err != nil {
		response.Error(c, http.StatusBadGateway, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "toggled", out)
}

func (h *RecommendationHandler) AdminDeleteItem(c *gin.Context) {
	idStr := c.Param("id")
	id, err := strconv.ParseInt(idStr, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "invalid id")
		return
	}
	force := c.DefaultQuery("force", "0") == "1"
	if err := h.recService.AdminDeleteItem(c.Request.Context(), id, force); err != nil {
		response.Error(c, http.StatusBadGateway, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "deleted", nil)
}

// Admin: States typeahead
func (h *RecommendationHandler) AdminListStates(c *gin.Context) {
	q := url.Values{}
	for k, v := range c.Request.URL.Query() {
		for _, val := range v {
			q.Add(k, val)
		}
	}
	out, err := h.recService.AdminListStates(c.Request.Context(), q)
	if err != nil {
		response.Error(c, http.StatusBadGateway, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "ok", out)
}

// Admin: Refs search typeahead
func (h *RecommendationHandler) AdminSearchRefs(c *gin.Context) {
	q := url.Values{}
	for k, v := range c.Request.URL.Query() {
		for _, val := range v {
			q.Add(k, val)
		}
	}
	out, err := h.recService.AdminSearchRefs(c.Request.Context(), q)
	if err != nil {
		response.Error(c, http.StatusBadGateway, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "ok", out)
}

// Admin: Unique States CRUD
func (h *RecommendationHandler) AdminStatesList(c *gin.Context) {
	q := url.Values{}
	for k, v := range c.Request.URL.Query() {
		for _, val := range v {
			q.Add(k, val)
		}
	}
	out, err := h.recService.AdminStatesList(c.Request.Context(), q)
	if err != nil {
		response.Error(c, http.StatusBadGateway, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "ok", out)
}

func (h *RecommendationHandler) AdminStatesCreate(c *gin.Context) {
	body, _ := io.ReadAll(c.Request.Body)
	out, err := h.recService.AdminStatesCreate(c.Request.Context(), body)
	if err != nil {
		response.Error(c, http.StatusBadGateway, err.Error())
		return
	}
	// backend returns 201; normalize to success with data
	response.Success(c, http.StatusCreated, "created", out)
}

func (h *RecommendationHandler) AdminStatesUpdate(c *gin.Context) {
	idStr := c.Param("id")
	id, err := strconv.ParseInt(idStr, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "invalid id")
		return
	}
	body, _ := io.ReadAll(c.Request.Body)
	out, err := h.recService.AdminStatesUpdate(c.Request.Context(), id, body)
	if err != nil {
		response.Error(c, http.StatusBadGateway, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "updated", out)
}

func (h *RecommendationHandler) AdminStatesDelete(c *gin.Context) {
	idStr := c.Param("id")
	id, err := strconv.ParseInt(idStr, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "invalid id")
		return
	}
	force := c.DefaultQuery("force", "0") == "1"
	if err := h.recService.AdminStatesDelete(c.Request.Context(), id, force); err != nil {
		response.Error(c, http.StatusBadGateway, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "deleted", nil)
}
