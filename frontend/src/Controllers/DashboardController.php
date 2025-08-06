<?php

namespace App\Controllers;

use App\Core\ApiClient;

class DashboardController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function showDashboard(): void
    {
        session_start();
        $user = $_SESSION['user_data'] ?? null;

        // If user data is not in session (e.g., first login after middleware), fetch it
        if (!$user) {
            $userProfileResponse = $this->apiClient->getUserProfile();
            if ($userProfileResponse['success']) {
                $user = $userProfileResponse['data'];
                $_SESSION['user_data'] = $user;
            } else {
                // This case should ideally be handled by AuthMiddleware, but as a fallback
                $_SESSION['messages'] = ['error' => $userProfileResponse['error'] ?? 'Gagal memuat profil pengguna.'];
                session_destroy();
                $this->redirect('/login');
                return;
            }
        }

        // Make a single API call to get all dashboard data
        $dashboardResponse = $this->apiClient->getDashboardData();

        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        if ($dashboardResponse['success']) {
            $data = $dashboardResponse['data']; // This will contain the ComprehensiveDashboardDTO

            // Extract individual pieces of data from the consolidated response
            $userProfile = $data['user_profile'] ?? null;
            $nlpStats = $data['nlp_stats'] ?? null;
            $latestVARKResult = $data['latest_vark_result'] ?? null;
            $adminCounts = $data['admin_counts'] ?? null;
            $studentStats = $data['student_stats'] ?? null;
            $teacherCounts = $data['teacher_counts'] ?? null;
            $questionnaireStats = $data['questionnaire_stats'] ?? null;
            $recentActivities = $data['recent_activities'] ?? [];
            $assignmentStats = $data['assignment_stats'] ?? null;
            $weeklyProgress = $data['weekly_progress'] ?? null;

            // AI Metrics (these were hardcoded, so keep them as is or fetch from backend if they become dynamic)
            $aiMetrics = [
                'nlp' => ['accuracy' => 89, 'samples_processed' => 1247, 'avg_score' => 78.5, 'improvement_rate' => 12.3],
                'rl' => ['accuracy' => 87, 'decisions_made' => 892, 'avg_reward' => 0.84, 'learning_rate' => 0.95],
                'cbf' => ['accuracy' => 92, 'recommendations' => 567, 'click_through_rate' => 34.2, 'user_satisfaction' => 4.6]
            ];

            $viewName = $user['role'] . '/dashboard';

            // Pass all extracted data to the view
            $this->render($viewName, [
                'user' => $userProfile, // Use the userProfile from the consolidated data
                'messages' => $messages,
                'studentStats' => $studentStats,
                'questionnaireScores' => null, // This was not directly fetched, might need to be derived or removed
                'counts' => ($user['role'] === 'admin') ? $adminCounts : (($user['role'] === 'guru') ? $teacherCounts : null), // Pass appropriate counts based on role
                'aiMetrics' => $aiMetrics,
                'assignmentStats' => $assignmentStats,
                'questionnaireStats' => $questionnaireStats,
                'nlpStats' => $nlpStats,
                'varkResult' => $latestVARKResult,
                'weeklyProgress' => $weeklyProgress,
                'recentActivities' => $recentActivities,
            ]);
        } else {
            $_SESSION['messages']['error'] = $dashboardResponse['error'] ?? 'Failed to load dashboard data.';
            $this->redirect('/dashboard');
        }
    }
}
