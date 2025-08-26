<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\ProfileService;

class ProfileController extends BaseController
{
    protected ProfileService $profileService;

    public function __construct(ApiClient $apiClient, ProfileService $profileService)
    {
        parent::__construct($apiClient);
        $this->profileService = $profileService;
    }

    public function showProfile(): void
    {
        $userProfile = $this->profileService->getUserProfile();
        if ($userProfile === null) {
            $_SESSION['messages'] = ['error' => 'Failed to load user profile.'];
        }

        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $viewName = 'profile';

        $this->render($viewName, [
            'title' => 'My Profile',
            'user' => $userProfile,
            'messages' => $messages,
        ]);
    }

    public function updateProfile(): void
    {
        if (!isset($_SESSION['jwt_token'])) {
            $this->redirect('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'avatar' => $_POST['avatar'] ?? null, // Assuming avatar is a URL or path
                'bio' => $_POST['bio'] ?? null,
            ];

            $ok = $this->profileService->updateProfile($data);

            if ($ok) {
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
