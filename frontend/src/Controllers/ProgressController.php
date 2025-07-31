<?php

namespace App\Controllers;

use App\Core\ApiClient;

class ProgressController extends BaseController
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

        $assignmentStats = null;
        $recentActivities = [];
        $weeklyProgress = [];
        $questionnaireStats = [];
        $nlpStats = null;
        $varkResult = null;
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

        // Fetch questionnaire statistics
        $questionnaireStatsResponse = $this->apiClient->getQuestionnaireStats();
        if ($questionnaireStatsResponse['success']) {
            $questionnaireStats = $questionnaireStatsResponse['data'] ?? [];
        } else {
            $_SESSION['messages'] = ['error' => $questionnaireStatsResponse['error'] ?? 'Failed to fetch questionnaire statistics.'];
        }

        // Fetch NLP statistics
        $nlpStatsResponse = $this->apiClient->getNLPStats();
        if ($nlpStatsResponse['success']) {
            $nlpStats = $nlpStatsResponse['data'] ?? null;
        } else {
            $_SESSION['messages'] = ['error' => $nlpStatsResponse['error'] ?? 'Failed to fetch NLP statistics.'];
        }

        // Fetch VARK result
        $varkResultResponse = $this->apiClient->getLatestVARKResult();
        if ($varkResultResponse['success']) {
            $varkResult = $varkResultResponse['data'] ?? null;
        } else {
            $_SESSION['messages'] = ['error' => $varkResultResponse['error'] ?? 'Failed to fetch VARK result.'];
        }

        $this->render('siswa/progress', [
            'title' => 'My Progress',
            'user' => $user,
            'assignmentStats' => $assignmentStats,
            'recentActivities' => $recentActivities,
            'weeklyProgress' => $weeklyProgress,
            'questionnaireStats' => $questionnaireStats,
            'nlpStats' => $nlpStats,
            'varkResult' => $varkResult,
            'messages' => $messages,
        ]);
    }
}