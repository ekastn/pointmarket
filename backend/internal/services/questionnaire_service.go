package services

import (
	"context"
	"database/sql"
	"encoding/json"
	"errors"
	"fmt"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
	"pointmarket/backend/internal/utils"
	"strconv"
)

// QuestionnaireService provides business logic for questionnaires
type QuestionnaireService struct {
	db *sql.DB
	q  *gen.Queries
}

// NewQuestionnaireService creates a new instance of QuestionnaireService
func NewQuestionnaireService(db *sql.DB, q *gen.Queries) *QuestionnaireService {
	return &QuestionnaireService{db: db, q: q}
}

// GetQuestionnaires retrieves all questionnaires
func (s *QuestionnaireService) GetQuestionnaires(ctx context.Context) ([]gen.Questionnaire, error) {
	return s.q.GetQuestionnaires(ctx)
}

// GetActiveQuestionnaires retrieves all active questionnaires
func (s *QuestionnaireService) GetActiveQuestionnaires(ctx context.Context) ([]gen.Questionnaire, error) {
	return s.q.GetActiveQuestionnaires(ctx)
}

// GetQuestionnaireByID retrieves a questionnaire by ID
func (s *QuestionnaireService) GetQuestionnaireByID(ctx context.Context, id int32) (gen.Questionnaire, error) {
	row, err := s.q.GetQuestionnaireByID(ctx, id)
	if err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return gen.Questionnaire{}, nil
		}
		return gen.Questionnaire{}, err
	}
	return row, nil
}

// GetQuestionsByQuestionnaireID retrieves all questions for a given questionnaire ID
func (s *QuestionnaireService) GetQuestionsByQuestionnaireID(ctx context.Context, questionnaireID int32) ([]gen.QuestionnaireQuestion, error) {
	return s.q.GetQuestionsByQuestionnaireID(ctx, questionnaireID)
}

// GetVarkOptionsByQuestionnaireID retrieves all VARK options for a given questionnaire ID
func (s *QuestionnaireService) GetVarkOptionsByQuestionnaireID(ctx context.Context, questionnaireID int32) ([]gen.QuestionnaireVarkOption, error) {
	return s.q.GetVarkOptionsByQuestionnaireID(ctx, questionnaireID)
}

// SubmitLikert saves a student's Likert answers
func (s *QuestionnaireService) SubmitLikert(ctx context.Context, studentID int64, questionnaireID int32, weeklyEvaluationID *int64, answers map[string]string) (*float64, error) {
	qMeta, err := s.GetQuestionnaireByID(ctx, questionnaireID)
	if err != nil {
		return nil, err
	}
	if qMeta.Type != "MSLQ" && qMeta.Type != "AMS" {
		return nil, fmt.Errorf("questionnaire type %s not likert", qMeta.Type)
	}

	questions, err := s.GetQuestionsByQuestionnaireID(ctx, questionnaireID)
	if err != nil {
		return nil, err
	}
	if len(questions) == 0 {
		return nil, errors.New("no questions defined")
	}
	if len(answers) != len(questions) {
		return nil, fmt.Errorf("answer length %d mismatch questions %d", len(answers), len(questions))
	}

	totalScore := 0.0
	subscaleScores := make(map[string]float64)
	subscaleCounts := make(map[string]int)

	for _, q := range questions {
		answerStr, ok := answers[fmt.Sprintf("%d", q.ID)]
		if !ok {
			continue
		}
		answer, err := strconv.ParseFloat(answerStr, 64)
		if err != nil {
			return nil, fmt.Errorf("invalid answer format for question %d: %w", q.ID, err)
		}

		// reverse scoring
		// if q.ReverseScored {
		// 	answer = 8 - answer
		// }

		totalScore += answer

		if q.Subscale.Valid {
			subscaleScores[q.Subscale.String] += answer
			subscaleCounts[q.Subscale.String]++
		}
	}

	avgTotalScore := totalScore / float64(len(questions))

	for subscale, sum := range subscaleScores {
		if subscaleCounts[subscale] > 0 {
			subscaleScores[subscale] = sum / float64(subscaleCounts[subscale])
		}
	}

	subscaleScoresJSON, err := json.Marshal(subscaleScores)
	if err != nil {
		return nil, fmt.Errorf("failed to marshal subscale scores: %w", err)
	}

	answersJSON, err := json.Marshal(answers)
	if err != nil {
		return nil, fmt.Errorf("failed to marshal answers: %w", err)
	}

	data := gen.CreateLikertResultParams{
		StudentID:       studentID,
		QuestionnaireID: questionnaireID,
		Answers:         answersJSON,
		TotalScore:      avgTotalScore,
		SubscaleScores:  subscaleScoresJSON,
	}

	if weeklyEvaluationID != nil {
		data.WeeklyEvaluationID = sql.NullInt64{Int64: *weeklyEvaluationID, Valid: true}
	}

	err = s.q.CreateLikertResult(ctx, data)
	if err != nil {
		return nil, err
	}

	// If the submission is for a weekly evaluation, update its status
	if weeklyEvaluationID != nil {
		err = s.q.UpdateWeeklyEvaluationStatus(ctx, gen.UpdateWeeklyEvaluationStatusParams{
			ID:        *weeklyEvaluationID,
			StudentID: studentID,
		})
		if err != nil {
			// Log the error but don't fail the whole transaction,
			// as the main result was already saved.
			// Or handle this in a transaction. For now, just log.
			fmt.Printf("Warning: failed to update weekly evaluation status for id %d: %v\n", *weeklyEvaluationID, err)
		}
	}

	return &avgTotalScore, nil
}

