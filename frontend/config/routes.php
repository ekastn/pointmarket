<?php

use App\Controllers\AssignmentsController;
use App\Controllers\AuthController;
use App\Controllers\CoursesController;
use App\Controllers\DashboardController;
use App\Controllers\ProfileController;
use App\Controllers\ProgressController;
use App\Controllers\QuestionnaireController;
use App\Controllers\QuizController;
use App\Controllers\TeacherEvaluationMonitoringController;
use App\Controllers\UsersController;
use App\Controllers\VarkCorrelationAnalysisController;
use App\Controllers\WeeklyEvaluationsController;
use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Controllers\DemoController;

return function (Router $router) {
    // Public routes
    $router->get('/', [AuthController::class, 'showLoginForm']);
    $router->get('/login', [AuthController::class, 'showLoginForm']);
    $router->post('/login', [AuthController::class, 'processLogin']);
    $router->get('/logout', [AuthController::class, 'logout']);

    // Authenticated routes group
    $router->group('/', function (Router $router) {
        $router->get('dashboard', [DashboardController::class, 'showDashboard']);
        
        // Consolidated Demo routes
        $router->get('ai-explanation', [DemoController::class, 'showAIExplanation']);
        $router->get('ai-recommendations', [DemoController::class, 'showAIRecommendations']);
        $router->get('nlp-demo', [DemoController::class, 'showNLPDemo']);
        $router->post('nlp-demo/analyze', [DemoController::class, 'analyzeNLP']);

        $router->get('assignments', [AssignmentsController::class, 'index']);
        $router->get('quiz', [QuizController::class, 'index']);
        $router->get('questionnaire', [QuestionnaireController::class, 'index']);

        $router->get('vark-correlation-analysis', [VarkCorrelationAnalysisController::class, 'index']);

        // Progress/Analytics routes
        $router->get('progress', [ProgressController::class, 'index']);

        // User Profile routes
        $router->get('profile', [ProfileController::class, 'showProfile']);
        $router->post('profile', [ProfileController::class, 'updateProfile']);

        // Admin routes group
        $router->group('/users', function (Router $router) {
            $router->get('/', [UsersController::class, 'index']);
            $router->post('/', [UsersController::class, 'saveUser']); // Create user
            $router->put('/{id}', [UsersController::class, 'updateUser']); // Update user
            $router->put('/{id}/role', [UsersController::class, 'updateUserRole']);
            $router->delete('/{id}', [UsersController::class, 'deleteUser']); // Delete user
        }, [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireAdmin']]);

        // Teacher specific routes
        $router->get('teacher-evaluation-monitoring', [TeacherEvaluationMonitoringController::class, 'index'], [[AuthMiddleware::class, 'requireTeacher']]);
        $router->get('weekly-evaluations', [WeeklyEvaluationsController::class, 'index']);

        // Courses routes (Admin/Teacher CRUD)
        $router->group('/courses', function (Router $router) {
            // Admin/Teacher can create/edit/delete courses
            $router->post('/', [CoursesController::class, 'store']);
            $router->get('/create', [CoursesController::class, 'create']); // Admin/Teacher can access create form
            $router->get('/{id}/edit', [CoursesController::class, 'edit']); // Admin/Teacher can access edit form
            $router->put('/{id}', [CoursesController::class, 'update']);
            $router->delete('/{id}', [CoursesController::class, 'destroy']);
        }, [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireAdminOrTeacher']]);

        // General courses listing (accessible by Admin, Teacher, Student)
        $router->get('/courses', [CoursesController::class, 'index'], [[AuthMiddleware::class, 'requireLogin']]);

        // Student specific courses routes
        $router->get('/my-courses', [CoursesController::class, 'index'], [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireStudent']]);
        $router->post('/courses/{id}/enroll', [CoursesController::class, 'enroll'], [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireStudent']]);
        $router->delete('/courses/{id}/unenroll', [CoursesController::class, 'unenroll'], [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireStudent']]);

    }, [[AuthMiddleware::class, 'requireLogin']]);
};
