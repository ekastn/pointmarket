<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\ProfileService;
use App\Services\QuestionnaireService;

class ProfileController extends BaseController
{
    protected ProfileService $profileService;
    protected QuestionnaireService $questionnaireService;

    public function __construct(ApiClient $apiClient, ProfileService $profileService, QuestionnaireService $questionnaireService)
    {
        parent::__construct($apiClient);
        $this->profileService = $profileService;
        $this->questionnaireService = $questionnaireService;
    }

    public function showProfile(): void
    {
        $userProfile = $this->profileService->getUserProfile();
        if ($userProfile === null) {
            $_SESSION['messages'] = ['error' => 'Failed to load user profile.'];
            session_destroy();
            $this->redirect('/login');
            return;
        }

        $assignmentStats = null;
        $questionnaireStats = [];
        $nlpStats = null;
        $varkResult = null;
        $weeklyProgress = [];
        $recentActivities = [];

        // Fetch additional data for student profile
        if ($userProfile['role'] === 'siswa') {
            $assignmentStats = $this->profileService->getAssignmentStatsByStudentID();
            if ($assignmentStats === null) {
                $_SESSION['messages']['error'] = 'Failed to fetch assignment stats.';
                $assignmentStats = null;
            }

            $questionnaireStats = $this->questionnaireService->getQuestionnaireStats();
            if ($questionnaireStats === null) {
                $_SESSION['messages']['error'] = 'Failed to fetch questionnaire stats.';
                $questionnaireStats = [];
            }

            $nlpStats = $this->profileService->getNLPStats();
            if ($nlpStats === null) {
                $_SESSION['messages']['error'] = 'Failed to fetch NLP stats.';
                $nlpStats = null;
            }

            $varkResult = $this->questionnaireService->getLatestVARKResult();
            if ($varkResult === null) {
                $_SESSION['messages']['error'] = 'Failed to fetch VARK result.';
                $varkResult = null;
            }

            $weeklyProgress = $this->profileService->getWeeklyEvaluationProgressByStudentID();
            if ($weeklyProgress === null) {
                $_SESSION['messages']['error'] = 'Failed to fetch weekly progress.';
                $weeklyProgress = [];
            }

            $recentActivities = $this->profileService->getRecentActivityByUserID();
            if ($recentActivities === null) {
                $_SESSION['messages']['error'] = 'Failed to fetch recent activities.';
                $recentActivities = [];
            }
        }

        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $viewName = $userProfile['role'] . '/profile';

        $this->render($viewName, [
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

            $result = $this->profileService->updateProfile($data);

            if ($result !== null) {
                $_SESSION['messages'] = ['success' => 'Profile updated successfully!'];
                // Update user_data in session after successful profile update
                $userProfileData = $this->profileService->getUserProfile();
                if ($userProfileData !== null) {
                    $_SESSION['user_data'] = $userProfileData;
                }
                $this->redirect('/profile');
            } else {
                $_SESSION['messages'] = ['error' => 'Failed to update profile.'];
                $this->redirect('/profile');
            }
        } else {
            $this->redirect('/profile');
        }
    }
}