// SubmitVARK calculates and saves a student's VARK assessment result
func (s *QuestionnaireService) SubmitVARK(
	ctx context.Context,
	studentID int64,
	questionnaireID int32,
	answers map[string]string,
) (dtos.VARKScores, error) {
	options, err := s.q.GetVarkOptionsByQuestionnaireID(ctx, questionnaireID)
	if err != nil {
		return dtos.VARKScores{}, err
	}

	// Create a map for quick lookup: questionID -> optionLetter -> learningStyle
	optionMap := make(map[int32]map[string]string)
	for _, opt := range options {
		if _, ok := optionMap[opt.QuestionID]; !ok {
			optionMap[opt.QuestionID] = make(map[string]string)
		}
		optionMap[opt.QuestionID][opt.OptionLetter] = string(opt.LearningStyle)
	}

	scores := map[string]int32{
		"Visual":      0,
		"Auditory":    0,
		"Reading":     0,
		"Kinesthetic": 0,
	}

	for questionIDStr, answerLetter := range answers {
		questionID := 0
		fmt.Sscanf(questionIDStr, "%d", &questionID) // Convert string key to int

		if qOptions, ok := optionMap[int32(questionID)]; ok {
			if learningStyle, ok := qOptions[answerLetter]; ok {
				scores[learningStyle]++
			}
		}
	}

	answersJSON, err := json.Marshal(answers)
	if err != nil {
		return dtos.VARKScores{}, err
	}

	prefType, prefLabel := utils.DetermineLearningPreferenceType(dtos.VARKScores{
		Visual:      float64(scores["Visual"]),
		Auditory:    float64(scores["Auditory"]),
		Reading:     float64(scores["Reading"]),
		Kinesthetic: float64(scores["Kinesthetic"]),
	})

	varkResult := gen.CreateVarkResultParams{
		StudentID:        studentID,
		QuestionnaireID:  questionnaireID,
		VarkType:         gen.StudentQuestionnaireVarkResultsVarkType(prefType),
		VarkLabel:        prefLabel,
		ScoreVisual:      scores["Visual"],
		ScoreAuditory:    scores["Auditory"],
		ScoreReading:     scores["Reading"],
		ScoreKinesthetic: scores["Kinesthetic"],
		Answers:          answersJSON,
	}

	if err := s.q.CreateVarkResult(ctx, varkResult); err != nil {
		return dtos.VARKScores{}, err
	}

	return dtos.VARKScores{
		Visual:      float64(scores["Visual"]),
		Auditory:    float64(scores["Auditory"]),
		Reading:     float64(scores["Reading"]),
		Kinesthetic: float64(scores["Kinesthetic"]),
	}, nil
}

func (s *QuestionnaireService) GetLatestLikertByType(ctx context.Context, studentID int64, qType string) (gen.StudentQuestionnaireLikertResult, error) {
	row, err := s.q.GetLatestLikertResultByType(
		ctx,
		gen.GetLatestLikertResultByTypeParams{
			StudentID: studentID,
			Type:      gen.QuestionnairesType(qType),
		},
	)
	if err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return gen.StudentQuestionnaireLikertResult{}, nil
		}
		return gen.StudentQuestionnaireLikertResult{}, err
	}
	return row, nil
}

func (s *QuestionnaireService) GetLatestVark(ctx context.Context, studentID int64) (gen.GetLatestVarkResultRow, error) {
	row, err := s.q.GetLatestVarkResult(ctx, studentID)
	if err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return gen.GetLatestVarkResultRow{}, nil
		}
		return gen.GetLatestVarkResultRow{}, err
	}
	return row, nil
}

func (s *QuestionnaireService) GetLikertStats(ctx context.Context, studentID int64) ([]gen.GetLikertStatsByStudentRow, error) {
	return s.q.GetLikertStatsByStudent(ctx, studentID)
}

// GetQuestionnaireByType retrieves a questionnaire by its type
func (s *QuestionnaireService) GetQuestionnaireByType(ctx context.Context, qType gen.QuestionnairesType) (gen.Questionnaire, error) {
	row, err := s.q.GetQuestionnaireByType(ctx, qType)
	if err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return gen.Questionnaire{}, fmt.Errorf("questionnaire of type %s not found", qType)
		}
		return gen.Questionnaire{}, fmt.Errorf("failed to get questionnaire by type %s: %w", qType, err)
	}
	return row, nil
}

