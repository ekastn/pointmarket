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

        $user = $userProfileResponse['data']['data'];

        // In a real application, you would fetch these from the Go backend
        $studentStatus = []; // Placeholder
        $weeklyOverview = []; // Placeholder
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $this->render('teacher-evaluation-monitoring', [
            'title' => 'Teacher Evaluation Monitoring',
            'user' => $user,
            'studentStatus' => $studentStatus,
            'weeklyOverview' => $weeklyOverview,
            'messages' => $messages,
        ]);
    }
}
