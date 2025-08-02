package services

import (
	"fmt"
	"log"
	"pointmarket/backend/internal/models"
	"pointmarket/backend/internal/store"
	"pointmarket/backend/internal/utils"
	"time"
)

type WeeklyEvaluationService struct {
	store *store.WeeklyEvaluationStore
}

func NewWeeklyEvaluationService(store *store.WeeklyEvaluationStore) *WeeklyEvaluationService {
	return &WeeklyEvaluationService{store: store}
}

func (s *WeeklyEvaluationService) GetStudentEvaluationStatus() ([]models.StudentEvaluationStatus, error) {
	_, week := time.Now().ISOWeek()
	year := time.Now().Year()
	return s.store.GetStudentEvaluationStatus(week, year)
}

func (s *WeeklyEvaluationService) GetWeeklyEvaluationOverview(weeks int) ([]models.WeeklyEvaluationOverview, error) {
	return s.store.GetWeeklyEvaluationOverview(weeks)
}

func (s *WeeklyEvaluationService) GetWeeklyEvaluationProgressByStudentID(studentID, weeks int) ([]models.WeeklyEvaluationProgress, error) {
	return s.store.GetWeeklyEvaluationProgressByStudentID(studentID, weeks)
}

func (s *WeeklyEvaluationService) GetPendingWeeklyEvaluationsByStudentID(studentID int) ([]models.WeeklyEvaluationProgress, error) {
	return s.store.GetPendingWeeklyEvaluationsByStudentID(studentID)
}

// InitializeWeeklyEvaluations creates pending weekly evaluations for all active students for the current week.
func (s *WeeklyEvaluationService) InitializeWeeklyEvaluations() error {
	log.Println("Starting InitializeWeeklyEvaluations...")
	currentYear, currentWeek := utils.GetCurrentWeekAndYear()

	students, err := s.store.GetAllActiveStudents()
	if err != nil {
		return fmt.Errorf("failed to get all active students: %w", err)
	}

	mslqQuestionnaire, err := s.store.GetQuestionnairesByType("mslq")
	if err != nil || len(mslqQuestionnaire) == 0 {
		return fmt.Errorf("failed to get MSLQ questionnaire: %w", err)
	}
	amsQuestionnaire, err := s.store.GetQuestionnairesByType("ams")
	if err != nil || len(amsQuestionnaire) == 0 {
		return fmt.Errorf("failed to get AMS questionnaire: %w", err)
	}

	questionnairesToAssign := []models.Questionnaire{
		mslqQuestionnaire[0],
		amsQuestionnaire[0],
	}

	for _, student := range students {
		for _, q := range questionnairesToAssign {
			// Check if an evaluation for this student, week, year, and questionnaire already exists
			existingEval, err := s.store.GetWeeklyEvaluationByStudentWeekYearAndQuestionnaire(
				student.ID,
				currentWeek,
				currentYear,
				q.ID,
			)
			if err != nil {
				log.Printf("Error checking for existing evaluation for student %d, week %d, year %d, questionnaire %d: %v", student.ID, currentWeek, currentYear, q.ID, err)
				continue
			}
			if existingEval != nil {
				log.Printf("Evaluation for student %d, week %d, year %d, questionnaire %d already exists (status: %s). Skipping.", student.ID, currentWeek, currentYear, q.ID, existingEval.Status)
				continue
			}

			// Calculate due date (e.g., end of current week)
			dueDate := utils.GetEndOfCurrentWeek()

			newEval := models.WeeklyEvaluationProgress{
				StudentID:         student.ID,
				QuestionnaireID:   q.ID,
				WeekNumber:        currentWeek,
				Year:              currentYear,
				QuestionnaireType: q.Type,
				QuestionnaireName: q.Name,
				Status:            "pending",
				DueDate:           &dueDate,
				CreatedAt:         time.Now(),
				UpdatedAt:         time.Now(),
			}

			log.Printf("Creating new pending evaluation for student %d, week %d, year %d, questionnaire %s.", student.ID, currentWeek, currentYear, q.Name)
			err = s.store.CreateWeeklyEvaluation(newEval)
			if err != nil {
				log.Printf("Error creating weekly evaluation for student %d, questionnaire %s: %v", student.ID, q.Name, err)
			}
		}
	}
	log.Println("Finished initializing weekly evaluations.")
	return nil
}

