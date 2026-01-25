<?php

use App\Controllers\AssignmentsController;
use App\Controllers\AuthController;
use App\Controllers\BadgesController;
use App\Controllers\CoursesController;
use App\Controllers\DashboardController;
use App\Controllers\DemoController;
use App\Controllers\LandingPageController;
use App\Controllers\MissionsController;
use App\Controllers\ProductCategoriesController;
use App\Controllers\ProductsController;
use App\Controllers\ProfileController;
use App\Controllers\StudentsController;
use App\Controllers\QuestionnaireController;
use App\Controllers\QuizController;
use App\Controllers\TeacherAssignmentsController;
use App\Controllers\TeacherQuizzesController;
use App\Controllers\SettingsController;
use App\Controllers\UsersController;
use App\Controllers\VarkCorrelationAnalysisController;
use App\Controllers\WeeklyEvaluationsController;
use App\Controllers\ReportsController;
use App\Controllers\AdminItemsController;
use App\Controllers\AdminStatesController;
use App\Core\Router;
use App\Middleware\AuthMiddleware;

return function (Router $router) {
    // Public routes
    $router->get('/', [LandingPageController::class, 'showLandingPage']);

    // Landing page sub-routes
    $router->get('/landing/sahabat-belajar', [LandingPageController::class, 'showSahabatBelajar']);
    $router->get('/landing/alur-kerja', [LandingPageController::class, 'showAlurKerja']);
    $router->get('/landing/studi-kasus', [LandingPageController::class, 'showStudiKasus']);
    $router->get('/landing/riset', [LandingPageController::class, 'showRiset']);

    $router->get('/login', [AuthController::class, 'showLoginForm']);
    $router->get('/register', [AuthController::class, 'showRegisterForm']);
    $router->post('/login', [AuthController::class, 'processLogin']);
    // Registration (user-only) from login page; auto-login handled in controller
    $router->post('/register', [AuthController::class, 'processRegister']);
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
        $router->get('assignments/{id}', [AssignmentsController::class, 'show']);
        $router->get('quiz', [QuizController::class, 'index']);
        $router->get('quiz/{id}', [QuizController::class, 'show']);

        // Teacher management routes
        $router->group('/guru', function (Router $router) {
            // Assignments
            $router->get('/assignments', [TeacherAssignmentsController::class, 'index']);
            $router->get('/assignments/create', [TeacherAssignmentsController::class, 'create']);
            $router->post('/assignments', [TeacherAssignmentsController::class, 'store']);
            $router->get('/assignments/{id}/edit', [TeacherAssignmentsController::class, 'edit']);
            $router->post('/assignments/{id}', [TeacherAssignmentsController::class, 'update']);
            $router->post('/assignments/{id}/delete', [TeacherAssignmentsController::class, 'destroy']);
            $router->get('/assignments/{id}/submissions', [TeacherAssignmentsController::class, 'submissions']);
            $router->post('/assignments/{id}/submissions/{studentAssignmentId}/grade', [TeacherAssignmentsController::class, 'grade']);

            // Quizzes
            $router->get('/quizzes', [TeacherQuizzesController::class, 'index']);
            $router->get('/quizzes/create', [TeacherQuizzesController::class, 'create']);
            $router->post('/quizzes', [TeacherQuizzesController::class, 'store']);
            $router->get('/quizzes/{id}/edit', [TeacherQuizzesController::class, 'edit']);
            $router->post('/quizzes/{id}', [TeacherQuizzesController::class, 'update']);
            $router->post('/quizzes/{id}/delete', [TeacherQuizzesController::class, 'destroy']);
        }, [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireAdminOrTeacher']]);

        $router->group('questionnaires', function (Router $router) {
            $router->get('/', [QuestionnaireController::class, 'index']);
            $router->get('/{id}', [QuestionnaireController::class, 'startQuestionnairePage']);
            $router->post('/likert', [QuestionnaireController::class, 'submitLikertQuestionnaire']);
            $router->post('/vark', [QuestionnaireController::class, 'submitVARKQuestionnaire']);

            // Admin CRUD routes
            $router->get('/create', [QuestionnaireController::class, 'create'], [[AuthMiddleware::class, 'requireAdmin']]);
            $router->get('/{id}/edit', [QuestionnaireController::class, 'edit'], [[AuthMiddleware::class, 'requireAdmin']]);
            $router->post('/', [QuestionnaireController::class, 'store'], [[AuthMiddleware::class, 'requireAdmin']]);
            $router->put('/{id}', [QuestionnaireController::class, 'update'], [[AuthMiddleware::class, 'requireAdmin']]);
            $router->delete('/{id}', [QuestionnaireController::class, 'destroy'], [[AuthMiddleware::class, 'requireAdmin']]);
        });

        $router->get('vark-correlation-analysis', [VarkCorrelationAnalysisController::class, 'index']);

        // User Profile routes
        $router->group('/profile', function (Router $router) {
            $router->get('/', [ProfileController::class, 'showProfile']);
            $router->post('/', [ProfileController::class, 'updateProfile']);
            $router->post('/password', [ProfileController::class, 'changePassword']);
            $router->post('/avatar', [ProfileController::class, 'uploadAvatar']);
        });

        // Admin routes group
        $router->group('/users', function (Router $router) {
            $router->get('/', [UsersController::class, 'index']);
            $router->get('/{id}', [UsersController::class, 'show']);
            $router->post('/', [UsersController::class, 'saveUser']); // Create user
            $router->put('/{id}', [UsersController::class, 'updateUser']); // Update user
            $router->put('/{id}/role', [UsersController::class, 'updateUserRole']);
            $router->delete('/{id}', [UsersController::class, 'deleteUser']); // Delete user
            $router->get('/{id}/stats', [UsersController::class, 'getStats']);
            $router->post('/{id}/stats', [UsersController::class, 'adjustStats']);
        }, [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireAdmin']]);

        // Admin Students management
        $router->group('/students', function (Router $router) {
            $router->get('/', [StudentsController::class, 'index']);
            $router->get('/{id}', [StudentsController::class, 'show']);
            $router->put('/{id}', [StudentsController::class, 'update']);
        }, [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireAdminOrTeacher']]);

        // Weekly Evaluations routes
        $router->get('weekly-evaluations', [WeeklyEvaluationsController::class, 'index']);
        $router->post('weekly-evaluations/initialize', [WeeklyEvaluationsController::class, 'initialize'], [[AuthMiddleware::class, 'requireAdmin']]);
        // Admin-only scheduler actions (rendered in Admin dashboard)
        $router->post('weekly-evaluations/start', [WeeklyEvaluationsController::class, 'start'], [[AuthMiddleware::class, 'requireAdmin']]);
        $router->post('weekly-evaluations/stop', [WeeklyEvaluationsController::class, 'stop'], [[AuthMiddleware::class, 'requireAdmin']]);

        // Teacher/Admin: per-student chart data endpoint for modal
        $router->get('guru/weekly-evaluations/{student_id}/chart-data', [WeeklyEvaluationsController::class, 'studentData'], [[AuthMiddleware::class, 'requireAdminOrTeacher']]);

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
        $router->get('/courses/{slug}', [CoursesController::class, 'show']);
        $router->get('/courses', [CoursesController::class, 'index']);
        $router->get('/my-courses', [CoursesController::class, 'index'], [[AuthMiddleware::class, 'requireStudent']]);
        $router->post('/courses/{id}/enroll', [CoursesController::class, 'enroll'], [[AuthMiddleware::class, 'requireStudent']]);
        $router->delete('/courses/{id}/unenroll', [CoursesController::class, 'unenroll'], [[AuthMiddleware::class, 'requireStudent']]);

        // Missions routes (Admin CRUD, Student actions, Student detail)
        $router->group('/missions', function (Router $router) {
            // Admin can create/edit/delete missions
            $router->post('/', [MissionsController::class, 'store'], [[AuthMiddleware::class, 'requireAdmin']]);
            $router->put('/{id}', [MissionsController::class, 'update'], [[AuthMiddleware::class, 'requireAdmin']]);
            $router->delete('/{id}', [MissionsController::class, 'destroy'], [[AuthMiddleware::class, 'requireAdmin']]);

            // Student actions on missions
            $router->post('/{id}/start', [MissionsController::class, 'start'], [[AuthMiddleware::class, 'requireStudent']]);
            $router->put('/{id}/status', [MissionsController::class, 'updateStatus'], [[AuthMiddleware::class, 'requireStudent']]);

            // Student view mission detail
            $router->get('/{id}', [MissionsController::class, 'show'], [[AuthMiddleware::class, 'requireStudent']]);
        });

        // General missions listing (accessible by Admin, Teacher, Student)
        $router->get('/missions', [MissionsController::class, 'index']);

        // Student specific missions routes
        $router->get('/my-missions', [MissionsController::class, 'index']);

        // Badges routes (Admin)
        $router->group('/badges', function (Router $router) {
            $router->get('/', [BadgesController::class, 'index']);
            $router->post('/', [BadgesController::class, 'store']);
            $router->put('/{id}', [BadgesController::class, 'update']);
            $router->delete('/{id}', [BadgesController::class, 'destroy']);
            $router->post('/award', [BadgesController::class, 'award']);
            $router->post('/revoke', [BadgesController::class, 'revoke']);
        }, [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireAdmin']]);

        // Student specific badges routes
        $router->get('/my-badges', [BadgesController::class, 'myBadges'], [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireStudent']]);

        // Products routes
        $router->get('/products', [ProductsController::class, 'index'], [[AuthMiddleware::class, 'requireLogin']]); // Marketplace view for all users, with admin-specific controls
        $router->post('/products', [ProductsController::class, 'store'], [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireAdmin']]); // Admin-only
        $router->put('/products/{id}', [ProductsController::class, 'update'], [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireAdmin']]); // Admin-only
        $router->delete('/products/{id}', [ProductsController::class, 'destroy'], [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireAdmin']]); // Admin-only
        $router->post('/products/{id}/purchase', [ProductsController::class, 'purchase'], [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireStudent']]); // For students

        // Product Categories routes (Admin)
        $router->group('/product-categories', function (Router $router) {
            $router->get('/', [ProductCategoriesController::class, 'index']);
            $router->post('/', [ProductCategoriesController::class, 'store']);
            $router->put('/{id}', [ProductCategoriesController::class, 'update']);
            $router->delete('/{id}', [ProductCategoriesController::class, 'destroy']);
        }, [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireAdmin']]);

        $router->group('/settings', function (Router $router) {
            $router->get('/', [SettingsController::class, 'index']);
            $router->put('/multimodal', [SettingsController::class, 'updateMultimodalThreshold']);

        }, [[AuthMiddleware::class, 'requireLogin']]);

        // Admin reports
        $router->group('/reports', function (Router $router) {
            $router->get('/recommendations-trace', [ReportsController::class, 'recommendationsTrace']);
        }, [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireAdmin']]);

        // Admin: Recommendation Items management
        $router->group('/admin/recommendations/items', function (Router $router) {
            $router->get('/export', [AdminItemsController::class, 'export']);
            $router->get('/', [AdminItemsController::class, 'index']);
            $router->post('/', [AdminItemsController::class, 'store']);
            $router->post('/update', [AdminItemsController::class, 'update']);
            $router->post('/toggle', [AdminItemsController::class, 'toggle']);
            $router->post('/delete', [AdminItemsController::class, 'destroy']);
            // Typeahead proxies
            $router->get('/typeahead/states', [AdminItemsController::class, 'typeaheadStates']);
            $router->get('/typeahead/refs', [AdminItemsController::class, 'typeaheadRefs']);
        }, [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireAdmin']]);

        // Admin: Unique States management
        $router->group('/admin/recommendations/states', function (Router $router) {
            $router->get('/export', [AdminStatesController::class, 'export']);
            $router->get('/', [AdminStatesController::class, 'index']);
            $router->post('/', [AdminStatesController::class, 'store']);
            $router->post('/update', [AdminStatesController::class, 'update']);
            $router->post('/delete', [AdminStatesController::class, 'destroy']);
        }, [[AuthMiddleware::class, 'requireLogin'], [AuthMiddleware::class, 'requireAdmin']]);

    }, [[AuthMiddleware::class, 'requireLogin']]);
};
