package main

import (
	"log"
	"pointmarket/backend/internal/config"
	"pointmarket/backend/internal/database"
	"pointmarket/backend/internal/services"
	"pointmarket/backend/internal/store"
	"time"
)

func main() {
	cfg := config.Init()
	db := database.Connect(cfg)

	weeklyEvaluationStore := store.NewWeeklyEvaluationStore(db)
	weeklyEvaluationService := services.NewWeeklyEvaluationService(weeklyEvaluationStore)

	log.Println("Weekly evaluation scheduler started. Waiting for tasks...")

	for {
		now := time.Now()
		// Calculate the start of today (midnight)
		todayMidnight := time.Date(now.Year(), now.Month(), now.Day(), 0, 0, 0, 0, now.Location())

		// Calculate days until next Monday
		// time.Monday is 1, time.Sunday is 0.
		daysToAdd := (int(time.Monday) - int(todayMidnight.Weekday()) + 7) % 7

		// If today is Monday and it's already past midnight, schedule for next Monday
		if daysToAdd == 0 && now.After(todayMidnight) {
			daysToAdd = 7
		}

		nextRun := todayMidnight.AddDate(0, 0, daysToAdd)

		durationUntilNextRun := nextRun.Sub(now)

		// Handle cases where duration might be negative (e.g., if the task just ran and it's still the same Monday)
		if durationUntilNextRun < 0 {
			// This means we've already passed the target time for this week. Schedule for next week.
			nextRun = nextRun.AddDate(0, 0, 7)
			durationUntilNextRun = nextRun.Sub(now)
		}

		log.Printf("Next weekly evaluation run scheduled for: %s (in %s)", nextRun.Format(time.RFC3339), durationUntilNextRun)

		time.Sleep(durationUntilNextRun)

		log.Println("Running weekly evaluation generation and overdue update...")
		err := weeklyEvaluationService.GenerateAndOverdueWeeklyEvaluations()
		if err != nil {
			log.Printf("Error running weekly evaluation task: %v", err)
		} else {
			log.Println("Weekly evaluation task completed successfully.")
		}
	}
}
