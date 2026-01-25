package handler

import (
	"io"
	"log"
	"net/http"
	"net/url"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"strconv"
	"sync"

	"github.com/gin-gonic/gin"
)

var _ = dtos.APIResponse{}

type RecommendationHandler struct {
	recService *services.RecommendationService
	stdService *services.StudentService
}

func NewRecommendationHandler(recService *services.RecommendationService, stdService *services.StudentService) *RecommendationHandler {
	return &RecommendationHandler{recService: recService, stdService: stdService}
}

// GetStudentRecommendations godoc
// @Summary Get student recommendations
// @Description Gets recommendation actions/items for the student (auth required)
// @Tags recommendations
// @Produce json
// @Security BearerAuth
// @Param user_id path int true "User ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.StudentRecommendationsDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 404 {object} dtos.APIError
// @Failure 502 {object} dtos.APIError
// @Router /students/{user_id}/recommendations [get]
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

// GetRecommendationsTrace godoc
// @Summary Get recommendation trace payload
// @Description Returns upstream trace payload for evaluator/debugging (admin-only)
// @Tags recommendations
// @Produce json
// @Security BearerAuth
// @Param student_id query string true "Student ID"
// @Success 200 {object} dtos.APIResponse{data=map[string]interface{}}
// @Failure 400 {object} dtos.APIError
// @Failure 502 {object} dtos.APIError
// @Router /admin/recommendations/trace [get]
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

// AdminListItems godoc
// @Summary List recommendation items
// @Description Lists recommendation items and includes stats (admin-only)
// @Tags recommendations
// @Produce json
// @Security BearerAuth
// @Success 200 {object} dtos.APIResponse{data=map[string]interface{}}
// @Failure 502 {object} dtos.APIError
// @Router /admin/recommendations/items [get]
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

// AdminCreateItems godoc
// @Summary Create recommendation items
// @Description Proxies raw JSON payload to recommendation-service (admin-only)
// @Tags recommendations
// @Accept json
// @Produce json
// @Security BearerAuth
// @Param request body object true "Raw JSON payload"
// @Success 201 {object} dtos.APIResponse{data=map[string]interface{}}
// @Failure 502 {object} dtos.APIError
// @Router /admin/recommendations/items [post]
func (h *RecommendationHandler) AdminCreateItems(c *gin.Context) {
	body, _ := io.ReadAll(c.Request.Body)
	out, err := h.recService.AdminCreateItems(c.Request.Context(), body)
	if err != nil {
		response.Error(c, http.StatusBadGateway, err.Error())
		return
	}
	response.Success(c, http.StatusCreated, "created", out)
}

// AdminUpdateItem godoc
// @Summary Update recommendation item
// @Description Proxies raw JSON payload to recommendation-service (admin-only)
// @Tags recommendations
// @Accept json
// @Produce json
// @Security BearerAuth
// @Param id path int true "Item ID"
// @Param request body object true "Raw JSON payload"
// @Success 200 {object} dtos.APIResponse{data=map[string]interface{}}
// @Failure 400 {object} dtos.APIError
// @Failure 502 {object} dtos.APIError
// @Router /admin/recommendations/items/{id} [put]
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

// AdminToggleItem godoc
// @Summary Toggle recommendation item
// @Description Toggles item active state via recommendation-service (admin-only)
// @Tags recommendations
// @Accept json
// @Produce json
// @Security BearerAuth
// @Param id path int true "Item ID"
// @Param request body object true "Raw JSON payload"
// @Success 200 {object} dtos.APIResponse{data=map[string]interface{}}
// @Failure 400 {object} dtos.APIError
// @Failure 502 {object} dtos.APIError
// @Router /admin/recommendations/items/{id}/toggle [patch]
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

// AdminDeleteItem godoc
// @Summary Delete recommendation item
// @Description Deletes an item (admin-only)
// @Tags recommendations
// @Produce json
// @Security BearerAuth
// @Param id path int true "Item ID"
// @Param force query int false "Force delete" Enums(0,1)
// @Success 200 {object} dtos.APIResponse
// @Failure 400 {object} dtos.APIError
// @Failure 502 {object} dtos.APIError
// @Router /admin/recommendations/items/{id} [delete]
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

