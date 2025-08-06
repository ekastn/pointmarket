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
	weeklyEvaluationStore := store.NewWeeklyEvaluationStore(db)
	textAnalysisSnapshotStore := store.NewTextAnalysisSnapshotStore(db)

	// Initialize gateways
	aiServiceGateway := gateway.NewAIServiceGateway(cfg.AIServiceURL)

	// Initialize services
	authService := services.NewAuthService(userStore, cfg)
	userService := services.NewUserService(userStore)
	assignmentService := services.NewAssignmentService(assignmentStore, userStore)
	quizService := services.NewQuizService(quizStore)
	questionnaireService := services.NewQuestionnaireService(questionnaireStore, varkStore, weeklyEvaluationStore)

	// Initialize nlpService before varkService as varkService now depends on it
	nlpService := services.NewNLPService(nlpStore, varkStore, aiServiceGateway, textAnalysisSnapshotStore)
	varkService := services.NewVARKService(varkStore, nlpService)

	materialService := services.NewMaterialService(materialStore)
	weeklyEvaluationService := services.NewWeeklyEvaluationService(weeklyEvaluationStore)
	correlationService := services.NewCorrelationService(varkStore, questionnaireStore)

	// Initialize handlers
	authHandler := handler.NewAuthHandler(*authService)
	correlationHandler := handler.NewCorrelationHandler(*correlationService)
	userHandler := handler.NewUserHandler(*userService)
	assignmentHandler := handler.NewAssignmentHandler(*assignmentService)
	quizHandler := handler.NewQuizHandler(*quizService)
	questionnaireHandler := handler.NewQuestionnaireHandler(*questionnaireService)
	// Pass nlpService to NewVARKHandler
	varkHandler := handler.NewVARKHandler(*varkService, *nlpService)
	nlpHandler := handler.NewNLPHandler(*nlpService)
	materialHandler := handler.NewMaterialHandler(*materialService)
	weeklyEvaluationHandler := handler.NewWeeklyEvaluationHandler(weeklyEvaluationService)

	// Initialize DashboardService and DashboardHandler
	dashboardService := services.NewDashboardService(
		*userService,
		*nlpService,
		*varkService,
		*questionnaireService,
		*weeklyEvaluationService,
	)
	dashboardHandler := handler.NewDashboardHandler(*dashboardService)

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
	authRequired.Use(middleware.Auth(cfg, db))

	adminRoutes := authRequired.Group("")
	adminRoutes.Use(middleware.Authz("admin"))

	{
		authRequired.GET("/profile", userHandler.GetUserProfile)
		authRequired.PUT("/profile", userHandler.UpdateUserProfile)

		userRoutes := adminRoutes.Group("/users")
		{
			userRoutes.GET("", userHandler.GetAllUsers)
			userRoutes.GET("/:id", userHandler.GetUserByID)
			userRoutes.PUT("/:id/role", userHandler.UpdateUserRole)
			userRoutes.DELETE("/:id", userHandler.DeleteUser)
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
			questionnaireRoutes.GET("/latest-by-type", questionnaireHandler.GetLatestQuestionnaireResultByType)
		}

		// VARK routes
		varkRoutes := authRequired.Group("/vark")
		{

			varkRoutes.POST("/submit", varkHandler.SubmitVARK)
			varkRoutes.GET("/latest", varkHandler.GetLatestVARKResult)
		}

		// NLP routes
		nlpRoutes := authRequired.Group("/nlp")
		{
			nlpRoutes.POST("/analyze", nlpHandler.AnalyzeText)
			nlpRoutes.GET("/stats", nlpHandler.GetNLPStats)
			nlpRoutes.GET("/latest-snapshot", nlpHandler.GetLatestTextAnalysisSnapshot)
		}

		// Dashboard routes
		dashboardRoutes := authRequired.Group("/dashboard")
		{
			dashboardRoutes.GET("/all", dashboardHandler.GetComprehensiveDashboardData)
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

		// Correlation routes
		correlationRoutes := authRequired.Group("/correlation")
		{
			correlationRoutes.GET("/analyze", correlationHandler.AnalyzeCorrelation)
		}
	}

	serverAddr := fmt.Sprintf(":%d", cfg.ServerPort)
	if err := r.Run(serverAddr); err != nil {
		panic(fmt.Sprintf("failed to start server: %v", err))
	}
}
