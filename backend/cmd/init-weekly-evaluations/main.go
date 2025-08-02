package main

import (
	"log"
	"pointmarket/backend/internal/config"
	"pointmarket/backend/internal/database"
	"pointmarket/backend/internal/services"
	"pointmarket/backend/internal/store"
)

func main() {
	cfg := config.Init()
	db := database.Connect(cfg)

	weeklyEvaluationStore := store.NewWeeklyEvaluationStore(db)
	weeklyEvaluationService := services.NewWeeklyEvaluationService(weeklyEvaluationStore)

	log.Println("Starting weekly evaluation initialization...")

	err := weeklyEvaluationService.InitializeWeeklyEvaluations()
	if err != nil {
		log.Fatalf("Failed to initialize weekly evaluations: %v", err)
	}

	log.Println("Weekly evaluations initialized successfully.")
}
