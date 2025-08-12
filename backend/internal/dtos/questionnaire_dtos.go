package dtos

import (
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store/gen"
	"time"
)

type QuestionnaireDTO struct {
	ID             int32     `json:"id"`
	Type           string    `json:"type"`
	Name           string    `json:"name"`
	Description    *string   `json:"description,omitempty"`
	TotalQuestions int32     `json:"total_questions"`
	Status         string    `json:"status"`
	CreatedAt      time.Time `json:"created_at"`
}

type QuestionnaireQuestionDTO struct {
	ID              int32   `json:"id"`
	QuestionnaireID int32   `json:"questionnaire_id"`
	Number          int32   `json:"number"`
	Text            string  `json:"text"`
	Subscale        *string `json:"subscale,omitempty"`
}

type LikertSubmissionRequestDTO struct {
	QuestionnaireID int32             `json:"questionnaire_id"`
	Answers         map[string]string `json:"answers"`
}

type LikertSubmissionResponseDTO struct {
	TotalScore float64 `json:"total_score"`
}

type VarkSubmissionRequestDTO struct {
	QuestionnaireID int32             `json:"questionnaire_id"`
	Answers         map[string]string `json:"answers"`
	Text            string            `json:"text"`
}

type VarkAnswerDetailDTO struct {
	QuestionID int64  `json:"question_id"`
	Answer     string `json:"answer"`
	Style      string `json:"style"`
}

type LearningStyleDTO struct {
	Type      string        `json:"type"` // dominant | multimodal
	Label     string        `json:"label"`
	Scores    VARKScores `json:"scores"`
	CreatedAt *time.Time    `json:"created_at,omitempty"`
}

type VarkSubmissionResponseDTO struct {
	QuestionnaireID int64                 `json:"questionnaire_id"`
	StudentID       int64                 `json:"student_id"`
	Scores          VARKScores         `json:"scores"`
	Style           LearningStyleDTO      `json:"style"`
	RawAnswers      []VarkAnswerDetailDTO `json:"raw_answers"`
	CreatedAt       time.Time             `json:"created_at"`
}

type LikertResultDTO struct {
	ID              int64              `json:"id"`
	QuestionnaireID int64              `json:"questionnaire_id"`
	StudentID       int64              `json:"student_id"`
	Answers         []int              `json:"answers"`
	TotalScore      float64            `json:"total_score"`
	SubscaleScores  map[string]float64 `json:"subscale_scores"`
	CreatedAt       time.Time          `json:"created_at"`
}

type VarkResultDTO struct {
	ID              int64                 `json:"id"`
	QuestionnaireID int64                 `json:"questionnaire_id"`
	StudentID       int64                 `json:"student_id"`
	Scores          VARKScores         `json:"scores"`
	Answers         []VarkAnswerDetailDTO `json:"answers"`
	CreatedAt       time.Time             `json:"created_at"`
}

type LikertStatsDTO struct {
	QuestionnaireID int64    `json:"questionnaire_id"`
	Type            string   `json:"type"`
	Name            string   `json:"name"`
	Attempts        int64    `json:"attempts"`
	AverageScore    *float64 `json:"average_score"`
	BestScore       *float64 `json:"best_score"`
	LowestScore     *float64 `json:"lowest_score"`
}

type QuestionnaireDetailResponseDTO struct {
	Questionnaire QuestionnaireDTO           `json:"questionnaire"`
	Questions     []QuestionnaireQuestionDTO `json:"questions"`
}

func (d *QuestionnaireDTO) FromQuestionnaire(q gen.Questionnaire) {
	d.ID = q.ID
	d.Type = string(q.Type)
	d.Name = q.Name
	d.Description = &q.Description.String
	d.TotalQuestions = q.TotalQuestions

	if q.Status.Valid {
		d.Status = string(q.Status.QuestionnairesStatus)
	} else {
		d.Status = string(gen.QuestionnairesStatusInactive)
	}

	if q.CreatedAt.Valid {
		d.CreatedAt = q.CreatedAt.Time
	} else {
		d.CreatedAt = time.Time{}
	}
}

func (d *QuestionnaireQuestionDTO) FromQuestion(q gen.QuestionnaireQuestion) {
	d.ID = q.ID
	d.QuestionnaireID = q.QuestionnaireID
	d.Number = q.QuestionNumber
	d.Text = q.QuestionText
	d.Subscale = &q.Subscale.String
}

// ==================
//     Legacy
// ==================
// ==================
//     Requests
// ==================

type SubmitQuestionnaireRequest struct {
	QuestionnaireID int               `json:"questionnaire_id" binding:"required"`
	Answers         map[string]string `json:"answers" binding:"required"`
	WeekNumber      int               `json:"week_number" binding:"required"`
	Year            int               `json:"year" binding:"required"`
}

// ==================
//     Responses
// ==================

type QuestionnaireResultResponse struct {
	ID                       int       `json:"id"`
	TotalScore               *float64  `json:"total_score"`
	CompletedAt              time.Time `json:"completed_at"`
	WeekNumber               int       `json:"week_number"`
	Year                     int       `json:"year"`
	QuestionnaireName        string    `json:"questionnaire_name"`
	QuestionnaireType        string    `json:"questionnaire_type"`
	QuestionnaireDescription *string   `json:"questionnaire_description"`
}

