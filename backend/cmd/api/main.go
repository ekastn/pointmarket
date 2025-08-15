package main

import (
	"fmt"
	"pointmarket/backend/internal/config"
	"pointmarket/backend/internal/database"
	"pointmarket/backend/internal/gateway"
	"pointmarket/backend/internal/handler"
	"pointmarket/backend/internal/middleware"
	"pointmarket/backend/internal/services"
	"pointmarket/backend/internal/store/gen"
	"strings"
	"time"

	"github.com/gin-contrib/cors"
	"github.com/gin-gonic/gin"
)

func main() {
	cfg := config.Init()
	db := database.Connect(cfg)

	querier := gen.New(db)

	aiServiceGateway := gateway.NewAIServiceGateway(cfg.AIServiceURL)

	dashboardService := services.NewDashboardService(querier)
	authService := services.NewAuthService(cfg, querier)
	userService := services.NewUserService(querier)
	questionnaireService := services.NewQuestionnaireService(querier)
	weeklyEvaluationService := services.NewWeeklyEvaluationService(querier, userService, questionnaireService)
	correlationService := services.NewCorrelationService(querier)
	productService := services.NewProductService(querier)
	badgeService := services.NewBadgeService(querier)
	missionService := services.NewMissionService(querier)
	courseService := services.NewCourseService(querier)
	assignmentService := services.NewAssignmentService(querier)
	quizService := services.NewQuizService(querier)
	textAnalyzerService := services.NewTextAnalyzerService(aiServiceGateway, querier)

	authHandler := handler.NewAuthHandler(*authService)
	userHandler := handler.NewUserHandler(*userService)
	questionnaireHandler := handler.NewQuestionnaireHandler(questionnaireService, textAnalyzerService, correlationService)
	weeklyEvaluationHandler := handler.NewWeeklyEvaluationHandler(weeklyEvaluationService)

	dashboardHandler := handler.NewDashboardHandler(*dashboardService)
	productHandler := handler.NewProductHandler(*productService)
	badgeHandler := handler.NewBadgeHandler(*badgeService)
	missionHandler := handler.NewMissionHandler(*missionService)
	courseHandler := handler.NewCourseHandler(*courseService)
	assignmentHandler := handler.NewAssignmentHandler(assignmentService)
	quizHandler := handler.NewQuizHandler(quizService)

	r := gin.Default()

	r.Use(cors.New(cors.Config{
		AllowOrigins:     strings.Split(cfg.AllowedOrigins, ","),
		AllowMethods:     []string{"GET", "POST", "PUT", "DELETE", "OPTIONS"},
		AllowHeaders:     []string{"Origin", "Content-Type", "Authorization"},
		ExposeHeaders:    []string{"Content-Length"},
		AllowCredentials: true,
		MaxAge:           12 * time.Hour,
	}))

	// Health check endpoint
	r.GET("/health", func(c *gin.Context) {
		c.JSON(200, gin.H{
			"status": "ok",
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
		// authRequired.GET("/profile", userHandler.GetUserProfile)
		authRequired.PUT("/profile", userHandler.UpdateUserProfile)
		authRequired.GET("/dashboard", dashboardHandler.GetDashboardData)
		authRequired.GET("/roles", userHandler.GetRoles)

		userRoutes := adminRoutes.Group("/users")
		{
			userRoutes.GET("", userHandler.GetAllUsers)
			userRoutes.POST("", userHandler.CreateUser)
			userRoutes.GET("/:id", userHandler.GetUserByID)
			userRoutes.PUT("/:id", userHandler.UpdateUser)
			userRoutes.PUT("/:id/role", userHandler.UpdateUserRole)
			userRoutes.DELETE("/:id", userHandler.DeleteUser)
		}

		productRoutes := authRequired.Group("/products")
		{
			productRoutes.GET("", productHandler.GetProducts)
			productRoutes.GET("/:id", productHandler.GetProductByID)
		}

		badgesRoutes := authRequired.Group("/badges")
		{
			badgesRoutes.POST("", adminRoutes.Handlers[0], badgeHandler.CreateBadge) // Admin-only
			badgesRoutes.GET("", badgeHandler.GetBadges)
			badgesRoutes.GET("/:id", badgeHandler.GetBadgeByID)
			badgesRoutes.PUT("/:id", adminRoutes.Handlers[0], badgeHandler.UpdateBadge)           // Admin-only
			badgesRoutes.DELETE("/:id", adminRoutes.Handlers[0], badgeHandler.DeleteBadge)        // Admin-only
			badgesRoutes.POST("/:id/award", adminRoutes.Handlers[0], badgeHandler.AwardBadge)     // Admin-only
			badgesRoutes.DELETE("/:id/revoke", adminRoutes.Handlers[0], badgeHandler.RevokeBadge) // Admin-only
		}

		missionsRoutes := authRequired.Group("/missions")
		{
			missionsRoutes.POST("", adminRoutes.Handlers[0], missionHandler.CreateMission) // Admin-only
			missionsRoutes.GET("", missionHandler.GetMissions)
			missionsRoutes.GET("/:id", missionHandler.GetMissionByID)
			missionsRoutes.PUT("/:id", adminRoutes.Handlers[0], missionHandler.UpdateMission)     // Admin-only
			missionsRoutes.DELETE("/:id", adminRoutes.Handlers[0], missionHandler.DeleteMission)  // Admin-only
			missionsRoutes.POST("/:id/start", missionHandler.StartMission)                        // Auth required
			missionsRoutes.PUT("/:id/status", missionHandler.UpdateUserMissionStatus)             // Auth required
			missionsRoutes.DELETE("/:id/end", adminRoutes.Handlers[0], missionHandler.EndMission) // Admin-only
		}

		// Courses routes
		coursesRoutes := authRequired.Group("/courses")
		{
			coursesRoutes.POST("", adminRoutes.Handlers[0], courseHandler.CreateCourse) // Admin/Teacher-only
			coursesRoutes.GET("", courseHandler.GetCourses)
			coursesRoutes.GET("/:id", courseHandler.GetCourseByID)
			coursesRoutes.PUT("/:id", adminRoutes.Handlers[0], courseHandler.UpdateCourse)    // Admin/Teacher-only, owner only
			coursesRoutes.DELETE("/:id", adminRoutes.Handlers[0], courseHandler.DeleteCourse) // Admin/Teacher-only, owner only
			coursesRoutes.POST("/:id/enroll", courseHandler.EnrollStudent)                    // Auth required
			coursesRoutes.DELETE("/:id/unenroll", courseHandler.UnenrollStudent)              // Auth required
		}

		// Assignments routes (general CRUD)
		assignmentsRoutes := authRequired.Group("/assignments")
		{
			assignmentsRoutes.POST("", adminRoutes.Handlers[0], assignmentHandler.CreateAssignment) // Admin/Teacher-only
			assignmentsRoutes.GET("", assignmentHandler.GetAssignments)
			assignmentsRoutes.GET("/:id", assignmentHandler.GetAssignmentByID)
			assignmentsRoutes.PUT("/:id", adminRoutes.Handlers[0], assignmentHandler.UpdateAssignment)    // Admin/Teacher-only, owner only
			assignmentsRoutes.DELETE("/:id", adminRoutes.Handlers[0], assignmentHandler.DeleteAssignment) // Admin/Teacher-only, owner only

			// Student-specific actions on assignments
			assignmentsRoutes.POST("/:id/start", assignmentHandler.CreateStudentAssignment)                                                         // Auth required (student starts an assignment)
			assignmentsRoutes.POST("/:id/submit", assignmentHandler.UpdateStudentAssignment)                                                        // Auth required (student submits an assignment - updates status/submission)
			assignmentsRoutes.GET("/:id/submissions", adminRoutes.Handlers[0], assignmentHandler.GetStudentAssignmentsByAssignmentID)               // Admin/Teacher-only
			assignmentsRoutes.PUT("/:id/submissions/:student_assignment_id", adminRoutes.Handlers[0], assignmentHandler.UpdateStudentAssignment)    // Admin/Teacher-only (grade/update specific submission)
			assignmentsRoutes.DELETE("/:id/submissions/:student_assignment_id", adminRoutes.Handlers[0], assignmentHandler.DeleteStudentAssignment) // Admin-only
		}

		// Specific student assignments list (e.g., for a student to see their own progress)
		authRequired.GET("/students/:student_id/assignments", assignmentHandler.GetStudentAssignmentsList)

		// NEW: Quizzes routes (general CRUD)
		quizzesRoutes := authRequired.Group("/quizzes")
		{
			quizzesRoutes.POST("", adminRoutes.Handlers[0], quizHandler.CreateQuiz) // Admin/Teacher-only
			quizzesRoutes.GET("", quizHandler.GetQuizzes)
			quizzesRoutes.GET("/:id", quizHandler.GetQuizByID)
			quizzesRoutes.PUT("/:id", adminRoutes.Handlers[0], quizHandler.UpdateQuiz)    // Admin/Teacher-only, owner only
			quizzesRoutes.DELETE("/:id", adminRoutes.Handlers[0], quizHandler.DeleteQuiz) // Admin/Teacher-only, owner only

			// Quiz Questions
			quizzesRoutes.POST("/:id/questions", adminRoutes.Handlers[0], quizHandler.CreateQuizQuestion) // Admin/Teacher-only
			quizzesRoutes.GET("/:id/questions", quizHandler.GetQuizQuestionsByQuizID)
			quizzesRoutes.GET("/:id/questions/:question_id", quizHandler.GetQuizQuestionByID)
			quizzesRoutes.PUT("/:id/questions/:question_id", adminRoutes.Handlers[0], quizHandler.UpdateQuizQuestion)    // Admin/Teacher-only
			quizzesRoutes.DELETE("/:id/questions/:question_id", adminRoutes.Handlers[0], quizHandler.DeleteQuizQuestion) // Admin-only

			// Student-specific actions on quizzes
			quizzesRoutes.POST("/:id/start", quizHandler.CreateStudentQuiz)                                                   // Auth required (student starts a quiz)
			quizzesRoutes.POST("/:id/submit", quizHandler.UpdateStudentQuiz)                                                  // Auth required (student submits a quiz - updates status/score)
			quizzesRoutes.GET("/:id/submissions", adminRoutes.Handlers[0], quizHandler.GetStudentQuizzesByQuizID)             // Admin/Teacher-only (get all submissions for a quiz)
			quizzesRoutes.PUT("/:id/submissions/:student_quiz_id", adminRoutes.Handlers[0], quizHandler.UpdateStudentQuiz)    // Admin/Teacher-only (grade/update specific submission)
			quizzesRoutes.DELETE("/:id/submissions/:student_quiz_id", adminRoutes.Handlers[0], quizHandler.DeleteStudentQuiz) // Admin-only
		}

		// Specific student quizzes list (e.g., for a student to see their own progress)
		authRequired.GET("/students/:student_id/quizzes", quizHandler.GetStudentQuizzesList)

		questionnaireRoutes := authRequired.Group("/questionnaires")
		{
			questionnaireRoutes.POST("", questionnaireHandler.SubmitLikert)
			questionnaireRoutes.GET("", questionnaireHandler.GetQuestionnaires)
			questionnaireRoutes.GET("/:id", questionnaireHandler.GetQuestionnaireByID)
			questionnaireRoutes.POST("/vark", questionnaireHandler.SubmitVark)
			questionnaireRoutes.GET("/correlations", questionnaireHandler.GetCorrelation)
		}

		// NEW: Weekly Evaluations routes
		weeklyEvaluationRoutes := authRequired.Group("/weekly-evaluations")
		{
			weeklyEvaluationRoutes.GET("", weeklyEvaluationHandler.GetWeeklyEvaluations)
			weeklyEvaluationRoutes.POST("/initialize", adminRoutes.Handlers[0], weeklyEvaluationHandler.InitializeWeeklyEvaluations) // Admin-only
		}
	}

	serverAddr := fmt.Sprintf(":%d", cfg.ServerPort)
	if err := r.Run(serverAddr); err != nil {
		panic(fmt.Sprintf("failed to start server: %v", err))
	}
}
