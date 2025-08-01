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

        $studentStats = null;
        $questionnaireScores = null;
        $counts = null;
        $varkResult = null;
        $aiMetrics = [
            'nlp' => ['accuracy' => 89, 'samples_processed' => 1247, 'avg_score' => 78.5, 'improvement_rate' => 12.3],
            'rl' => ['accuracy' => 87, 'decisions_made' => 892, 'avg_reward' => 0.84, 'learning_rate' => 0.95],
            'cbf' => ['accuracy' => 92, 'recommendations' => 567, 'click_through_rate' => 34.2, 'user_satisfaction' => 4.6]
        ];

        switch ($user['role']) {
            case 'siswa':
                $studentStats = [
                    'total_points' => 0,
                    'completed_assignments' => 0,
                    'mslq_score' => null,
                    'ams_score' => null,
                    'vark_dominant_style' => null,
                    'vark_learning_preference' => null,
                    'total_assignments' => 0,
                    'avg_score' => 0,
                    'best_score' => 0,
                ];
                $questionnaireScores = ['mslq' => null, 'ams' => null, 'vark' => null];
                $assignmentStats = null;
                $questionnaireStats = [];
                $nlpStats = null;
                $varkResult = null;
                $weeklyProgress = [];
                $recentActivities = [];

                $studentStatsResponse = $this->apiClient->getStudentDashboardStats();
                if ($studentStatsResponse['success']) {
                    $studentStats = array_merge($studentStats, $studentStatsResponse['data']);
                } else {
                    $_SESSION['messages'] = ['error' => $studentStatsResponse['error'] ?? 'Failed to fetch student dashboard stats.'];
                }

                // Fetch assignment/quiz statistics
                $assignmentStatsResponse = $this->apiClient->getAssignmentStatsByStudentID();
                if ($assignmentStatsResponse['success']) {
                    $assignmentStats = $assignmentStatsResponse['data'];
                    // Merge assignment stats into studentStats for dashboard display
                    if ($assignmentStats) {
                        $studentStats['total_assignments'] = $assignmentStats['total_assignments'] ?? 0;
                        $studentStats['avg_score'] = $assignmentStats['avg_score'] ?? 0;
                        $studentStats['best_score'] = $assignmentStats['best_score'] ?? 0;
                    }
                }

                // Fetch questionnaire statistics
                $questionnaireStatsResponse = $this->apiClient->getQuestionnaireStats();
                if ($questionnaireStatsResponse['success']) {
                    $questionnaireStats = $questionnaireStatsResponse['data'] ?? [];
                }

                // Fetch NLP statistics
                $nlpStatsResponse = $this->apiClient->getNLPStats();
                if ($nlpStatsResponse['success']) {
                    $nlpStats = $nlpStatsResponse['data'] ?? null;
                }

                // Fetch VARK result
                $varkResultResponse = $this->apiClient->getLatestVARKResult();
                if ($varkResultResponse['success']) {
                    $varkResult = $varkResultResponse['data'] ?? null;
                    if ($varkResult) {
                        $studentStats['vark_dominant_style'] = $varkResult['dominant_style'] ?? null;
                        $studentStats['vark_learning_preference'] = $varkResult['learning_preference'] ?? null;
                        $studentStats['visual_score'] = $varkResult['visual_score'] ?? null;
                        $studentStats['auditory_score'] = $varkResult['auditory_score'] ?? null;
                        $studentStats['reading_score'] = $varkResult['reading_score'] ?? null;
                        $studentStats['kinesthetic_score'] = $varkResult['kinesthetic_score'] ?? null;
                    }
                }

                // Fetch weekly evaluation progress
                $weeklyProgressResponse = $this->apiClient->getWeeklyEvaluationProgressByStudentID();
                if ($weeklyProgressResponse['success']) {
                    $weeklyProgress = $weeklyProgressResponse['data'] ?? [];
                }

                // Fetch recent activity
                $recentActivitiesResponse = $this->apiClient->getRecentActivityByUserID();
                if ($recentActivitiesResponse['success']) {
                    $recentActivities = $recentActivitiesResponse['data'] ?? [];
                }
                break;
            case 'guru':
                $teacherCountsResponse = $this->apiClient->getTeacherDashboardCounts();
                if ($teacherCountsResponse['success']) {
                    $counts = $teacherCountsResponse['data'];
                } else {
                    $_SESSION['messages'] = ['error' => $teacherCountsResponse['error'] ?? 'Failed to fetch teacher dashboard counts.'];
                }
                break;
            case 'admin':
                $adminCountsResponse = $this->apiClient->getAdminDashboardCounts();
                if ($adminCountsResponse['success']) {
                    $counts = $adminCountsResponse['data'];
                } else {
                    $_SESSION['messages'] = ['error' => $adminCountsResponse['error'] ?? 'Failed to fetch admin dashboard counts.'];
                }
                break;
        }

        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $viewName = $user['role'] . '/dashboard';

        $this->render($viewName, [
            'user' => $user,
            'messages' => $messages,
            'studentStats' => $studentStats,
            'questionnaireScores' => $questionnaireScores,
            'counts' => $counts,
            'aiMetrics' => $aiMetrics,
            'assignmentStats' => $assignmentStats,
            'questionnaireStats' => $questionnaireStats,
            'nlpStats' => $nlpStats,
            'varkResult' => $varkResult,
            'weeklyProgress' => $weeklyProgress,
            'recentActivities' => $recentActivities,
        ]);
    }
}
