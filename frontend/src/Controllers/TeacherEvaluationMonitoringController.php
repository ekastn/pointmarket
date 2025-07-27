<?php

namespace App\Controllers;

use App\Services\ApiClient;

class TeacherEvaluationMonitoringController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function index(): void
    {
        session_start();
        $user = $_SESSION['user_data'] ?? null;

        // This should ideally be handled by AuthMiddleware, but as a fallback
        if (!$user) {
            $userProfileResponse = $this->apiClient->getUserProfile();
            if ($userProfileResponse['success']) {
                $user = $userProfileResponse['data'];
                $_SESSION['user_data'] = $user;
            } else {
                $_SESSION['messages'] = ['error' => $userProfileResponse['error'] ?? 'Gagal memuat profil pengguna.'];
                session_destroy();
                $this->redirect('/login');
                return;
            }
        }

        $studentStatus = [];
        $weeklyOverview = [];
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        // Fetch student evaluation status
        $studentStatusResponse = $this->apiClient->getStudentEvaluationStatus();
        if ($studentStatusResponse['success']) {
            $studentStatus = $studentStatusResponse['data'] ?? [];
        } else {
            $_SESSION['messages'] = ['error' => $studentStatusResponse['error'] ?? 'Failed to fetch student statuses.'];
        }

        // Fetch weekly evaluation overview
        $weeklyOverviewResponse = $this->apiClient->getWeeklyEvaluationOverview();
        if ($weeklyOverviewResponse['success']) {
            $weeklyOverview = $weeklyOverviewResponse['data'] ?? [];
        } else {
            $_SESSION['messages'] = ['error' => $weeklyOverviewResponse['error'] ?? 'Failed to fetch weekly overview.'];
        }

        $this->render('teacher-evaluation-monitoring', [
            'title' => 'Teacher Evaluation Monitoring',
            'user' => $user,
            'studentStatus' => $studentStatus,
            'weeklyOverview' => $weeklyOverview,
            'messages' => $messages,
        ]);
    }
}