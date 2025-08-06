<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\WeeklyEvaluationService;

class TeacherEvaluationMonitoringController extends BaseController
{
    protected WeeklyEvaluationService $weeklyEvaluationService;

    public function __construct(ApiClient $apiClient, WeeklyEvaluationService $weeklyEvaluationService)
    {
        parent::__construct($apiClient);
        $this->weeklyEvaluationService = $weeklyEvaluationService;
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
        $studentStatus = $this->weeklyEvaluationService->getStudentEvaluationStatus();
        if ($studentStatus === null) {
            $_SESSION['messages'] = ['error' => 'Failed to fetch student statuses.'];
            $studentStatus = [];
        }

        // Fetch weekly evaluation overview
        $weeklyOverview = $this->weeklyEvaluationService->getWeeklyEvaluationOverview();
        if ($weeklyOverview === null) {
            $_SESSION['messages'] = ['error' => 'Failed to fetch weekly overview.'];
            $weeklyOverview = [];
        }

        $this->render('guru/teacher-evaluation-monitoring', [
            'title' => 'Teacher Evaluation Monitoring',
            'user' => $user,
            'studentStatus' => $studentStatus,
            'weeklyOverview' => $weeklyOverview,
            'messages' => $messages,
        ]);
    }
}