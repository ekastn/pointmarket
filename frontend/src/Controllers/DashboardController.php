<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\DashboardService;
use App\Services\WeeklyEvaluationService;

class DashboardController extends BaseController
{
    protected DashboardService $dashboardService;

    protected WeeklyEvaluationService $weeklyEvaluationService;

    public function __construct(ApiClient $apiClient, DashboardService $dashboardService, WeeklyEvaluationService $weeklyEvaluationService)
    {
        parent::__construct($apiClient);
        $this->dashboardService = $dashboardService;
        $this->weeklyEvaluationService = $weeklyEvaluationService;
    }

    public function showDashboard(): void
    {
        // Make a single API call to get all dashboard data
        $dashboardData = $this->dashboardService->getDashboardData();

        if ($dashboardData === null) {
            throw new \Exception('Failed to fetch dashboard data.');
        }

        $userRole = $_SESSION['user_data']['role'];

        switch ($userRole) {
            case 'admin':
                $adminStats = $dashboardData['admin_stats'] ?? null;
                $this->render('admin/dashboard', [
                    'adminStats' => $adminStats
                ]);
                return;
            case 'guru':
                $teacherStats = $dashboardData['teacher_stats'] ?? null;
                $this->render('guru/dashboard', [
                    'teacherStats' => $teacherStats
                ]);
                return;
            case 'siswa':
                $studentStats = $dashboardData['student_stats'] ?? null;
                $this->render('siswa/dashboard', [
                    'studentStats' => $studentStats,
                ]);
                return;
        }

        $latestVARKResult = $dashboardData['latest_vark_result'] ?? null;
        $adminCounts = $dashboardData['admin_counts'] ?? null;
        $studentStats = $dashboardData['student_stats'] ?? null;
        $teacherCounts = $dashboardData['teacher_counts'] ?? null;
        $questionnaireStats = $dashboardData['questionnaire_stats'] ?? null;
        $recentActivities = $dashboardData['recent_activities'] ?? [];
        $assignmentStats = $dashboardData['assignment_stats'] ?? null;
        $weeklyProgress = $dashboardData['weekly_progress'] ?? null;

        // Fetch pending weekly evaluations using the new service
        $pendingEvaluations = $this->weeklyEvaluationService->getPendingWeeklyEvaluations();
        if ($pendingEvaluations === null) {
            $_SESSION['messages']['error'] = 'Failed to fetch pending evaluations.';
            $pendingEvaluations = [];
        }

        // AI Metrics (these were hardcoded, so keep them as is or fetch from backend if they become dynamic)
        $aiMetrics = [
            'nlp' => ['accuracy' => 89, 'samples_processed' => 1247, 'avg_score' => 78.5, 'improvement_rate' => 12.3],
            'rl' => ['accuracy' => 87, 'decisions_made' => 892, 'avg_reward' => 0.84, 'learning_rate' => 0.95],
            'cbf' => ['accuracy' => 92, 'recommendations' => 567, 'click_through_rate' => 34.2, 'user_satisfaction' => 4.6],
        ];

        $viewName = $_SESSION['user_data']['role'].'/dashboard';

        // Pass all extracted data to the view
        $this->render($viewName, [
            'studentStats' => $studentStats,
            'questionnaireScores' => null, // This was not directly fetched, might need to be derived or removed
            'aiMetrics' => $aiMetrics,
            'assignmentStats' => $assignmentStats,
            'questionnaireStats' => $questionnaireStats,
            'varkResult' => $latestVARKResult,
            'weeklyProgress' => $weeklyProgress,
            'recentActivities' => $recentActivities,
            'pendingEvaluations' => $pendingEvaluations, // NEW: Pass pending evaluations
        ]);
    }
}
