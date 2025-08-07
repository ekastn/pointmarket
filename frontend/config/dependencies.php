<?php

use App\Core\ApiClient;
use App\Core\Router;
use App\Services\UserService;
use App\Services\DashboardService;
use App\Services\QuestionnaireService;
use App\Services\AssignmentService;
use App\Services\QuizService;
use App\Services\VarkCorrelationService;
use App\Services\ProfileService;
use App\Services\MaterialService;
use App\Services\WeeklyEvaluationService;
use App\Middleware\AuthMiddleware;
use App\Controllers\ErrorController;
use Psr\Container\ContainerInterface;

return [
    ApiClient::class => function () {
        return new ApiClient(API_BASE_URL);
    },

    UserService::class => function (ApiClient $apiClient) {
        return new UserService($apiClient);
    },
    DashboardService::class => function (ApiClient $apiClient) {
        return new DashboardService($apiClient);
    },

    QuestionnaireService::class => function (ApiClient $apiClient) {
        return new QuestionnaireService($apiClient);
    },

    AssignmentService::class => function (ApiClient $apiClient) {
        return new AssignmentService($apiClient);
    },

    QuizService::class => function (ApiClient $apiClient) {
        return new QuizService($apiClient);
    },

    VarkCorrelationService::class => function (ApiClient $apiClient) {
        return new VarkCorrelationService($apiClient);
    },

    ProfileService::class => function (ApiClient $apiClient) {
        return new ProfileService($apiClient);
    },

    MaterialService::class => function (ApiClient $apiClient) {
        return new MaterialService($apiClient);
    },

    WeeklyEvaluationService::class => function (ApiClient $apiClient) {
        return new WeeklyEvaluationService($apiClient);
    },

    AuthMiddleware::class => function (ApiClient $apiClient, ProfileService $profileService) {
        return new AuthMiddleware($apiClient, $profileService);
    },

    ErrorController::class => function (ApiClient $apiClient) {
        return new ErrorController($apiClient);
    },

    Router::class => function (ApiClient $apiClient, ContainerInterface $container) {
        return new Router($apiClient, $container);
    },

    // Controllers will be autowired by PHP-DI based on their type hints
    // No explicit definitions needed for controllers if their dependencies are defined
];