package services

import (
	"context"
	"database/sql"
	"errors"
	"fmt"
	"pointmarket/backend/internal/store/gen"
	"time"
)

// WeeklyEvaluationService provides business logic for weekly evaluations
type WeeklyEvaluationService struct {
	q                    gen.Querier
	userService          *UserService
	questionnaireService *QuestionnaireService
}

// NewWeeklyEvaluationService creates a new instance of WeeklyEvaluationService
func NewWeeklyEvaluationService(
	q gen.Querier,
	userService *UserService,
	questionnaireService *QuestionnaireService,
) *WeeklyEvaluationService {
	return &WeeklyEvaluationService{
		q:                    q,
		userService:          userService,
		questionnaireService: questionnaireService,
	}
}

// GetWeeklyEvaluationsByStudentID retrieves all weekly evaluations for a given student within a specified number of weeks
func (s *WeeklyEvaluationService) GetWeeklyEvaluationsByStudentID(
	ctx context.Context,
	studentID int64,
	numberOfWeeks int32,
) ([]gen.WeeklyEvaluation, error) {
	evaluations, err := s.q.GetWeeklyEvaluationsByStudentID(ctx, gen.GetWeeklyEvaluationsByStudentIDParams{
		StudentID: studentID,
		DATESUB:   numberOfWeeks,
	})
	if err != nil {
		return nil, fmt.Errorf("failed to get weekly evaluations for student %d: %w", studentID, err)
	}
	return evaluations, nil
}

// GetWeeklyEvaluationsForTeacherDashboard retrieves aggregated data for the teacher dashboard
func (s *WeeklyEvaluationService) GetWeeklyEvaluationsForTeacherDashboard(
	ctx context.Context,
	numberOfWeeks int32,
) ([]gen.GetWeeklyEvaluationsForTeacherDashboardRow, error) {
	evaluations, err := s.q.GetWeeklyEvaluationsForTeacherDashboard(ctx, numberOfWeeks)
	if err != nil {
		return nil, fmt.Errorf("failed to get weekly evaluations for teacher dashboard: %w", err)
	}
	return evaluations, nil
}

// GenerateAndOverdueWeeklyEvaluations is called by the scheduler to create new evaluations and mark old ones as overdue
func (s *WeeklyEvaluationService) GenerateAndOverdueWeeklyEvaluations(ctx context.Context) error {
	// 1. Mark overdue evaluations
	err := s.q.MarkOverdueWeeklyEvaluations(ctx)
	if err != nil {
		return fmt.Errorf("failed to mark overdue weekly evaluations: %w", err)
	}
	fmt.Println("Marked overdue weekly evaluations.")

	// 2. Get all active students
	students, err := s.userService.GetActiveStudents(ctx)
	if err != nil {
		return fmt.Errorf("failed to get all active students: %w", err)
	}
	if len(students) == 0 {
		fmt.Println("No active students found to generate weekly evaluations for.")
		return nil
	}

	// 3. Get MSLQ and AMS questionnaire IDs
	mslq, err := s.questionnaireService.GetQuestionnaireByType(ctx, gen.QuestionnairesTypeMSLQ)
	if err != nil {
		return fmt.Errorf("failed to get MSLQ questionnaire: %w", err)
	}
	ams, err := s.questionnaireService.GetQuestionnaireByType(ctx, gen.QuestionnairesTypeAMS)
	if err != nil {
		return fmt.Errorf("failed to get AMS questionnaire: %w", err)
	}

	questionnairesToAssign := []struct {
		ID   int32
		Type gen.QuestionnairesType
	}{
		{ID: mslq.ID, Type: gen.QuestionnairesTypeMSLQ},
		{ID: ams.ID, Type: gen.QuestionnairesTypeAMS},
	}

	// 4. Calculate the upcoming week's due date (end of current week, e.g., Sunday 23:59:59)
	now := time.Now()
	// Find the end of the current week (Sunday)
	daysUntilSunday := (int(time.Sunday) - int(now.Weekday()) + 7) % 7
	if daysUntilSunday == 0 { // If today is Sunday, due date is end of today
		daysUntilSunday = 7 // Due date is next Sunday
	}
	dueDate := time.Date(now.Year(), now.Month(), now.Day(), 23, 59, 59, 0, now.Location()).AddDate(0, 0, daysUntilSunday)

	fmt.Printf("Generating weekly evaluations for due date: %s\n", dueDate.Format("2006-01-02"))

	// 5. Loop through students and assign evaluations
	for _, student := range students {
		for _, q := range questionnairesToAssign {
			// Check if an evaluation already exists for this student, questionnaire, and due date
			_, err := s.q.GetWeeklyEvaluationByStudentAndQuestionnaireAndDueDate(ctx, gen.GetWeeklyEvaluationByStudentAndQuestionnaireAndDueDateParams{
				StudentID:       student.ID,
				QuestionnaireID: q.ID,
				DueDate:         dueDate,
			})
			if err != nil {
				if errors.Is(err, sql.ErrNoRows) {
					// Evaluation does not exist, create it
					err = s.q.CreateWeeklyEvaluation(ctx, gen.CreateWeeklyEvaluationParams{
						StudentID:       student.ID,
						QuestionnaireID: q.ID,
						Status:          gen.WeeklyEvaluationsStatusPending,
						DueDate:         dueDate,
					})
					if err != nil {
						fmt.Printf("Error creating weekly evaluation for student %d, questionnaire %s: %v\n", student.ID, q.Type, err)
					} else {
						fmt.Printf("Created weekly evaluation for student %d, questionnaire %s.\n", student.ID, q.Type)
					}
				} else {
					fmt.Printf("Error checking existing weekly evaluation for student %d, questionnaire %s: %v\n", student.ID, q.Type, err)
				}
			} else {
				fmt.Printf("Weekly evaluation already exists for student %d, questionnaire %s for this week.\n", student.ID, q.Type)
			}
		}
	}

	fmt.Println("Finished generating weekly evaluations.")
	return nil
}

