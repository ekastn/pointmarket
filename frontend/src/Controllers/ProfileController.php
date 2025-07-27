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
        // This should ideally be handled by AuthMiddleware, but as a fallback
        if (!isset($_SESSION['jwt_token'])) {
            $this->redirect('/login');
            return;
        }

        $this->apiClient->setJwtToken($_SESSION['jwt_token']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'avatar' => $_POST['avatar'] ?? null, // Assuming avatar is a URL or path
            ];

            $response = $this->apiClient->updateProfile($data);

            if ($response['success']) {
                $_SESSION['messages'] = ['success' => 'Profile updated successfully!'];
                // Update user_data in session after successful profile update
                $userProfileResponse = $this->apiClient->getUserProfile();
                if ($userProfileResponse['success']) {
                    $_SESSION['user_data'] = $userProfileResponse['data'];
                }
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
