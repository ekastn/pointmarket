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
        $jwt = $_SESSION['jwt_token'] ?? null;

        if (!$jwt) {
            $this->redirect('/login');
            return;
        }

        $this->apiClient->setJwtToken($jwt);
        $userProfileResponse = $this->apiClient->getUserProfile();

        if (!$userProfileResponse['success']) {
            session_destroy();
            $this->redirect('/login');
            return;
        }

        $user = $userProfileResponse['data'];

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