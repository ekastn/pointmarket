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

    // Admin: start scheduler
    public function start(): void
    {
        $ok = false;
        try {
            $ok = $this->weeklyEvaluationService->startScheduler();
        } catch (\Throwable $e) {
            $ok = false;
        }
        $_SESSION['messages'][$ok ? 'success' : 'error'] = $ok ? 'Scheduler started.' : 'Failed to start scheduler.';
        $this->redirect('/dashboard');
    }

    // Admin: stop scheduler
    public function stop(): void
    {
        $ok = false;
        try {
            $ok = $this->weeklyEvaluationService->stopScheduler();
        } catch (\Throwable $e) {
            $ok = false;
        }
        $_SESSION['messages'][$ok ? 'success' : 'error'] = $ok ? 'Scheduler stopped.' : 'Failed to stop scheduler.';
        $this->redirect('/dashboard');
    }

    // Teacher/Admin JSON: per-student weekly evaluations for chart modal
    public function studentData(string $studentId): void
    {
        header('Content-Type: application/json');
        try {
            $id = (int)$studentId;
            if ($id <= 0) { echo json_encode(['success' => false, 'message' => 'invalid student id']); return; }
            $data = $this->weeklyEvaluationService->getStudentWeeklyEvaluations($id, 520) ?? [];
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