// AdminItemsExport godoc
// @Summary Export recommendation items
// @Description Exports all recommendation items as a CSV file (admin-only)
// @Tags recommendations
// @Produce text/csv
// @Security BearerAuth
// @Success 200 {string} string "CSV data"
// @Failure 502 {object} dtos.APIError
// @Router /admin/recommendations/items/export [get]
func (h *RecommendationHandler) AdminItemsExport(c *gin.Context) {
	csvBytes, err := h.recService.AdminExportItems(c.Request.Context())
	if err != nil {
		response.Error(c, http.StatusBadGateway, err.Error())
		return
	}

	c.Header("Content-Description", "File Transfer")
	c.Header("Content-Disposition", "attachment; filename=recommendation_items.csv")
	c.Header("Content-Type", "text/csv")
	c.Header("Content-Transfer-Encoding", "binary")
	c.Data(http.StatusOK, "text/csv", csvBytes)
}

// AdminListStates godoc
// @Summary List recommendation states
// @Description Typeahead helper for recommendation states (admin-only)
// @Tags recommendations
// @Produce json
// @Security BearerAuth
// @Success 200 {object} dtos.APIResponse{data=map[string]interface{}}
// @Failure 502 {object} dtos.APIError
// @Router /admin/recommendations/states [get]
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

// AdminSearchRefs godoc
// @Summary Search recommendation refs
// @Description Typeahead helper for ref targets (admin-only)
// @Tags recommendations
// @Produce json
// @Security BearerAuth
// @Success 200 {object} dtos.APIResponse{data=map[string]interface{}}
// @Failure 502 {object} dtos.APIError
// @Router /admin/recommendations/refs [get]
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

// AdminStatesList godoc
// @Summary List unique states
// @Description Lists unique states used by the recommendation engine (admin-only)
// @Tags recommendations
// @Produce json
// @Security BearerAuth
// @Success 200 {object} dtos.APIResponse{data=map[string]interface{}}
// @Failure 502 {object} dtos.APIError
// @Router /admin/recommendations/unique-states [get]
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

// AdminStatesCreate godoc
// @Summary Create unique state
// @Description Creates a unique state entry (admin-only)
// @Tags recommendations
// @Accept json
// @Produce json
// @Security BearerAuth
// @Param request body object true "Raw JSON payload"
// @Success 201 {object} dtos.APIResponse{data=map[string]interface{}}
// @Failure 502 {object} dtos.APIError
// @Router /admin/recommendations/unique-states [post]
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

// AdminStatesUpdate godoc
// @Summary Update unique state
// @Description Updates a unique state entry (admin-only)
// @Tags recommendations
// @Accept json
// @Produce json
// @Security BearerAuth
// @Param id path int true "State ID"
// @Param request body object true "Raw JSON payload"
// @Success 200 {object} dtos.APIResponse{data=map[string]interface{}}
// @Failure 400 {object} dtos.APIError
// @Failure 502 {object} dtos.APIError
// @Router /admin/recommendations/unique-states/{id} [put]
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

// AdminStatesDelete godoc
// @Summary Delete unique state
// @Description Deletes a unique state entry (admin-only)
// @Tags recommendations
// @Produce json
// @Security BearerAuth
// @Param id path int true "State ID"
// @Param force query int false "Force delete" Enums(0,1)
// @Success 200 {object} dtos.APIResponse
// @Failure 400 {object} dtos.APIError
// @Failure 502 {object} dtos.APIError
// @Router /admin/recommendations/unique-states/{id} [delete]
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

// AdminStatesExport godoc
// @Summary Export unique states
// @Description Exports all unique states as a CSV file (admin-only)
// @Tags recommendations
// @Produce text/csv
// @Security BearerAuth
// @Success 200 {string} string "CSV data"
// @Failure 502 {object} dtos.APIError
// @Router /admin/recommendations/unique-states/export [get]
func (h *RecommendationHandler) AdminStatesExport(c *gin.Context) {
	csvBytes, err := h.recService.AdminExportStates(c.Request.Context())
	if err != nil {
		response.Error(c, http.StatusBadGateway, err.Error())
		return
	}

	c.Header("Content-Description", "File Transfer")
	c.Header("Content-Disposition", "attachment; filename=unique_states.csv")
	c.Header("Content-Type", "text/csv")
	c.Header("Content-Transfer-Encoding", "binary")
	c.Data(http.StatusOK, "text/csv", csvBytes)
}
