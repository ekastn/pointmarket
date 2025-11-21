package main

import (
	"context"
	"fmt"
	"pointmarket/backend/internal/config"
	"pointmarket/backend/internal/database"
	"pointmarket/backend/internal/gateway"
	"pointmarket/backend/internal/handler"
	"pointmarket/backend/internal/middleware"
	"pointmarket/backend/internal/services"
	"pointmarket/backend/internal/store"
	"pointmarket/backend/internal/store/gcs"
	"pointmarket/backend/internal/store/gen"
	"strings"
	"time"

	"cloud.google.com/go/storage"
	"github.com/gin-contrib/cors"
	"github.com/gin-gonic/gin"
)

func main() {
	cfg := config.Init()
	db := database.Connect(cfg)

	querier := gen.New(db)

	aiServiceGateway := gateway.NewAIServiceGateway(cfg.AIServiceURL)
	recGateway := gateway.NewRecommendationGateway(cfg.RecommendationServiceURL, "")

	authService := services.NewAuthService(cfg, querier)
	userService := services.NewUserService(querier)
	studentService := services.NewStudentService(querier)
	questionnaireService := services.NewQuestionnaireService(db.DB, querier)
	weeklyEvaluationService := services.NewWeeklyEvaluationService(querier, userService, questionnaireService)
	schedulerManager := services.NewSchedulerManager(weeklyEvaluationService)
	dashboardService := services.NewDashboardService(querier, weeklyEvaluationService, recGateway)
	analyticsService := services.NewAnalyticsService(querier)
	correlationService := services.NewCorrelationService(querier)
	productService := services.NewProductService(db.DB, querier)
	badgeService := services.NewBadgeService(querier)
	pointsService := services.NewPointsService(db.DB, querier)
	missionService := services.NewMissionService(querier, pointsService)
	courseService := services.NewCourseService(querier)
	lessonService := services.NewLessonService(querier)
	assignmentService := services.NewAssignmentService(querier, pointsService)
	quizService := services.NewQuizService(querier, pointsService)
	textAnalyzerService := services.NewTextAnalyzerService(aiServiceGateway, querier)
	recommendationService := services.NewRecommendationService(recGateway, studentService, missionService)
	pointsHandler := handler.NewPointsHandler(pointsService, querier)

	var imgStore store.ImageStore
	ctx, cancel := context.WithTimeout(context.Background(), 30*time.Second)
	defer cancel()

	gClient, err := storage.NewClient(ctx)
	if err == nil {
		storeInst, err2 := gcs.NewGCSImageStore(gClient, cfg.GCSBucket, cfg.GCSPublicBaseURL, cfg.MaxAvatarMB)
		if err2 == nil {
			imgStore = storeInst
		}
	}

	userService.ConfigureAvatarStore(imgStore, cfg.GCSPublicBaseURL)

	authHandler := handler.NewAuthHandler(*authService)
	userHandler := handler.NewUserHandler(*userService, studentService, cfg.MaxAvatarMB)
	studentHandler := handler.NewStudentHandler(studentService)
	questionnaireHandler := handler.NewQuestionnaireHandler(questionnaireService, textAnalyzerService, correlationService, userService)
	weeklyEvaluationHandler := handler.NewWeeklyEvaluationHandler(weeklyEvaluationService, schedulerManager)
	textAnalyzerHandler := handler.NewTextAnalysisHandler(textAnalyzerService)
	recommendationHandler := handler.NewRecommendationHandler(recommendationService, studentService)
	dashboardHandler := handler.NewDashboardHandler(*dashboardService)
	analyticsHandler := handler.NewAnalyticsHandler(analyticsService)
	productHandler := handler.NewProductHandler(*productService)
	badgeHandler := handler.NewBadgeHandler(*badgeService)
	missionHandler := handler.NewMissionHandler(*missionService)
	courseHandler := handler.NewCourseHandler(*courseService)
	authzService := services.NewAuthzService(querier)
	assignmentHandler := handler.NewAssignmentHandler(assignmentService, authzService)
	lessonHandler := handler.NewLessonHandler(lessonService, querier)
	quizHandler := handler.NewQuizHandler(quizService, authzService)
	scoringHandler := handler.NewScoringHandler()

	r := gin.Default()

	r.Use(cors.New(cors.Config{
		AllowOrigins:     strings.Split(cfg.AllowedOrigins, ","),
		AllowMethods:     []string{"GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"},
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
		profileRoutes := authRequired.Group("/profile")
		{
			profileRoutes.GET("/", userHandler.GetUserProfile)
			profileRoutes.PUT("/", userHandler.UpdateUserProfile)
			profileRoutes.PUT("/password", userHandler.ChangePassword)
			profileRoutes.PATCH("/avatar", userHandler.PatchUserAvatar)
		}

		authRequired.GET("/dashboard", dashboardHandler.GetDashboardData)
		authRequired.GET("/roles", userHandler.GetRoles)
		authRequired.POST("/text-analyzer", textAnalyzerHandler.PredictText)

		userRoutes := adminRoutes.Group("/users")
		{
			userRoutes.GET("", userHandler.GetAllUsers)
			userRoutes.POST("", userHandler.CreateUser)
			userRoutes.GET("/:id", userHandler.GetUserByID)
			userRoutes.GET("/:id/details", userHandler.GetUserDetails)
			userRoutes.PUT("/:id", userHandler.UpdateUser)
			userRoutes.PUT("/:id/role", userHandler.UpdateUserRole)
			userRoutes.DELETE("/:id", userHandler.DeleteUser)

			// Admin user stats management
			userRoutes.GET("/:id/stats", pointsHandler.GetUserStats)
			userRoutes.POST("/:id/stats", pointsHandler.AdjustUserStats)
		}

		// Academic: programs (all roles can read)
		programsRoutes := authRequired.Group("/programs")
		{
			programsRoutes.GET("", studentHandler.GetPrograms)
		}

		// Academic: students (admin-only management)
		studentsRoutes := authRequired.Group("/students")
		{
			// Admin-only routes reusing admin middleware handler
			studentsRoutes.GET("", adminRoutes.Handlers[0], studentHandler.SearchStudents)
			studentsRoutes.GET("/:user_id", adminRoutes.Handlers[0], studentHandler.GetStudentByUserID)
			studentsRoutes.GET("/:user_id/details", adminRoutes.Handlers[0], studentHandler.GetStudentDetailsByUserID)
			studentsRoutes.PUT("/:user_id", adminRoutes.Handlers[0], studentHandler.UpsertStudentByUserID)

			studentsRoutes.GET("/:user_id/assignments", assignmentHandler.GetStudentAssignmentsList)
			studentsRoutes.GET("/:user_id/quizzes", quizHandler.GetStudentQuizzesList)

			studentsRoutes.GET("/:user_id/recommendations", recommendationHandler.GetStudentRecommendations)
		}

		productRoutes := authRequired.Group("/products")
		{
			productRoutes.GET("", productHandler.GetProducts)
			productRoutes.GET("/:id", productHandler.GetProductByID)
			productRoutes.POST("", adminRoutes.Handlers[0], productHandler.CreateProduct)                  // Admin-only
			productRoutes.PUT("/:id", adminRoutes.Handlers[0], productHandler.UpdateProduct)               // Admin-only
			productRoutes.DELETE("/:id", adminRoutes.Handlers[0], productHandler.DeleteProduct)            // Admin-only
			productRoutes.POST("/:id/purchase", middleware.Authz("siswa"), productHandler.PurchaseProduct) // Students only
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

		authRequired.GET("/my-badges", badgeHandler.GetUserOwnBadges)

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
			assignmentsRoutes.GET("/:id/submissions", middleware.Authz("guru"), assignmentHandler.GetStudentAssignmentsByAssignmentID)              // Teacher/Admin
			assignmentsRoutes.PUT("/:id/submissions/:student_assignment_id", middleware.Authz("guru"), assignmentHandler.UpdateStudentAssignment)   // Teacher/Admin
			assignmentsRoutes.DELETE("/:id/submissions/:student_assignment_id", adminRoutes.Handlers[0], assignmentHandler.DeleteStudentAssignment) // Admin-only
		}

		// Lessons routes (top-level resource)
		lessonsRoutes := authRequired.Group("/lessons")
		{
			lessonsRoutes.GET("", lessonHandler.GetLessons)
			lessonsRoutes.GET("/:id", lessonHandler.GetLessonByID)
			lessonsRoutes.POST("", lessonHandler.CreateLesson)
			lessonsRoutes.PUT("/:id", lessonHandler.UpdateLesson)
			lessonsRoutes.DELETE("/:id", lessonHandler.DeleteLesson)
		}

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

		// Teacher analytics
		teacherRoutes := authRequired.Group("/teachers")
		{
			teacherRoutes.GET("/course-insights", analyticsHandler.GetTeacherCourseInsights)
		}

		questionnaireRoutes := authRequired.Group("/questionnaires")
		{
			questionnaireRoutes.POST("", questionnaireHandler.SubmitLikert)
			questionnaireRoutes.GET("", questionnaireHandler.GetQuestionnaires)
			questionnaireRoutes.GET("/:id", questionnaireHandler.GetQuestionnaireByID)
			questionnaireRoutes.POST("/vark", questionnaireHandler.SubmitVark)
			questionnaireRoutes.GET("/correlations", questionnaireHandler.GetCorrelation)
			// New student helpers
			questionnaireRoutes.GET("/stats", questionnaireHandler.GetQuestionnaireStats)
			questionnaireRoutes.GET("/history", questionnaireHandler.GetQuestionnaireHistory)
			questionnaireRoutes.GET("/vark", questionnaireHandler.GetLatestVark)

			// Admin routes for questionnaires
			questionnaireRoutes.POST("/", adminRoutes.Handlers[0], questionnaireHandler.CreateQuestionnaire)
			questionnaireRoutes.PUT("/:id", adminRoutes.Handlers[0], questionnaireHandler.UpdateQuestionnaire)
			questionnaireRoutes.DELETE("/:id", adminRoutes.Handlers[0], questionnaireHandler.DeleteQuestionnaire)
		}

		// NEW: Weekly Evaluations routes
		weeklyEvaluationRoutes := authRequired.Group("/weekly-evaluations")
		{
			weeklyEvaluationRoutes.GET("", weeklyEvaluationHandler.GetWeeklyEvaluations)
			weeklyEvaluationRoutes.POST("/initialize", adminRoutes.Handlers[0], weeklyEvaluationHandler.InitializeWeeklyEvaluations) // Admin-only
			// Admin-only scheduler controls (flat under the resource)
			weeklyEvaluationRoutes.GET("/status", adminRoutes.Handlers[0], weeklyEvaluationHandler.SchedulerStatus)
			weeklyEvaluationRoutes.POST("/start", adminRoutes.Handlers[0], weeklyEvaluationHandler.SchedulerStart)
			weeklyEvaluationRoutes.POST("/stop", adminRoutes.Handlers[0], weeklyEvaluationHandler.SchedulerStop)
		}

		productCategoriesRoutes := authRequired.Group("/product-categories")
		{
			productCategoriesRoutes.POST("", adminRoutes.Handlers[0], productHandler.CreateProductCategory) // Admin-only
			productCategoriesRoutes.GET("", productHandler.GetProductCategories)
			productCategoriesRoutes.GET("/:id", productHandler.GetProductCategoryByID)
			productCategoriesRoutes.PUT("/:id", adminRoutes.Handlers[0], productHandler.UpdateProductCategory)    // Admin-only
			productCategoriesRoutes.DELETE("/:id", adminRoutes.Handlers[0], productHandler.DeleteProductCategory) // Admin-only
		}

		scoringsRoutes := adminRoutes.Group("/scorings")
		{
			scoringsRoutes.PUT("/multimodal", scoringHandler.UpdateMultimodalThreshold)
			scoringsRoutes.GET("/multimodal", scoringHandler.GetMultimodalThreshold)
		}

		// Admin-only: recommendations trace for evaluator
		adminGroup := authRequired.Group("/admin")
		adminGroup.Use(middleware.Authz("admin"))
		adminGroup.GET("/recommendations/trace", recommendationHandler.GetRecommendationsTrace)

		// Admin-only: Recommendation items management
		adminGroup.GET("/recommendations/items", recommendationHandler.AdminListItems)
		adminGroup.POST("/recommendations/items", recommendationHandler.AdminCreateItems)
		adminGroup.PUT("/recommendations/items/:id", recommendationHandler.AdminUpdateItem)
		adminGroup.PATCH("/recommendations/items/:id/toggle", recommendationHandler.AdminToggleItem)
		adminGroup.DELETE("/recommendations/items/:id", recommendationHandler.AdminDeleteItem)

		// Admin-only: typeahead helpers
		adminGroup.GET("/recommendations/states", recommendationHandler.AdminListStates)
		adminGroup.GET("/recommendations/refs", recommendationHandler.AdminSearchRefs)

		// Admin-only: Unique States CRUD
		adminGroup.GET("/recommendations/unique-states", recommendationHandler.AdminStatesList)
		adminGroup.POST("/recommendations/unique-states", recommendationHandler.AdminStatesCreate)
		adminGroup.PUT("/recommendations/unique-states/:id", recommendationHandler.AdminStatesUpdate)
		adminGroup.DELETE("/recommendations/unique-states/:id", recommendationHandler.AdminStatesDelete)
	}

	serverAddr := fmt.Sprintf(":%d", cfg.ServerPort)
	if err := r.Run(serverAddr); err != nil {
		panic(fmt.Sprintf("failed to start server: %v", err))
	}
}
