<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\WeeklyEvaluationService;

class WeeklyEvaluationsController extends BaseController
{
    protected WeeklyEvaluationService $weeklyEvaluationService;

    public function __construct(ApiClient $apiClient, WeeklyEvaluationService $weeklyEvaluationService)
    {
        parent::__construct($apiClient);
        $this->weeklyEvaluationService = $weeklyEvaluationService;
    }

    public function index(): void
    {
        $userRole = $_SESSION['user_data']['role'] ?? '';

        if ($userRole === 'admin') {
            // Admins can view the teacher dashboard
            $this->viewTeacherDashboard();
        } elseif ($userRole === 'guru') {
            $this->viewTeacherDashboard();
        } elseif ($userRole === 'siswa') {
            $this->viewStudentDashboard();
        } else {
            $this->redirect('/login');
        }
    }

    private function viewStudentDashboard(): void
    {
        $evaluations = $this->weeklyEvaluationService->getWeeklyEvaluations();

        $this->render('siswa/weekly-evaluations', [
            'title' => 'Weekly Evaluations',
            'evaluations' => $evaluations,
        ]);
    }

    private function viewTeacherDashboard(): void
    {
        $dashboardData = $this->weeklyEvaluationService->getTeacherDashboard();

        $this->render('guru/teacher-evaluation-monitoring', [
            'title' => 'Teacher Evaluation Monitoring',
            'dashboardData' => $dashboardData,
        ]);
    }

    public function initialize(): void
    {
        $result = $this->weeklyEvaluationService->initializeWeeklyEvaluations();

        if ($result) {
            $_SESSION['messages']['success'] = 'Weekly evaluations initialized successfully!';
        } else {
            $_SESSION['messages']['error'] = 'Failed to initialize weekly evaluations.';
        }

        $this->redirect('/dashboard');
    }
}