// GenerateAndOverdueWeeklyEvaluations orchestrates the generation of new weekly evaluations
// and updates the status of overdue evaluations.
func (s *WeeklyEvaluationService) GenerateAndOverdueWeeklyEvaluations() error {
	log.Println("Starting GenerateAndOverdueWeeklyEvaluations...")

	// 1. Update overdue evaluations
	log.Println("Updating overdue evaluations...")
	overdueDate := time.Now().Add(-24 * time.Hour) // Evaluations due yesterday or earlier are overdue
	overdueEvals, err := s.store.GetPendingEvaluationsDueBefore(overdueDate)
	if err != nil {
		return fmt.Errorf("failed to get pending evaluations due before %s: %w", overdueDate.Format(time.RFC3339), err)
	}

	for _, eval := range overdueEvals {
		log.Printf("Marking evaluation ID %d for student %d as overdue.", eval.ID, eval.StudentID)
		err := s.store.UpdateWeeklyEvaluationStatus(eval.ID, "overdue")
		if err != nil {
			log.Printf("Error marking evaluation ID %d as overdue: %v", eval.ID, err)
		}
	}
	log.Printf("Finished updating %d overdue evaluations.", len(overdueEvals))

	// 2. Generate new weekly evaluations
	log.Println("Generating new weekly evaluations...")
	currentYear, currentWeek := utils.GetCurrentWeekAndYear()

	students, err := s.store.GetAllActiveStudents()
	if err != nil {
		return fmt.Errorf("failed to get all active students: %w", err)
	}

	mslqQuestionnaire, err := s.store.GetQuestionnairesByType("mslq")
	if err != nil || len(mslqQuestionnaire) == 0 {
		return fmt.Errorf("failed to get MSLQ questionnaire: %w", err)
	}
	amsQuestionnaire, err := s.store.GetQuestionnairesByType("ams")
	if err != nil || len(amsQuestionnaire) == 0 {
		return fmt.Errorf("failed to get AMS questionnaire: %w", err)
	}

	questionnairesToAssign := []models.Questionnaire{
		mslqQuestionnaire[0],
		amsQuestionnaire[0],
	}

	for _, student := range students {
		for _, q := range questionnairesToAssign {
			// Check if an evaluation for this student, week, year, and questionnaire already exists
			existingEval, err := s.store.GetWeeklyEvaluationByStudentWeekYearAndQuestionnaire(
				student.ID,
				currentWeek,
				currentYear,
				q.ID,
			)
			if err != nil {
				log.Printf("Error checking for existing evaluation for student %d, week %d, year %d, questionnaire %d: %v", student.ID, currentWeek, currentYear, q.ID, err)
				continue
			}
			if existingEval != nil {
				log.Printf("Evaluation for student %d, week %d, year %d, questionnaire %d already exists (status: %s). Skipping.", student.ID, currentWeek, currentYear, q.ID, existingEval.Status)
				continue
			}

			// Calculate due date (e.g., end of current week)
			dueDate := utils.GetEndOfCurrentWeek()

			newEval := models.WeeklyEvaluationProgress{
				StudentID:         student.ID,
				QuestionnaireID:   q.ID,
				WeekNumber:        currentWeek,
				Year:              currentYear,
				QuestionnaireType: q.Type,
				QuestionnaireName: q.Name,
				Status:            "pending",
				DueDate:           &dueDate,
				CreatedAt:         time.Now(),
				UpdatedAt:         time.Now(),
			}

			log.Printf("Creating new pending evaluation for student %d, week %d, year %d, questionnaire %s.", student.ID, currentWeek, currentYear, q.Name)
			err = s.store.CreateWeeklyEvaluation(newEval)
			if err != nil {
				log.Printf("Error creating weekly evaluation for student %d, questionnaire %s: %v", student.ID, q.Name, err)
			}
		}
	}
	log.Println("Finished generating new weekly evaluations.")

	log.Println("GenerateAndOverdueWeeklyEvaluations completed.")
	return nil
}
