package main

import (
	"context"
	"log"
	"pointmarket/backend/internal/config"
	"pointmarket/backend/internal/database"
	"pointmarket/backend/internal/services"
	"pointmarket/backend/internal/store/gen"
)

func main() {
	cfg := config.Init()
	db := database.Connect(cfg)

	querier := gen.New(db)
	userService := services.NewUserService(querier)
	questionnaireService := services.NewQuestionnaireService(querier)
	weeklyEvaluationService := services.NewWeeklyEvaluationService(querier, userService, questionnaireService)

	log.Println("Starting weekly evaluation initialization...")

	err := weeklyEvaluationService.InitializeWeeklyEvaluations(context.Background())
	if err != nil {
		log.Fatalf("Failed to initialize weekly evaluations: %v", err)
	}

	log.Println("Weekly evaluations initialized successfully.")
}
