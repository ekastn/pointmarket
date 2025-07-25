<?php

namespace App\Controllers;

use App\Services\ApiClient;

class ProgressController extends BaseController
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

        $assignmentStats = null;
        $recentActivities = [];
        $weeklyProgress = [];
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        // Fetch assignment/quiz statistics
        $assignmentStatsResponse = $this->apiClient->getAssignmentStatsByStudentID();
        if ($assignmentStatsResponse['success']) {
            $assignmentStats = $assignmentStatsResponse['data'];
        } else {
            $_SESSION['messages'] = ['error' => $assignmentStatsResponse['error'] ?? 'Failed to fetch assignment statistics.'];
        }

        // Fetch recent activity
        $recentActivitiesResponse = $this->apiClient->getRecentActivityByUserID();
        if ($recentActivitiesResponse['success']) {
            $recentActivities = $recentActivitiesResponse['data'] ?? [];
        } else {
            $_SESSION['messages'] = ['error' => $recentActivitiesResponse['error'] ?? 'Failed to fetch recent activities.'];
        }

        // Fetch weekly evaluation progress
        $weeklyProgressResponse = $this->apiClient->getWeeklyEvaluationProgressByStudentID();
        if ($weeklyProgressResponse['success']) {
            $weeklyProgress = $weeklyProgressResponse['data'] ?? [];
        } else {
            $_SESSION['messages'] = ['error' => $weeklyProgressResponse['error'] ?? 'Failed to fetch weekly progress.'];
        }

        $this->render('progress', [
            'title' => 'My Progress',
            'user' => $user,
            'assignmentStats' => $assignmentStats,
            'recentActivities' => $recentActivities,
            'weeklyProgress' => $weeklyProgress,
            'messages' => $messages,
        ]);
    }
}