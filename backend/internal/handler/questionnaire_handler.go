package handler

import (
	"context"
	"log"
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/middleware"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"pointmarket/backend/internal/store/gen"
	"pointmarket/backend/internal/utils"
	"strconv"
	"time"

	"github.com/gin-gonic/gin"
)

var _ = dtos.APIResponse{}

type QuestionnaireHandler struct {
	questionnaireService *services.QuestionnaireService
	textAnalyzerService  *services.TextAnalyzerService
	correlationService   *services.CorrelationService
	userService          *services.UserService
}

func NewQuestionnaireHandler(
	questionnaireService *services.QuestionnaireService,
	textAnalyzerService *services.TextAnalyzerService,
	correlationService *services.CorrelationService,
	userService *services.UserService,
) *QuestionnaireHandler {
	return &QuestionnaireHandler{
		questionnaireService: questionnaireService,
		textAnalyzerService:  textAnalyzerService,
		correlationService:   correlationService,
		userService:          userService,
	}
}

// GetQuestionnaires godoc
// @Summary List questionnaires
// @Description Lists questionnaires; `active=true` returns only active ones
// @Tags questionnaires
// @Produce json
// @Security BearerAuth
// @Param active query bool false "Only active questionnaires"
// @Success 200 {object} dtos.APIResponse{data=[]dtos.QuestionnaireDTO}
// @Failure 500 {object} dtos.APIError
// @Router /questionnaires [get]
func (h *QuestionnaireHandler) GetQuestionnaires(c *gin.Context) {
	active := c.Query("active")

	var questionnaires []gen.Questionnaire
	if active == "true" {
		rows, err := h.questionnaireService.GetActiveQuestionnaires(c.Request.Context())
		if err != nil {
			response.Error(c, http.StatusInternalServerError, err.Error())
			return
		}
		questionnaires = rows
	} else {
		rows, err := h.questionnaireService.GetQuestionnaires(c.Request.Context())
		if err != nil {
			response.Error(c, http.StatusInternalServerError, err.Error())
			return
		}
		questionnaires = rows
	}

	var questionnaireDTOs []dtos.QuestionnaireDTO
	for _, q := range questionnaires {
		var qDTO dtos.QuestionnaireDTO
		qDTO.FromQuestionnaire(q)
		questionnaireDTOs = append(questionnaireDTOs, qDTO)
	}

	response.Success(c, http.StatusOK, "Questionnaires retrieved successfully", questionnaireDTOs)
}

// GetQuestionnaireByID godoc
// @Summary Get questionnaire detail
// @Description Gets questionnaire and question list by ID
// @Tags questionnaires
// @Produce json
// @Security BearerAuth
// @Param id path int true "Questionnaire ID"
// @Success 200 {object} dtos.APIResponse{data=dtos.QuestionnaireLikertDetailResponse}
// @Success 200 {object} dtos.APIResponse{data=dtos.QuestionnaireVarkDetailResponse}
// @Failure 400 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /questionnaires/{id} [get]
func (h *QuestionnaireHandler) GetQuestionnaireByID(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid questionnaire ID")
		return
	}

	questionnaire, err := h.questionnaireService.GetQuestionnaireByID(c.Request.Context(), int32(id))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	var questionnaireDTO dtos.QuestionnaireDTO
	questionnaireDTO.FromQuestionnaire(questionnaire)

	questions, err := h.questionnaireService.GetQuestionsByQuestionnaireID(c.Request.Context(), int32(id))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	if questionnaire.Type != gen.QuestionnairesTypeVARK {
		var questionsDTOs []dtos.QuestionnaireLikertQuestionDTO
		for _, q := range questions {
			var qDTO dtos.QuestionnaireLikertQuestionDTO
			qDTO.FromQuestion(q)
			questionsDTOs = append(questionsDTOs, qDTO)
		}

		detailResponse := dtos.QuestionnaireLikertDetailResponse{
			Questionnaire: questionnaireDTO,
			Questions:     questionsDTOs,
		}

		response.Success(c, http.StatusOK, "Questionnaire retrieved successfully", detailResponse)
	} else {
		options, err := h.questionnaireService.GetVarkOptionsByQuestionnaireID(c.Request.Context(), questionnaire.ID)
		if err != nil {
			response.Error(c, http.StatusInternalServerError, err.Error())
			return
		}

		optionsByQuestionID := make(map[int32][]gen.QuestionnaireVarkOption)
		for _, o := range options {
			optionsByQuestionID[o.QuestionID] = append(optionsByQuestionID[o.QuestionID], o)
		}

		var questionsDTOs []dtos.QuestionnaireVarkQuestionDTO
		for _, q := range questions {
			var qDTO dtos.QuestionnaireVarkQuestionDTO
			qDTO.FromQuestionAndOptions(q, optionsByQuestionID[q.ID])
			questionsDTOs = append(questionsDTOs, qDTO)
		}

		detailResponse := dtos.QuestionnaireVarkDetailResponse{
			Questionnaire: questionnaireDTO,
			Questions:     questionsDTOs,
		}

		response.Success(c, http.StatusOK, "Questionnaire retrieved successfully", detailResponse)
	}
}

