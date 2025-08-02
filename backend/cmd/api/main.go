package main

import (
	"fmt"
	"pointmarket/backend/internal/config"
	"pointmarket/backend/internal/database"
	"pointmarket/backend/internal/gateway"
	"pointmarket/backend/internal/handler"
	"pointmarket/backend/internal/middleware"
	"pointmarket/backend/internal/services"
	"pointmarket/backend/internal/store"
	"strings"
	"time"

	"github.com/gin-contrib/cors"
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
	materialStore := store.NewMaterialStore(db)

	// Initialize gateways
	aiServiceGateway := gateway.NewAIServiceGateway(cfg.AIServiceURL)

	// Initialize services
	authService := services.NewAuthService(userStore, cfg)
	userService := services.NewUserService(userStore)
	assignmentService := services.NewAssignmentService(assignmentStore, userStore)
	quizService := services.NewQuizService(quizStore)
	questionnaireService := services.NewQuestionnaireService(questionnaireStore, varkStore)
	varkService := services.NewVARKService(varkStore)
	nlpService := services.NewNLPService(nlpStore, aiServiceGateway)
	materialService := services.NewMaterialService(materialStore)

	// Initialize stores
	weeklyEvaluationStore := store.NewWeeklyEvaluationStore(db)

	// Initialize services
	weeklyEvaluationService := services.NewWeeklyEvaluationService(weeklyEvaluationStore)

	// Initialize handlers
	authHandler := handler.NewAuthHandler(*authService)
	userHandler := handler.NewUserHandler(*userService)
	assignmentHandler := handler.NewAssignmentHandler(*assignmentService)
	quizHandler := handler.NewQuizHandler(*quizService)
	questionnaireHandler := handler.NewQuestionnaireHandler(*questionnaireService)
	varkHandler := handler.NewVARKHandler(*varkService)
	nlpHandler := handler.NewNLPHandler(*nlpService)
	materialHandler := handler.NewMaterialHandler(*materialService)
	weeklyEvaluationHandler := handler.NewWeeklyEvaluationHandler(weeklyEvaluationService)

	r := gin.Default()

	// CORS middleware
	r.Use(cors.New(cors.Config{
		AllowOrigins:     strings.Split(cfg.AllowedOrigins, ","),
		AllowMethods:     []string{"GET", "POST", "PUT", "DELETE", "OPTIONS"},
		AllowHeaders:     []string{"Origin", "Content-Type", "Authorization"},
		ExposeHeaders:    []string{"Content-Length"},
		AllowCredentials: true,
		MaxAge:           12 * time.Hour,
	}))

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
		authRequired.PUT("/profile", userHandler.UpdateUserProfile)

		// Admin User Management routes
		adminRoutes := authRequired.Group("/admin/users")
		{
			adminRoutes.GET("", userHandler.GetAllUsers)
			adminRoutes.GET("/:id", userHandler.GetUserByID)
			adminRoutes.PUT("/:id/role", userHandler.UpdateUserRole)
			adminRoutes.DELETE("/:id", userHandler.DeleteUser)
		}

		// Assignment routes
		assignmentRoutes := authRequired.Group("/assignments")
		{
			assignmentRoutes.GET("", assignmentHandler.GetAllAssignments)
			assignmentRoutes.POST("", assignmentHandler.CreateAssignment)
			assignmentRoutes.GET("/:id", assignmentHandler.GetAssignmentByID)
			assignmentRoutes.PUT("/:id", assignmentHandler.UpdateAssignment)
			assignmentRoutes.DELETE("/:id", assignmentHandler.DeleteAssignment)
			assignmentRoutes.POST("/:id/start", assignmentHandler.StartAssignment)
			assignmentRoutes.POST("/:id/submit", assignmentHandler.SubmitAssignment)
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
			questionnaireRoutes.GET("", questionnaireHandler.GetAllQuestionnaires)
			questionnaireRoutes.GET("/:id", questionnaireHandler.GetQuestionnaireByID)
			questionnaireRoutes.POST("/submit", questionnaireHandler.SubmitQuestionnaire)
			questionnaireRoutes.GET("/history", questionnaireHandler.GetQuestionnaireHistory)
			questionnaireRoutes.GET("/stats", questionnaireHandler.GetQuestionnaireStats)
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

		// Dashboard routes
		dashboardRoutes := authRequired.Group("/dashboard")
		{
			dashboardRoutes.GET("/student/stats", userHandler.GetStudentDashboardStats)
			dashboardRoutes.GET("/admin/counts", userHandler.GetAdminDashboardCounts)
			dashboardRoutes.GET("/teacher/counts", userHandler.GetTeacherDashboardCounts)
			dashboardRoutes.GET("/student/assignments/stats", userHandler.GetAssignmentStatsByStudentID)
			dashboardRoutes.GET("/student/activity", userHandler.GetRecentActivityByUserID)
		}

		// Material routes
		materialRoutes := authRequired.Group("/materials")
		{
			materialRoutes.GET("", materialHandler.GetAllMaterials)
			materialRoutes.POST("", materialHandler.CreateMaterial)
			materialRoutes.GET("/:id", materialHandler.GetMaterialByID)
			materialRoutes.PUT("/:id", materialHandler.UpdateMaterial)
			materialRoutes.DELETE("/:id", materialHandler.DeleteMaterial)
		}

		// Weekly Evaluation routes
		weeklyEvaluationRoutes := authRequired.Group("/evaluations/weekly")
		{
			weeklyEvaluationRoutes.GET("/student/progress", weeklyEvaluationHandler.GetWeeklyEvaluationProgressByStudentID)
			weeklyEvaluationRoutes.GET("/student/pending", weeklyEvaluationHandler.GetPendingWeeklyEvaluationsByStudentID)
			weeklyEvaluationRoutes.GET("/teacher/overview", weeklyEvaluationHandler.GetWeeklyEvaluationOverview)
			weeklyEvaluationRoutes.GET("/teacher/status", weeklyEvaluationHandler.GetStudentEvaluationStatus)
		}
	}

	serverAddr := fmt.Sprintf(":%d", cfg.ServerPort)
	if err := r.Run(serverAddr); err != nil {
		panic(fmt.Sprintf("failed to start server: %v", err))
	}
}