func (s *QuestionnaireService) CreateQuestionnaire(ctx context.Context, arg dtos.AdminQuestionnaireDTO) (gen.Questionnaire, error) {
	tx, err := s.db.BeginTx(ctx, nil)
	if err != nil {
		return gen.Questionnaire{}, err
	}
	defer tx.Rollback()

	qtx := s.q.WithTx(tx)

	questionnaireResult, err := qtx.CreateQuestionnaire(ctx, gen.CreateQuestionnaireParams{
		Type:           gen.QuestionnairesType(arg.Type),
		Name:           arg.Name,
		Description:    sql.NullString{String: arg.Description, Valid: true},
		TotalQuestions: int32(len(arg.Questions)),
		Status:         gen.NullQuestionnairesStatus{QuestionnairesStatus: gen.QuestionnairesStatus(arg.Status), Valid: true},
	})
	if err != nil {
		return gen.Questionnaire{}, err
	}
	questionnaireId, err := questionnaireResult.LastInsertId()
	if err != nil {
		return gen.Questionnaire{}, err
	}

	for _, qDto := range arg.Questions {
		questionResult, err := qtx.CreateQuestion(ctx, gen.CreateQuestionParams{
			QuestionnaireID: int32(questionnaireId),
			QuestionNumber:  qDto.QuestionNumber,
			QuestionText:    qDto.QuestionText,
			Subscale:        sql.NullString{String: *qDto.Subscale, Valid: qDto.Subscale != nil},
		})
		if err != nil {
			return gen.Questionnaire{}, err
		}

		if arg.Type == "VARK" {
			questionId, err := questionResult.LastInsertId()
			if err != nil {
				return gen.Questionnaire{}, err
			}
			for _, oDto := range qDto.Options {
				_, err := qtx.CreateVarkOption(ctx, gen.CreateVarkOptionParams{
					QuestionID:    int32(questionId),
					OptionText:    oDto.OptionText,
					OptionLetter:  oDto.OptionLetter,
					LearningStyle: gen.QuestionnaireVarkOptionsLearningStyle(oDto.LearningStyle),
				})
				if err != nil {
					return gen.Questionnaire{}, err
				}
			}
		}
	}

	if err := tx.Commit(); err != nil {
		return gen.Questionnaire{}, err
	}

	return s.q.GetQuestionnaireByID(ctx, int32(questionnaireId))
}

func (s *QuestionnaireService) UpdateQuestionnaire(ctx context.Context, id int32, arg dtos.AdminQuestionnaireDTO) (gen.Questionnaire, error) {
	tx, err := s.db.BeginTx(ctx, nil)
	if err != nil {
		return gen.Questionnaire{}, err
	}
	defer tx.Rollback()

	qtx := s.q.WithTx(tx)

	err = qtx.UpdateQuestionnaire(ctx, gen.UpdateQuestionnaireParams{
		ID:             id,
		Name:           arg.Name,
		Description:    sql.NullString{String: arg.Description, Valid: true},
		TotalQuestions: int32(len(arg.Questions)),
		Status:         gen.NullQuestionnairesStatus{QuestionnairesStatus: gen.QuestionnairesStatus(arg.Status), Valid: true},
	})
	if err != nil {
		return gen.Questionnaire{}, err
	}

	// Delete existing questions and options
	questions, err := qtx.GetQuestionsByQuestionnaireID(ctx, id)
	if err != nil {
		return gen.Questionnaire{}, err
	}
	for _, q := range questions {
		if arg.Type == "VARK" {
			options, err := qtx.GetVarkOptionsByQuestionnaireID(ctx, q.QuestionnaireID)
			if err != nil {
				return gen.Questionnaire{}, err
			}
			for _, o := range options {
				err := qtx.DeleteVarkOption(ctx, o.ID)
				if err != nil {
					return gen.Questionnaire{}, err
				}
			}
		}
		err := qtx.DeleteQuestion(ctx, q.ID)
		if err != nil {
			return gen.Questionnaire{}, err
		}
	}

	for _, qDto := range arg.Questions {
		questionResult, err := qtx.CreateQuestion(ctx, gen.CreateQuestionParams{
			QuestionnaireID: id,
			QuestionNumber:  qDto.QuestionNumber,
			QuestionText:    qDto.QuestionText,
			Subscale:        sql.NullString{String: *qDto.Subscale, Valid: qDto.Subscale != nil},
		})
		if err != nil {
			return gen.Questionnaire{}, err
		}

		if arg.Type == "VARK" {
			questionId, err := questionResult.LastInsertId()
			if err != nil {
				return gen.Questionnaire{}, err
			}
			for _, oDto := range qDto.Options {
				_, err := qtx.CreateVarkOption(ctx, gen.CreateVarkOptionParams{
					QuestionID:    int32(questionId),
					OptionText:    oDto.OptionText,
					OptionLetter:  oDto.OptionLetter,
					LearningStyle: gen.QuestionnaireVarkOptionsLearningStyle(oDto.LearningStyle),
				})
				if err != nil {
					return gen.Questionnaire{}, err
				}
			}
		}
	}

	if err := tx.Commit(); err != nil {
		return gen.Questionnaire{}, err
	}

	return s.q.GetQuestionnaireByID(ctx, id)
}

func (s *QuestionnaireService) DeleteQuestionnaire(ctx context.Context, id int32) error {
	return s.q.DeleteQuestionnaire(ctx, id)
}
