<?php

namespace App\Controllers;

use App\Services\ApiClient;

class ProfileController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function showProfile(): void
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
            $_SESSION['messages'] = ['error' => $userProfileResponse['error'] ?? 'Failed to load user profile.'];
            session_destroy();
            $this->redirect('/login');
            return;
        }

        $user = $userProfileResponse['data'];

        $assignmentStats = null;
        $questionnaireStats = [];
        $nlpStats = null;
        $varkResult = null;
        $weeklyProgress = [];
        $recentActivities = [];

        // Fetch additional data for student profile
        if ($user['role'] === 'siswa') {
            $assignmentStatsResponse = $this->apiClient->getAssignmentStatsByStudentID();
            if ($assignmentStatsResponse['success']) {
                $assignmentStats = $assignmentStatsResponse['data'];
            }

            $questionnaireStatsResponse = $this->apiClient->getQuestionnaireStats();
            if ($questionnaireStatsResponse['success']) {
                $questionnaireStats = $questionnaireStatsResponse['data'] ?? [];
            }

            $nlpStatsResponse = $this->apiClient->getNLPStats();
            if ($nlpStatsResponse['success']) {
                $nlpStats = $nlpStatsResponse['data'] ?? null;
            }

            $varkResultResponse = $this->apiClient->getLatestVARKResult();
            if ($varkResultResponse['success']) {
                $varkResult = $varkResultResponse['data'] ?? null;
            }

            $weeklyProgressResponse = $this->apiClient->getWeeklyEvaluationProgressByStudentID();
            if ($weeklyProgressResponse['success']) {
                $weeklyProgress = $weeklyProgressResponse['data'] ?? [];
            }

            $recentActivitiesResponse = $this->apiClient->getRecentActivityByUserID();
            if ($recentActivitiesResponse['success']) {
                $recentActivities = $recentActivitiesResponse['data'] ?? [];
            }
        }

        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $this->render('profile', [
            'title' => 'My Profile',
            'user' => $user,
            'assignmentStats' => $assignmentStats,
            'questionnaireStats' => $questionnaireStats,
            'nlpStats' => $nlpStats,
            'varkResult' => $varkResult,
            'weeklyProgress' => $weeklyProgress,
            'recentActivities' => $recentActivities,
            'messages' => $messages,
        ]);
    }

    public function updateProfile(): void
    {
        session_start();
        $jwt = $_SESSION['jwt_token'] ?? null;

        if (!$jwt) {
            $this->redirect('/login');
            return;
        }

        $this->apiClient->setJwtToken($jwt);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'avatar' => $_POST['avatar'] ?? null, // Assuming avatar is a URL or path
            ];

            $response = $this->apiClient->updateProfile($data);

            if ($response['success']) {
                $_SESSION['messages'] = ['success' => 'Profile updated successfully!'];
                $this->redirect('/profile');
            } else {
                $_SESSION['messages'] = ['error' => $response['error'] ?? 'Failed to update profile.'];
                $this->redirect('/profile');
            }
        } else {
            $this->redirect('/profile');
        }
    }
}
