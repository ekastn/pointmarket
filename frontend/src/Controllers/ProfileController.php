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

        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $this->render('profile', [
            'title' => 'My Profile',
            'user' => $user,
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