// FromQuestionnaireResult converts a models.QuestionnaireResult to a QuestionnaireResultResponse DTO.
func (dto *QuestionnaireResultResponse) FromQuestionnaireResult(result models.QuestionnaireResult) {
	dto.ID = result.ID
	dto.TotalScore = result.TotalScore
	dto.CompletedAt = result.CompletedAt
	dto.WeekNumber = result.WeekNumber
	dto.Year = result.Year
	dto.QuestionnaireName = result.QuestionnaireName
	dto.QuestionnaireType = result.QuestionnaireType
	dto.QuestionnaireDescription = result.QuestionnaireDescription
}

type QuestionnaireResponse struct {
	ID             int                          `json:"id"`
	Type           string                       `json:"type"`
	Name           string                       `json:"name"`
	Description    *string                      `json:"description"`
	TotalQuestions int                          `json:"total_questions"`
	Questions      []QuestionResponse           `json:"questions"`
	RecentResult   *QuestionnaireResultResponse `json:"recent_result,omitempty"`
	CreatedAt      time.Time                    `json:"created_at"`
}

type QuestionnaireDetailResponse struct {
	Questionnaire QuestionnaireResponse        `json:"questionnaire"`
	Questions     []QuestionResponse           `json:"questions"`
	RecentResult  *QuestionnaireResultResponse `json:"recent_result,omitempty"`
}

type QuestionnaireListDTO struct {
	ID             int       `json:"id"`
	Type           string    `json:"type"`
	Name           string    `json:"name"`
	Description    *string   `json:"description"`
	TotalQuestions int       `json:"total_questions"`
	CreatedAt      time.Time `json:"created_at"`
}

type QuestionResponse struct {
	ID              int                   `json:"id"`
	QuestionnaireID int                   `json:"questionnaire_id"`
	QuestionNumber  int                   `json:"question_number"`
	QuestionText    string                `json:"question_text"`
	Subscale        *string               `json:"subscale"`
	ReverseScored   bool                  `json:"reverse_scored"`
	Options         []VARKAnswerOptionDTO `json:"options,omitempty"` // Added for VARK questions
	CreatedAt       time.Time             `json:"created_at"`
}

type VARKAnswerOptionDTO struct {
	ID           int    `json:"id"`
	QuestionID   int    `json:"question_id"`
	OptionLetter string `json:"option_letter"`
	OptionText   string `json:"option_text"`
}

// FromQuestionnaire converts a models.Questionnaire and its questions to a QuestionnaireResponse DTO.
func (dto *QuestionnaireResponse) FromQuestionnaire(q models.Questionnaire, questions []QuestionResponse) {
	dto.ID = q.ID
	dto.Type = q.Type
	dto.Name = q.Name
	dto.Description = q.Description
	dto.TotalQuestions = q.TotalQuestions
	dto.CreatedAt = q.CreatedAt
	dto.Questions = make([]QuestionResponse, len(questions))
	for i, question := range questions {
		dto.Questions[i] = question
	}
}

// FromQuestion converts a models.QuestionnaireQuestion to a QuestionResponse DTO.
func (dto *QuestionResponse) FromQuestion(question models.QuestionnaireQuestion) {
	dto.ID = question.ID
	dto.QuestionnaireID = question.QuestionnaireID
	dto.QuestionNumber = question.QuestionNumber
	dto.QuestionText = question.QuestionText
	dto.Subscale = question.Subscale
	dto.ReverseScored = question.ReverseScored
	dto.CreatedAt = question.CreatedAt
}

// FromVARKAnswerOption converts a models.VARKAnswerOption to a VARKAnswerOptionDTO.
func (dto *VARKAnswerOptionDTO) FromVARKAnswerOption(option models.VARKAnswerOption) {
	dto.ID = option.ID
	dto.QuestionID = option.QuestionID
	dto.OptionLetter = option.OptionLetter
	dto.OptionText = option.OptionText
}

type QuestionnaireHistoryResponse struct {
	ID                       int       `json:"id"`
	StudentID                int       `json:"student_id"`
	QuestionnaireID          int       `json:"questionnaire_id"`
	TotalScore               *float64  `json:"total_score"`
	CompletedAt              time.Time `json:"completed_at"`
	WeekNumber               int       `json:"week_number"`
	Year                     int       `json:"year"`
	QuestionnaireName        string    `json:"questionnaire_name"`
	QuestionnaireType        string    `json:"questionnaire_type"`
	QuestionnaireDescription *string   `json:"questionnaire_description"`
}

type LatestQuestionnaireResultResponse struct {
	ID                int                `json:"id"`
	QuestionnaireType string             `json:"questionnaire_type"`
	QuestionnaireName string             `json:"questionnaire_name"`
	TotalScore        *float64           `json:"total_score,omitempty"`
	SubscaleScores    map[string]float64 `json:"subscale_scores,omitempty"`
	VisualScore       *int               `json:"visual_score,omitempty"`
	AuditoryScore     *int               `json:"auditory_score,omitempty"`
	ReadingScore      *int               `json:"reading_score,omitempty"`
	KinestheticScore  *int               `json:"kinesthetic_score,omitempty"`
	DominantStyle     *string            `json:"dominant_style,omitempty"`
	CompletedAt       time.Time          `json:"completed_at"`
	WeekNumber        *int               `json:"week_number,omitempty"`
	Year              *int               `json:"year,omitempty"`
}
