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

// Define routes
$router->get('/', [LoginController::class, 'showLoginForm']);
$router->get('/login', [LoginController::class, 'showLoginForm']);
$router->post('/login', [LoginController::class, 'processLogin']);
$router->get('/dashboard', [DashboardController::class, 'showDashboard']);
$router->get('/ai-explanation', [AIExplanationController::class, 'show']);
$router->get('/ai-recommendations', [AIRecommendationsController::class, 'show']);
$router->get('/nlp-demo', [NLPDemoController::class, 'show']);
$router->post('/nlp-demo/analyze', [NLPDemoController::class, 'analyze']);
$router->get('/assignments', [AssignmentsController::class, 'index']);
$router->get('/quiz', [QuizController::class, 'index']);
$router->get('/questionnaire', [QuestionnaireController::class, 'index']);
$router->get('/questionnaire-progress', [QuestionnaireProgressController::class, 'index']);
$router->get('/teacher-evaluation-monitoring', [TeacherEvaluationMonitoringController::class, 'index']);
$router->get('/vark-assessment', [VarkAssessmentController::class, 'show']);
$router->post('/vark-assessment', [VarkAssessmentController::class, 'submit']);
$router->get('/vark-correlation-analysis', [VarkCorrelationAnalysisController::class, 'index']);

// Materials routes
$router->get('/materials', [MaterialsController::class, 'index']);
$router->get('/materials/create', [MaterialsController::class, 'create']);
$router->post('/materials/create', [MaterialsController::class, 'create']);
$router->get('/materials/edit/{id}', [MaterialsController::class, 'edit']);
$router->post('/materials/edit/{id}', [MaterialsController::class, 'edit']);
$router->post('/materials/delete/{id}', [MaterialsController::class, 'delete']);

// Progress/Analytics routes
$router->get('/progress', [ProgressController::class, 'index']);

// User Profile routes
$router->get('/profile', [ProfileController::class, 'showProfile']);
$router->post('/profile', [ProfileController::class, 'updateProfile']);

// User Management routes
$router->get('/admin/users', [UsersController::class, 'index']);
$router->post('/admin/users/update-role', [UsersController::class, 'updateUserRole']);
$router->post('/admin/users/delete', [UsersController::class, 'deleteUser']);

// Dispatch the request
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