// InitializeWeeklyEvaluations is a one-time function to set up initial weekly evaluations
func (s *WeeklyEvaluationService) InitializeWeeklyEvaluations(ctx context.Context) error {
	fmt.Println("Initializing weekly evaluations...")

	// 1. Get MSLQ and AMS questionnaire IDs
	mslq, err := s.questionnaireService.GetQuestionnaireByType(ctx, gen.QuestionnairesTypeMSLQ)
	if err != nil {
		return fmt.Errorf("failed to get MSLQ questionnaire: %w", err)
	}
	ams, err := s.questionnaireService.GetQuestionnaireByType(ctx, gen.QuestionnairesTypeAMS)
	if err != nil {
		return fmt.Errorf("failed to get AMS questionnaire: %w", err)
	}

	questionnairesToAssign := []struct {
		ID   int32
		Type gen.QuestionnairesType
	}{
		{ID: mslq.ID, Type: gen.QuestionnairesTypeMSLQ},
		{ID: ams.ID, Type: gen.QuestionnairesTypeAMS},
	}

	// 2. Get all active students
	students, err := s.userService.GetActiveStudents(ctx)
	if err != nil {
		return fmt.Errorf("failed to get all active students: %w", err)
	}
	if len(students) == 0 {
		fmt.Println("No active students found to initialize weekly evaluations for.")
		return nil
	}

	// 3. Calculate the current week's due date (end of current week, e.g., Sunday 23:59:59)
	now := time.Now()
	daysUntilSunday := (int(time.Sunday) - int(now.Weekday()) + 7) % 7
	if daysUntilSunday == 0 {
		daysUntilSunday = 7
	}
	dueDate := time.Date(now.Year(), now.Month(), now.Day(), 23, 59, 59, 0, now.Location()).AddDate(0, 0, daysUntilSunday)

	fmt.Printf("Initializing weekly evaluations for due date: %s\n", dueDate.Format("2006-01-02"))

	// 4. Loop through students and assign evaluations for the current week
	for _, student := range students {
		for _, q := range questionnairesToAssign {
			// Check if an evaluation already exists for this student, questionnaire, and due date
			_, err := s.q.GetWeeklyEvaluationByStudentAndQuestionnaireAndDueDate(ctx, gen.GetWeeklyEvaluationByStudentAndQuestionnaireAndDueDateParams{
				StudentID:       student.ID,
				QuestionnaireID: q.ID,
				DueDate:         dueDate,
			})
			if err != nil {
				if errors.Is(err, sql.ErrNoRows) {
					// Evaluation does not exist, create it
					err = s.q.CreateWeeklyEvaluation(ctx, gen.CreateWeeklyEvaluationParams{
						StudentID:       student.ID,
						QuestionnaireID: q.ID,
						Status:          gen.WeeklyEvaluationsStatusPending,
						DueDate:         dueDate,
					})
					if err != nil {
						fmt.Printf("Error creating initial weekly evaluation for student %d, questionnaire %s: %v\n", student.ID, q.Type, err)
					} else {
						fmt.Printf("Created initial weekly evaluation for student %d, questionnaire %s.\n", student.ID, q.Type)
					}
				} else {
					fmt.Printf("Error checking existing initial weekly evaluation for student %d, questionnaire %s: %v\n", student.ID, q.Type, err)
				}
			} else {
				fmt.Printf("Initial weekly evaluation already exists for student %d, questionnaire %s for this week.\n", student.ID, q.Type)
			}
		}
	}

	fmt.Println("Finished initializing weekly evaluations.")
	return nil
}
