package services

import (
	"context"
	"database/sql"
	"errors"
	"fmt"
	"pointmarket/backend/internal/dtos"
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
// This method will now also augment completed evaluations with score and actual completed_at
func (s *WeeklyEvaluationService) GetWeeklyEvaluationsByStudentID(
	ctx context.Context,
	studentID int64,
	numberOfWeeks int32,
) ([]dtos.WeeklyEvaluationDetailDTO, error) {
	evaluations, err := s.q.GetWeeklyEvaluationsByStudentID(ctx, gen.GetWeeklyEvaluationsByStudentIDParams{
		UserID:  studentID,
		DATESUB: numberOfWeeks,
	})
	if err != nil {
		return nil, fmt.Errorf("failed to get weekly evaluations for student %d: %w", studentID, err)
	}

	var detailedEvaluations []dtos.WeeklyEvaluationDetailDTO
	for _, eval := range evaluations {
		detailedEval := dtos.FromWeeklyEvaluation(eval) // Convert to new DTO

		if eval.Status == gen.WeeklyEvaluationsStatusCompleted {
			// Fetch score and actual completed_at from questionnaire_results
			result, err := s.q.GetWeeklyEvaluationResult(ctx, sql.NullInt64{Int64: eval.ID, Valid: true})
			if err != nil {
				if errors.Is(err, sql.ErrNoRows) {
					fmt.Printf("Warning: No questionnaire result found for completed weekly evaluation ID %d\n", eval.ID)
				} else {
					return nil, fmt.Errorf("failed to get result for weekly evaluation %d: %w", eval.ID, err)
				}
			} else {
				detailedEval.Score = &result.Score
				if result.CompletedAt.Valid {
					detailedEval.CompletedAt = &result.CompletedAt.Time
				}
			}
		}
		detailedEvaluations = append(detailedEvaluations, detailedEval)
	}

	return detailedEvaluations, nil
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
				UserID:          student.ID,
				QuestionnaireID: q.ID,
				DueDate:         dueDate,
			})
			if err != nil {
				if errors.Is(err, sql.ErrNoRows) {
					// Evaluation does not exist, create it
					err = s.q.CreateWeeklyEvaluation(ctx, gen.CreateWeeklyEvaluationParams{
						UserID:          student.ID,
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
				UserID:          student.ID,
				QuestionnaireID: q.ID,
				DueDate:         dueDate,
			})
			if err != nil {
				if errors.Is(err, sql.ErrNoRows) {
					// Evaluation does not exist, create it
					err = s.q.CreateWeeklyEvaluation(ctx, gen.CreateWeeklyEvaluationParams{
						UserID:          student.ID,
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

// GetCurrentWeekEvaluationsByStudentID fetches MSLQ and AMS evaluations for the current week for a student.
// It ensures both types are returned, creating placeholders if one is missing.
func (s *WeeklyEvaluationService) GetCurrentWeekEvaluationsByStudentID(
	ctx context.Context,
	studentID int64,
) ([]dtos.WeeklyEvaluationDetailDTO, error) {
	// Calculate start and end of current week (Monday to Sunday)
	now := time.Now()
	startOfWeek := now.AddDate(0, 0, -int(now.Weekday())+1) // Monday
	endOfWeek := startOfWeek.AddDate(0, 0, 6)               // Sunday

	// Fetch all evaluations for the current week
	// We use a large number of weeks (e.g., 1) and filter by date range to ensure we get only current week's
	// Resolve the student's code (students.student_id) for display in placeholders
	var studentCode string
	if st, err := s.q.GetStudentByUserID(ctx, studentID); err == nil {
		studentCode = st.StudentID
	}

	allEvaluations, err := s.GetWeeklyEvaluationsByStudentID(ctx, studentID, 1) // Use the augmented method
	if err != nil {
		return nil, fmt.Errorf("failed to get all evaluations for current week: %w", err)
	}

	// Map evaluations by questionnaire title for easy access
	evalMap := make(map[string]dtos.WeeklyEvaluationDetailDTO)
	for _, eval := range allEvaluations {
		evalDueDate := eval.DueDate                                                                               // Assuming DueDate is already time.Time
		if evalDueDate.After(startOfWeek.Add(-24*time.Hour)) && evalDueDate.Before(endOfWeek.Add(24*time.Hour)) { // Check if within current week
			evalMap[eval.QuestionnaireTitle] = eval
		}
	}

	// Ensure both MSLQ and AMS are present, create placeholders if missing
	var currentWeekDashboardEvals []dtos.WeeklyEvaluationDetailDTO
	requiredQuestionnaires := map[gen.QuestionnairesType]string{ // Use type as key
		gen.QuestionnairesTypeMSLQ: "MSLQ Assessment", // Value is fallback title
		gen.QuestionnairesTypeAMS:  "AMS Assessment",  // Value is fallback title
	}

	for qType, fallbackTitle := range requiredQuestionnaires {
		foundEval := false
		for _, eval := range evalMap {
			if eval.QuestionnaireType == string(qType) {
				currentWeekDashboardEvals = append(currentWeekDashboardEvals, eval)
				foundEval = true
				break
			}
		}

		if !foundEval {
			// Create a placeholder for missing evaluation
			questionnaire, err := s.questionnaireService.GetQuestionnaireByType(ctx, qType) // Fetch full questionnaire details
			if err != nil {
				fmt.Printf("Warning: Could not find questionnaire definition for type %s: %v\n", qType, err)
				// Fallback to a generic placeholder if questionnaire lookup fails
				currentWeekDashboardEvals = append(currentWeekDashboardEvals, dtos.WeeklyEvaluationDetailDTO{
					QuestionnaireTitle: fallbackTitle, // Use fallback title
					QuestionnaireType:  string(qType),
					Status:             "not_assigned",
					DueDate:            endOfWeek, // Set due date to end of current week
				})
			} else {
				currentWeekDashboardEvals = append(currentWeekDashboardEvals, dtos.WeeklyEvaluationDetailDTO{
					StudentID:                studentCode,
					QuestionnaireID:          questionnaire.ID,
					QuestionnaireTitle:       questionnaire.Name,
					QuestionnaireType:        string(questionnaire.Type),
					QuestionnaireDescription: &questionnaire.Description.String,
					Status:                   "not_assigned",
					DueDate:                  endOfWeek, // Set due date to end of current week
				})
			}
		}
	}

	return currentWeekDashboardEvals, nil
}