// SubmitLikert godoc
// @Summary Submit Likert questionnaire
// @Description Submits a Likert questionnaire (MSLQ/AMS)
// @Tags questionnaires
// @Accept json
// @Produce json
// @Security BearerAuth
// @Param request body dtos.LikertSubmissionRequestDTO true "Submission payload"
// @Success 201 {object} dtos.APIResponse{data=dtos.LikertSubmissionResponseDTO}
// @Failure 400 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /questionnaires [post]
func (h *QuestionnaireHandler) SubmitLikert(c *gin.Context) {
	var req dtos.LikertSubmissionRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	userID := middleware.GetUserID(c)
	score, err := h.questionnaireService.SubmitLikert(
		c.Request.Context(),
		userID,
		req.QuestionnaireID,
		req.WeeklyEvaluationID,
		req.Answers,
	)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Questionnaire submitted successfully", dtos.LikertSubmissionResponseDTO{TotalScore: score})
}

// SubmitVark godoc
// @Summary Submit VARK questionnaire
// @Description Submits VARK answers and uses text-analysis to fuse the final learning style
// @Tags questionnaires
// @Accept json
// @Produce json
// @Security BearerAuth
// @Param request body dtos.VarkSubmissionRequestDTO true "Submission payload"
// @Success 201 {object} dtos.APIResponse{data=dtos.TextAnalyzerResponse}
// @Failure 400 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /questionnaires/vark [post]
func (h *QuestionnaireHandler) SubmitVark(c *gin.Context) {
	var req dtos.VarkSubmissionRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	userID := middleware.GetUserID(c)

	scores, err := h.questionnaireService.SubmitVARK(
		c.Request.Context(),
		int64(userID),
		req.QuestionnaireID,
		req.Answers,
	)
	if err != nil {
		log.Println("Error submitting VARK:", err)
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	ctx, cancel := context.WithTimeout(c.Request.Context(), 90*time.Second)
	defer cancel()

	row, fusedScores, keywords, sentences, err := h.textAnalyzerService.Predict(
		ctx,
		req.Text,
		userID,
		utils.NormalizeVARKDBScores(scores),
	)
	if err != nil {
		log.Println("Error analyzing text:", err)
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	err = h.userService.UpdateUserLearningStyle(
		c.Request.Context(),
		userID,
		row.LearningPreferenceType,
		row.LearningPreferenceLabel,
		*fusedScores,
	)
	if err != nil {
		log.Println("Error updating user learning style:", err)
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	resp := dtos.TextAnalyzerResponse{
		Keywords:      keywords,
		KeySentences:  sentences,
		LearningStyle: dtos.StudentLearningStyle{Type: row.LearningPreferenceType, Label: row.LearningPreferenceLabel, Scores: *fusedScores},
		TextStats: dtos.TextAnalyzerTextStats{
			WordCount:         row.CountWords,
			SentenceCount:     row.CountSentences,
			AverageWordLength: row.AverageWordLength,
			ReadingTime:       row.ReadingTime,
			GrammarScore:      row.ScoreGrammar,
			ReadabilityScore:  row.ScoreReadability,
			SentimentScore:    row.ScoreSentiment,
			StructureScore:    row.ScoreStructure,
			ComplexityScore:   row.ScoreComplexity,
		},
	}

	response.Success(c, http.StatusCreated, "Questionnaire submitted successfully", resp)
}

// GetCorrelation godoc
// @Summary Get correlation analysis
// @Description Returns correlation analysis and personalized recommendations
// @Tags questionnaires
// @Produce json
// @Security BearerAuth
// @Success 200 {object} dtos.APIResponse{data=dtos.CorrelationAnalysisResponse}
// @Failure 500 {object} dtos.APIError
// @Router /questionnaires/correlations [get]
func (h *QuestionnaireHandler) GetCorrelation(c *gin.Context) {
	userID := middleware.GetUserID(c)

	analysisResult, err := h.correlationService.GetCorrelationAnalysisForStudent(c.Request.Context(), int64(userID))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Correlation analysis successful", analysisResult)
}

// GetQuestionnaireStats godoc
// @Summary Get questionnaire stats
// @Description Returns aggregated stats for the current student
// @Tags questionnaires
// @Produce json
// @Security BearerAuth
// @Success 200 {object} dtos.APIResponse{data=dtos.StudentQuestionnaireStatsDTO}
// @Failure 500 {object} dtos.APIError
// @Router /questionnaires/stats [get]
//
// GetQuestionnaireStats returns aggregated stats for the current student.
func (h *QuestionnaireHandler) GetQuestionnaireStats(c *gin.Context) {
	userID := middleware.GetUserID(c)
	stats, err := h.questionnaireService.GetQuestionnaireStats(c.Request.Context(), userID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Success(c, http.StatusOK, "Questionnaire stats retrieved", stats)
}

// GetQuestionnaireHistory godoc
// @Summary Get questionnaire history
// @Description Returns questionnaire history, paginated
// @Tags questionnaires
// @Produce json
// @Security BearerAuth
// @Param type query string false "Likert type filter" Enums(MSLQ,AMS)
// @Param limit query int false "Limit" default(10)
// @Param offset query int false "Offset" default(0)
// @Success 200 {object} dtos.PaginatedResponse{data=[]dtos.QuestionnaireHistoryItemDTO}
// @Failure 500 {object} dtos.APIError
// @Router /questionnaires/history [get]
//
// GetQuestionnaireHistory returns the student's questionnaire history (Likert), paginated.
func (h *QuestionnaireHandler) GetQuestionnaireHistory(c *gin.Context) {
	userID := middleware.GetUserID(c)
	typ := c.Query("type") // optional: MSLQ|AMS
	limit, _ := strconv.Atoi(c.DefaultQuery("limit", "10"))
	offset, _ := strconv.Atoi(c.DefaultQuery("offset", "0"))
	if limit <= 0 {
		limit = 10
	}
	if limit > 50 {
		limit = 50
	}
	if offset < 0 {
		offset = 0
	}

	items, total, err := h.questionnaireService.GetQuestionnaireHistory(c.Request.Context(), userID, typ, int32(limit), int32(offset))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	response.Paginated(c, http.StatusOK, "Questionnaire history retrieved", items, total, (offset/limit)+1, limit)
}

// GetLatestVark godoc
// @Summary Get latest VARK result
// @Description Returns the latest fused learning style for the current user
// @Tags questionnaires
// @Produce json
// @Security BearerAuth
// @Success 200 {object} dtos.APIResponse{data=dtos.LatestVarkResponseDTO}
// @Failure 500 {object} dtos.APIError
// @Router /questionnaires/vark [get]
//
// GetLatestVark returns the latest fused learning style for the current user (if available).
func (h *QuestionnaireHandler) GetLatestVark(c *gin.Context) {
	userID := middleware.GetUserID(c)
	// Prefer latest fused learning style from user service
	ls, err := h.userService.GetLatestUserLearningStyle(c.Request.Context(), userID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}
	// When no record, return empty style
	if ls.ID == 0 {
		response.Success(c, http.StatusOK, "No VARK result", dtos.LatestVarkResponseDTO{
			Style:       dtos.LatestVarkStyleDTO{},
			CompletedAt: nil,
		})
		return
	}

	var completedAt *string
	if !ls.CreatedAt.IsZero() {
		ts := ls.CreatedAt.Format("2006-01-02T15:04:05Z07:00")
		completedAt = &ts
	}

	scores := dtos.VARKScores{
		Visual:      nsToFloat(ls.ScoreVisual),
		Auditory:    nsToFloat(ls.ScoreAuditory),
		Reading:     nsToFloat(ls.ScoreReading),
		Kinesthetic: nsToFloat(ls.ScoreKinesthetic),
	}

	resp := dtos.LatestVarkResponseDTO{
		Style: dtos.LatestVarkStyleDTO{
			Type:   string(ls.Type),
			Label:  ls.Label,
			Scores: &scores,
		},
		CompletedAt: completedAt,
	}

	response.Success(c, http.StatusOK, "Latest VARK retrieved", resp)
}

// helper to safely unwrap *float64 (nullable scores)
func nsToFloat(p *float64) float64 {
	if p == nil {
		return 0
	}
	return *p
}

// CreateQuestionnaire godoc
// @Summary Create questionnaire
// @Description Creates a questionnaire (admin-only)
// @Tags questionnaires
// @Accept json
// @Produce json
// @Security BearerAuth
// @Param request body dtos.AdminQuestionnaireDTO true "Questionnaire"
// @Success 201 {object} dtos.APIResponse{data=gen.Questionnaire}
// @Failure 400 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /questionnaires/ [post]
func (h *QuestionnaireHandler) CreateQuestionnaire(c *gin.Context) {
	var req dtos.AdminQuestionnaireDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	questionnaire, err := h.questionnaireService.CreateQuestionnaire(c.Request.Context(), req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Questionnaire created successfully", questionnaire)
}

// UpdateQuestionnaire godoc
// @Summary Update questionnaire
// @Description Updates a questionnaire (admin-only)
// @Tags questionnaires
// @Accept json
// @Produce json
// @Security BearerAuth
// @Param id path int true "Questionnaire ID"
// @Param request body dtos.AdminQuestionnaireDTO true "Questionnaire"
// @Success 200 {object} dtos.APIResponse{data=gen.Questionnaire}
// @Failure 400 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /questionnaires/{id} [put]
func (h *QuestionnaireHandler) UpdateQuestionnaire(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid questionnaire ID")
		return
	}

	var req dtos.AdminQuestionnaireDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	questionnaire, err := h.questionnaireService.UpdateQuestionnaire(c.Request.Context(), int32(id), req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Questionnaire updated successfully", questionnaire)
}

// DeleteQuestionnaire godoc
// @Summary Delete questionnaire
// @Description Deletes a questionnaire (admin-only)
// @Tags questionnaires
// @Produce json
// @Security BearerAuth
// @Param id path int true "Questionnaire ID"
// @Success 200 {object} dtos.APIResponse
// @Failure 400 {object} dtos.APIError
// @Failure 500 {object} dtos.APIError
// @Router /questionnaires/{id} [delete]
func (h *QuestionnaireHandler) DeleteQuestionnaire(c *gin.Context) {
	id, err := strconv.ParseInt(c.Param("id"), 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid questionnaire ID")
		return
	}

	err = h.questionnaireService.DeleteQuestionnaire(c.Request.Context(), int32(id))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Questionnaire deleted successfully", nil)
}
