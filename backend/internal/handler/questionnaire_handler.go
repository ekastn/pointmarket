package handler

import (
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"pointmarket/backend/internal/store/gen"
	"strconv"

	"github.com/gin-gonic/gin"
)

type QuestionnaireHandler struct {
	questionnaireService *services.QuestionnaireService
	textAnalyzerService  *services.TextAnalyzerService
}

func NewQuestionnaireHandler(
	questionnaireService *services.QuestionnaireService,
	textAnalyzerService *services.TextAnalyzerService,
) *QuestionnaireHandler {
	return &QuestionnaireHandler{
		questionnaireService: questionnaireService,
		textAnalyzerService:  textAnalyzerService,
	}
}

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

	var questionsDTOs []dtos.QuestionnaireQuestionDTO
	for _, q := range questions {
		var qDTO dtos.QuestionnaireQuestionDTO
		qDTO.FromQuestion(q)
		questionsDTOs = append(questionsDTOs, qDTO)
	}

	detailResponse := dtos.QuestionnaireDetailResponseDTO{
		Questionnaire: questionnaireDTO,
		Questions:     questionsDTOs,
	}

	response.Success(c, http.StatusOK, "Questionnaire retrieved successfully", detailResponse)
}

func (h *QuestionnaireHandler) SubmitLikert(c *gin.Context) {
	var req dtos.LikertSubmissionRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	userID, _ := c.Get("userID")
	score, err := h.questionnaireService.SubmitLikert(
		c.Request.Context(),
		int64(userID.(uint)),
		req.QuestionnaireID,
		req.Answers,
	)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Questionnaire submitted successfully", gin.H{"total_score": score})
}

func (h *QuestionnaireHandler) SubmitVark(c *gin.Context) {
	var req dtos.VarkSubmissionRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, err.Error())
		return
	}

	userID, _ := c.Get("userID")

	scores, err := h.questionnaireService.SubmitVARK(
		c.Request.Context(),
		int64(userID.(uint)),
		req.QuestionnaireID,
		req.Answers,
	)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	row, fusedScores, keywords, sentences, err := h.textAnalyzerService.Predict(
		c.Request.Context(),
		req.Text,
		int64(userID.(uint)),
		scores,
	)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, err.Error())
		return
	}

	resp := gin.H{
		"keywords":      keywords,
		"key_sentences": sentences,
		"learning_style": dtos.StudentLearningStyle{
			Type:   row.LearningPreferenceType,
			Label:  row.LearningPreferenceLabel,
			Scores: *fusedScores,
		},
		"text_stats": gin.H{
            "word_count": row.CountWords,
            "sentence_count": row.CountSentences,
            "average_word_length": row.AverageWordLength,
            "reading_time": row.ReadingTime,
			"grammar_score": row.ScoreGrammar,
            "readability_score": row.ScoreReadability,
            "sentiment_score": row.ScoreSentiment,
            "structure_score": row.ScoreSentiment,
            "complexity_score": row.ScoreComplexity,
		},
	}

    response.Success(c, http.StatusCreated, "Questionnaire submitted successfully", resp)
}
