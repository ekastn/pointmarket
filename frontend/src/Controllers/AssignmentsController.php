<?php

namespace App\Controllers;

use App\Services\ApiClient;

class AssignmentsController extends BaseController
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

        // In a real application, you would fetch these from the Go backend
        $assignments = []; // Placeholder
        $stats = ['total_assignments' => 0, 'completed' => 0, 'in_progress' => 0, 'overdue' => 0, 'total_points' => 0, 'average_score' => 0]; // Placeholder
        $subjects = []; // Placeholder
        $pendingEvaluations = []; // Placeholder
        $status_filter = $_GET['status'] ?? 'all';
        $subject_filter = $_GET['subject'] ?? 'all';
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $this->render('assignments', [
            'title' => 'Assignments',
            'user' => $user,
            'assignments' => $assignments,
            'stats' => $stats,
            'subjects' => $subjects,
            'pendingEvaluations' => $pendingEvaluations,
            'status_filter' => $status_filter,
            'subject_filter' => $subject_filter,
            'messages' => $messages,
        ]);
    }
}