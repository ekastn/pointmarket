<?php

require_once __DIR__.'/vendor/autoload.php';

use App\Controllers\AIExplanationController;
use App\Controllers\AIRecommendationsController;
use App\Controllers\AssignmentsController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\MaterialsController;
use App\Controllers\NLPDemoController;
use App\Controllers\ProfileController;
use App\Controllers\ProgressController;
use App\Controllers\QuestionnaireController;
use App\Controllers\QuizController;
use App\Controllers\TeacherEvaluationMonitoringController;
use App\Controllers\UsersController;
use App\Controllers\VarkCorrelationAnalysisController;
use App\Controllers\WeeklyEvaluationsController;
use App\Core\ApiClient;
use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Services\UserService; // <--- NEW: Import UserService
use DI\ContainerBuilder; // <--- NEW: Import ContainerBuilder
use Psr\Container\ContainerInterface; // <--- NEW: Import ContainerInterface

// Load environment variables from .env file
if (file_exists(__DIR__.'/.env')) {
    $env = parse_ini_file(__DIR__.'/.env');
    define('API_BASE_URL', $env['API_BASE_URL'] ?? 'http://localhost:8080');
} else {
    define('API_BASE_URL', 'http://localhost:8080');
}

//$apiClient = new ApiClient(API_BASE_URL);

// NEW: Build the DI Container
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    // Define how to create ApiClient
    ApiClient::class => function () {
        return new ApiClient(API_BASE_URL);
    },
    // Define how to create UserService (it depends on ApiClient)
    UserService::class => function (ApiClient $apiClient) { // PHP-DI can autowire ApiClient here
        return new UserService($apiClient);
    },
    // Define how to create the Router (it depends on ApiClient and the Container itself)
    Router::class => function (ApiClient $apiClient, ContainerInterface $container) { // PHP-DI can autowire
        return new Router($apiClient, $container);
    },
    // Controllers will be autowired by PHP-DI based on their type hints
    // No explicit definitions needed for controllers if their dependencies are defined
]);
$container = $containerBuilder->build();

$router = $container->get(Router::class);

// Public routes
$router->get('/', [AuthController::class, 'showLoginForm']);
$router->get('/login', [AuthController::class, 'showLoginForm']);
$router->post('/login', [AuthController::class, 'processLogin']);
$router->get('/logout', [AuthController::class, 'logout']);

// Authenticated routes group
$router->group('/', function ($router) {
    $router->get('dashboard', [DashboardController::class, 'showDashboard']);
    $router->get('ai-explanation', [AIExplanationController::class, 'show']);
    $router->get('ai-recommendations', [AIRecommendationsController::class, 'show']);
    $router->get('nlp-demo', [NLPDemoController::class, 'show']);
    $router->post('nlp-demo/analyze', [NLPDemoController::class, 'analyze']);
    $router->get('assignments', [AssignmentsController::class, 'index']);
    $router->get('quiz', [QuizController::class, 'index']);
    $router->get('questionnaire', [QuestionnaireController::class, 'index']);

    $router->get('vark-correlation-analysis', [VarkCorrelationAnalysisController::class, 'index']);

    // Materials routes
    $router->group('materials', function ($router) {
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
    $router->group('/users', function ($router) {
        $router->get('/', [UsersController::class, 'index']);
        $router->put('/{id}/role', [UsersController::class, 'updateUserRole']);
        $router->post('/delete', [UsersController::class, 'deleteUser']);
    }, [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireAdmin']]);

    // Teacher specific routes
    $router->get('teacher-evaluation-monitoring', [TeacherEvaluationMonitoringController::class, 'index'], [[AuthMiddleware::class, 'requireTeacher']]);
    $router->get('weekly-evaluations', [WeeklyEvaluationsController::class, 'index']);

}, [[AuthMiddleware::class, 'requireLogin']]);

// Dispatch the request
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
