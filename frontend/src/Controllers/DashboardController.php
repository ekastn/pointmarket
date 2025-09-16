<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\DashboardService;
use App\Services\RecommendationService;

class DashboardController extends BaseController
{
    protected DashboardService $dashboardService;
    protected RecommendationService $recommendationService;

    public function __construct(ApiClient $apiClient, DashboardService $dashboardService, RecommendationService $recommendationService = null)
    {
        parent::__construct($apiClient);
        $this->dashboardService = $dashboardService;
        $this->recommendationService = $recommendationService ?? new RecommendationService($apiClient);
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
                $userId = $_SESSION['user_data']['id'] ?? null; // Pass current user id for lazy loads
                // Determine if we have sufficient psychometric coverage to request recommendations
                $recommendations = null;
                $missingAssessments = [];
                if ($studentStats && $userId) {
                    $hasMSLQ = !empty($studentStats['mslq_score']) && $studentStats['mslq_score'] > 0;
                    $hasAMS  = !empty($studentStats['ams_score'])  && $studentStats['ams_score'] > 0;
                    $learningStyle = $studentStats['learning_style'] ?? null;
                    $scores = $learningStyle['scores'] ?? [];
                    $hasVARK = isset($scores['visual'], $scores['auditory'], $scores['reading'], $scores['kinesthetic'])
                        && ($scores['visual'] ?? 0) > 0; // assume others >0 if one >0

                    if (!$hasVARK) $missingAssessments[] = 'VARK';
                    if (!$hasMSLQ) $missingAssessments[] = 'MSLQ';
                    if (!$hasAMS)  $missingAssessments[] = 'AMS';

                    if (empty($missingAssessments)) {
                        // Full coverage -> fetch
                        $recommendations = $this->recommendationService->getStudentRecommendations((int)$userId) ?? [];
                    }
                }

                $this->render('siswa/dashboard', [
                    'studentStats' => $studentStats,
                    'weekly_evaluations' => $weeklyEvaluations,
                    'recommendations' => $recommendations,
                    'missingAssessments' => $missingAssessments,
                ]);
                return;
        }
    }
}
