<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\DashboardService;

class DashboardController extends BaseController
{
    protected DashboardService $dashboardService;


    public function __construct(ApiClient $apiClient, DashboardService $dashboardService)
    {
        parent::__construct($apiClient);
        $this->dashboardService = $dashboardService;
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
                $weeklyEvaluations = $studentStats['weekly_evaluations'] ?? []; // Extract the new data
                $this->render('siswa/dashboard', [
                    'studentStats' => $studentStats,
                    'weekly_evaluations' => $weeklyEvaluations, // Pass the extracted data to the view
                ]);
                return;
        }
    }
}
