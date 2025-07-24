package main

import (
	"fmt"
	"pointmarket/backend/internal/config"
	"pointmarket/backend/internal/database"
	"pointmarket/backend/internal/handler"
	"pointmarket/backend/internal/middleware"
	"pointmarket/backend/internal/services"
	"pointmarket/backend/internal/store"

	"github.com/gin-gonic/gin"
)

func main() {
	cfg := config.Init()
	db := database.Connect(cfg)

	// Initialize stores
	userStore := store.NewUserStore(db)
	assignmentStore := store.NewAssignmentStore(db)
	quizStore := store.NewQuizStore(db)
	questionnaireStore := store.NewQuestionnaireStore(db)
	varkStore := store.NewVARKStore(db)
	nlpStore := store.NewNLPStore(db)

	// Initialize services
	authService := services.NewAuthService(userStore, cfg)
	assignmentService := services.NewAssignmentService(assignmentStore)
	quizService := services.NewQuizService(quizStore)
	questionnaireService := services.NewQuestionnaireService(questionnaireStore)
	varkService := services.NewVARKService(varkStore)
	nlpService := services.NewNLPService(nlpStore, varkStore, questionnaireStore)

	// Initialize API handlers
	apiHandler := handler.NewHandler(authService, assignmentService, quizService, questionnaireService, varkService, nlpService)

	r := gin.Default()

	// Health check endpoint
	r.GET("/ping", func(c *gin.Context) {
		c.JSON(200, gin.H{
			"message": "pong",
		})
	})

	// Authentication routes
	authRoutes := r.Group("/auth")
	{
		authRoutes.POST("/login", apiHandler.Login)
	}

	// Authenticated routes
	authRequired := r.Group("/")
	authRequired.Use(middleware.AuthMiddleware(cfg, db))
	{
		authRequired.GET("/profile", apiHandler.GetUserProfile)

		// Assignment routes
		authRequired.POST("/assignments", apiHandler.CreateAssignment)
		authRequired.GET("/assignments/:id", apiHandler.GetAssignment)
		authRequired.PUT("/assignments/:id", apiHandler.UpdateAssignment)
		authRequired.DELETE("/assignments/:id", apiHandler.DeleteAssignment)
		authRequired.GET("/assignments", apiHandler.ListAssignments)

		// Quiz routes
		authRequired.POST("/quizzes", apiHandler.CreateQuiz)
		authRequired.GET("/quizzes/:id", apiHandler.GetQuiz)
		authRequired.PUT("/quizzes/:id", apiHandler.UpdateQuiz)
		authRequired.DELETE("/quizzes/:id", apiHandler.DeleteQuiz)
		authRequired.GET("/quizzes", apiHandler.ListQuizzes)

		// Questionnaire routes
		authRequired.GET("/questionnaires/:id", apiHandler.GetQuestionnaire)
		authRequired.POST("/questionnaires/submit", apiHandler.SubmitQuestionnaire)

		// VARK routes
		authRequired.GET("/vark", apiHandler.GetVARKAssessment)
		authRequired.POST("/vark/submit", apiHandler.SubmitVARK)
		authRequired.GET("/vark/latest", apiHandler.GetLatestVARKResult)

		// NLP routes
		authRequired.POST("/nlp/analyze", apiHandler.AnalyzeText)
		authRequired.GET("/nlp/stats", apiHandler.GetNLPStats)
	}

	serverAddr := fmt.Sprintf(":%d", cfg.ServerPort)
	if err := r.Run(serverAddr); err != nil {
		panic(fmt.Sprintf("failed to start server: %v", err))
	}
}
