package dtos

import (
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

type QuestionnaireLikertQuestionDTO struct {
	ID              int32   `json:"id"`
	QuestionnaireID int32   `json:"questionnaire_id"`
	QuestionNumber  int32   `json:"question_number"`
	QuestionText    string  `json:"question_text"`
	Subscale        *string `json:"subscale,omitempty"`
}

type QuestionnaireVarkQuestionDTO struct {
	ID              int32                        `json:"id"`
	QuestionnaireID int32                        `json:"questionnaire_id"`
	QuestionNumber  int32                        `json:"question_number"`
	QuestionText    string                       `json:"question_text"`
	Subscale        *string                      `json:"subscale,omitempty"`
	Options         []QuestionnaireVarkOptionDTO `json:"options"`
}

type QuestionnaireVarkOptionDTO struct {
	ID            int32  `json:"id"`
	QuestionID    int32  `json:"question_id"`
	OptionText    string `json:"option_text"`
	OptionLetter  string `json:"option_letter"`
	LearningStyle string `json:"learning_style"`
}

type LikertSubmissionRequestDTO struct {
	QuestionnaireID    int32             `json:"questionnaire_id"`
	Answers            map[string]string `json:"answers"`
	WeeklyEvaluationID *int64            `json:"weekly_evaluation_id,omitempty"`
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
	Type      string     `json:"type"` // dominant | multimodal
	Label     string     `json:"label"`
	Scores    VARKScores `json:"scores"`
	CreatedAt *time.Time `json:"created_at,omitempty"`
}

type VarkSubmissionResponseDTO struct {
	QuestionnaireID int64                 `json:"questionnaire_id"`
	StudentID       string                `json:"student_id"`
	Scores          VARKScores            `json:"scores"`
	Style           LearningStyleDTO      `json:"style"`
	RawAnswers      []VarkAnswerDetailDTO `json:"raw_answers"`
	CreatedAt       time.Time             `json:"created_at"`
}

type LikertResultDTO struct {
	ID              int64              `json:"id"`
	StudentID       string             `json:"student_id"`
	QuestionnaireID int64              `json:"questionnaire_id"`
	Answers         []int              `json:"answers"`
	TotalScore      float64            `json:"total_score"`
	SubscaleScores  map[string]float64 `json:"subscale_scores"`
	CreatedAt       time.Time          `json:"created_at"`
}

type VarkResultDTO struct {
	ID              int64                 `json:"id"`
	StudentID       string                `json:"student_id"`
	QuestionnaireID int64                 `json:"questionnaire_id"`
	Scores          VARKScores            `json:"scores"`
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

type QuestionnaireLikertDetailResponse struct {
	Questionnaire QuestionnaireDTO                 `json:"questionnaire"`
	Questions     []QuestionnaireLikertQuestionDTO `json:"questions"`
}

type QuestionnaireVarkDetailResponse struct {
	Questionnaire QuestionnaireDTO               `json:"questionnaire"`
	Questions     []QuestionnaireVarkQuestionDTO `json:"questions"`
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

func (d *QuestionnaireLikertQuestionDTO) FromQuestion(q gen.QuestionnaireQuestion) {
	d.ID = q.ID
	d.QuestionnaireID = q.QuestionnaireID
	d.QuestionNumber = q.QuestionNumber
	d.QuestionText = q.QuestionText

	if q.Subscale.Valid {
		d.Subscale = &q.Subscale.String
	} else {
		d.Subscale = nil
	}
}

func (d *QuestionnaireVarkQuestionDTO) FromQuestionAndOptions(q gen.QuestionnaireQuestion, options []gen.QuestionnaireVarkOption) {
	d.ID = q.ID
	d.QuestionnaireID = q.QuestionnaireID
	d.QuestionNumber = q.QuestionNumber
	d.QuestionText = q.QuestionText

	if q.Subscale.Valid {
		d.Subscale = &q.Subscale.String
	} else {
		d.Subscale = nil
	}

	d.Options = make([]QuestionnaireVarkOptionDTO, 0, len(options))
	for _, option := range options {
		var optionDTO QuestionnaireVarkOptionDTO
		optionDTO.FromVarkOption(option)
		d.Options = append(d.Options, optionDTO)
	}
}

func (d *QuestionnaireVarkOptionDTO) FromVarkOption(option gen.QuestionnaireVarkOption) {
	d.ID = option.ID
	d.QuestionID = option.QuestionID
	d.OptionText = option.OptionText
	d.OptionLetter = option.OptionLetter
	d.LearningStyle = string(option.LearningStyle)
}

// AdminOptionDTO represents a single option for a VARK question in the admin view.
type AdminOptionDTO struct {
	ID            *int32 `json:"id,omitempty"`
	OptionText    string `json:"option_text"`
	OptionLetter  string `json:"option_letter"`
	LearningStyle string `json:"learning_style"`
}

// AdminQuestionDTO represents a single question in a questionnaire in the admin view.
type AdminQuestionDTO struct {
	ID             *int32           `json:"id,omitempty"`
	QuestionNumber int32            `json:"question_number"`
	QuestionText   string           `json:"question_text"`
	Subscale       *string          `json:"subscale,omitempty"`
	Options        []AdminOptionDTO `json:"options,omitempty"` // Only for VARK questions
}

// AdminQuestionnaireDTO represents the full state of a questionnaire for admin CRUD operations.
type AdminQuestionnaireDTO struct {
	ID             *int32             `json:"id,omitempty"`
	Name           string             `json:"name"`
	Description    string             `json:"description"`
	Type           string             `json:"type"`
	Status         string             `json:"status"`
	TotalQuestions int32              `json:"total_questions"`
	Questions      []AdminQuestionDTO `json:"questions"`
}
