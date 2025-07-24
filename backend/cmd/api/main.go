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
	userService := services.NewUserService(userStore)
	assignmentService := services.NewAssignmentService(assignmentStore)
	quizService := services.NewQuizService(quizStore)
	questionnaireService := services.NewQuestionnaireService(questionnaireStore)
	varkService := services.NewVARKService(varkStore)
	nlpService := services.NewNLPService(nlpStore)

	// Initialize handlers
	authHandler := handler.NewAuthHandler(*authService)
	userHandler := handler.NewUserHandler(*userService)
	assignmentHandler := handler.NewAssignmentHandler(*assignmentService)
	quizHandler := handler.NewQuizHandler(*quizService)
	questionnaireHandler := handler.NewQuestionnaireHandler(*questionnaireService)
	varkHandler := handler.NewVARKHandler(*varkService)
	nlpHandler := handler.NewNLPHandler(*nlpService)

	r := gin.Default()

	// Health check endpoint
	r.GET("/ping", func(c *gin.Context) {
		c.JSON(200, gin.H{
			"message": "pong",
		})
	})

	// API v1 group
	v1 := r.Group("/api/v1")

	// Authentication routes
	authRoutes := v1.Group("/auth")
	{
		authRoutes.POST("/register", authHandler.Register)
		authRoutes.POST("/login", authHandler.Login)
	}

	// Authenticated routes
	authRequired := v1.Group("/")
	authRequired.Use(middleware.AuthMiddleware(cfg, db))
	{
		// User routes
		authRequired.GET("/profile", userHandler.GetUserProfile)

		// Assignment routes
		assignmentRoutes := authRequired.Group("/assignments")
		{
			assignmentRoutes.GET("", assignmentHandler.GetAllAssignments)
			assignmentRoutes.POST("", assignmentHandler.CreateAssignment)
			assignmentRoutes.GET("/:id", assignmentHandler.GetAssignmentByID)
			assignmentRoutes.PUT("/:id", assignmentHandler.UpdateAssignment)
			assignmentRoutes.DELETE("/:id", assignmentHandler.DeleteAssignment)
		}

		// Quiz routes
		quizRoutes := authRequired.Group("/quizzes")
		{
			quizRoutes.GET("", quizHandler.GetAllQuizzes)
			quizRoutes.POST("", quizHandler.CreateQuiz)
			quizRoutes.GET("/:id", quizHandler.GetQuizByID)
			quizRoutes.PUT("/:id", quizHandler.UpdateQuiz)
			quizRoutes.DELETE("/:id", quizHandler.DeleteQuiz)
		}

		// Questionnaire routes
		questionnaireRoutes := authRequired.Group("/questionnaires")
		{
			questionnaireRoutes.GET("/:id", questionnaireHandler.GetQuestionnaireByID)
			questionnaireRoutes.POST("/submit", questionnaireHandler.SubmitQuestionnaire)
		}

		// VARK routes
		varkRoutes := authRequired.Group("/vark")
		{
			varkRoutes.GET("", varkHandler.GetVARKQuestions)
			varkRoutes.POST("/submit", varkHandler.SubmitVARK)
			varkRoutes.GET("/latest", varkHandler.GetLatestVARKResult)
		}

		// NLP routes
		nlpRoutes := authRequired.Group("/nlp")
		{
			nlpRoutes.POST("/analyze", nlpHandler.AnalyzeText)
			nlpRoutes.GET("/stats", nlpHandler.GetNLPStats)
		}
	}

	serverAddr := fmt.Sprintf(":%d", cfg.ServerPort)
	if err := r.Run(serverAddr); err != nil {
		panic(fmt.Sprintf("failed to start server: %v", err))
	}
}