<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Router;
use App\Services\ApiClient;
use App\Controllers\LoginController;
use App\Controllers\DashboardController;
use App\Controllers\AIExplanationController;
use App\Controllers\AIRecommendationsController;
use App\Controllers\NLPDemoController;
use App\Controllers\AssignmentsController;
use App\Controllers\QuestionnaireController;
use App\Controllers\QuestionnaireProgressController;
use App\Controllers\TeacherEvaluationMonitoringController;
use App\Controllers\VarkAssessmentController;
use App\Controllers\VarkCorrelationAnalysisController;
use App\Controllers\QuizController;
use App\Controllers\MaterialsController;
use App\Controllers\ProgressController;
use App\Controllers\ProfileController;
use App\Controllers\UsersController;
use App\Middleware\AuthMiddleware;

// Load environment variables from .env file
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    define('API_BASE_URL', $env['API_BASE_URL'] ?? 'http://localhost:8080');
} else {
    define('API_BASE_URL', 'http://localhost:8080');
}

// Initialize API Client
$apiClient = new ApiClient(API_BASE_URL);

$router = new Router($apiClient);

// Public routes
$router->get('/', [LoginController::class, 'showLoginForm']);
$router->get('/login', [LoginController::class, 'showLoginForm']);
$router->post('/login', [LoginController::class, 'processLogin']);

// Authenticated routes group
$router->group('/', function($router) {
    $router->get('dashboard', [DashboardController::class, 'showDashboard']);
    $router->get('ai-explanation', [AIExplanationController::class, 'show']);
    $router->get('ai-recommendations', [AIRecommendationsController::class, 'show']);
    $router->get('nlp-demo', [NLPDemoController::class, 'show']);
    $router->post('nlp-demo/analyze', [NLPDemoController::class, 'analyze']);
    $router->get('assignments', [AssignmentsController::class, 'index']);
    $router->get('quiz', [QuizController::class, 'index']);
    $router->get('questionnaire', [QuestionnaireController::class, 'index']);
    $router->get('questionnaire-progress', [QuestionnaireProgressController::class, 'index']);
    $router->get('vark-assessment', [VarkAssessmentController::class, 'show']);
    $router->post('vark-assessment', [VarkAssessmentController::class, 'submit']);
    $router->get('vark-correlation-analysis', [VarkCorrelationAnalysisController::class, 'index']);

    // Materials routes
    $router->group('materials', function($router) {
        $router->get('/', [MaterialsController::class, 'index']);
        $router->get('/create', [MaterialsController::class, 'create'], [[AuthMiddleware::class, 'requireTeacher']]);
        $router->post('/create', [MaterialsController::class, 'create'], [[AuthMiddleware::class, 'requireTeacher']]);
        $router->get('/edit/{id}', [MaterialsController::class, 'edit'], [[AuthMiddleware::class, 'requireTeacher']]);
        $router->post('/edit/{id}', [MaterialsController::class, 'edit'], [[AuthMiddleware::class, 'requireTeacher']]);
        $router->post('/delete/{id}', [MaterialsController::class, 'delete'], [[AuthMiddleware::class, 'requireTeacher']]);
    }, [[AuthMiddleware::class, 'requireLogin']]);

    // Progress/Analytics routes
    $router->get('progress', [ProgressController::class, 'index']);

    // User Profile routes
    $router->get('profile', [ProfileController::class, 'showProfile']);
    $router->post('profile', [ProfileController::class, 'updateProfile']);

    // Admin routes group
    $router->group('admin', function($router) {
        $router->group('users', function($router) {
            $router->get('/', [UsersController::class, 'index']);
            $router->post('/update-role', [UsersController::class, 'updateUserRole']);
            $router->post('/delete', [UsersController::class, 'deleteUser']);
        });
    }, [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireAdmin']]);

    // Teacher specific routes
    $router->get('teacher-evaluation-monitoring', [TeacherEvaluationMonitoringController::class, 'index'], [[AuthMiddleware::class, 'requireTeacher']]);

}, [[AuthMiddleware::class, 'requireLogin']]);

// Dispatch the request
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
