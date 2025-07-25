<?php

namespace App\Controllers;

use App\Services\ApiClient;

class DashboardController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function showDashboard(): void
    {
        session_start();
        $jwt = $_SESSION['jwt_token'] ?? null;

        if (!$jwt) {
            $_SESSION['messages'] = ['error' => 'Sesi Anda telah berakhir. Silakan login kembali.'];
            $this->redirect('/login');
            return;
        }

        $this->apiClient->setJwtToken($jwt);

        $userProfileResponse = $this->apiClient->getUserProfile();

        if (!$userProfileResponse['success']) {
            $_SESSION['messages'] = ['error' => $userProfileResponse['error'] ?? 'Gagal memuat profil pengguna.'];
            session_destroy(); // Clear session on API error
            $this->redirect('/login');
            return;
        }

        $user = $userProfileResponse['data'];

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
                $studentStatsResponse = $this->apiClient->getStudentDashboardStats();
                if ($studentStatsResponse['success']) {
                    $studentStats = $studentStatsResponse['data'];
                    // Assuming VARK data is part of studentStats or fetched separately
                    $varkResult = [
                        'dominant_style' => $studentStats['vark_dominant_style'] ?? null,
                        'learning_preference' => $studentStats['vark_learning_preference'] ?? null,
                        // Add other VARK scores if available in studentStats
                    ];
                    $questionnaireScores = [
                        'mslq' => $studentStats['mslq_score'] ?? null,
                        'ams' => $studentStats['ams_score'] ?? null,
                        'vark' => $varkResult,
                    ];
                } else {
                    $_SESSION['messages'] = ['error' => $studentStatsResponse['error'] ?? 'Failed to fetch student dashboard stats.'];
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

        $this->render('dashboard', [
            'user' => $user,
            'messages' => $messages,
            'studentStats' => $studentStats,
            'questionnaireScores' => $questionnaireScores,
            'counts' => $counts,
            'aiMetrics' => $aiMetrics,
        ]);
    }
}
