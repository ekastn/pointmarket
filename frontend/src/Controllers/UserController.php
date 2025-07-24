<?php

namespace App\Controllers;

use App\Services\ApiClient;

class UserController extends BaseController
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
            session_destroy();
            $this->redirect('/login');
            return;
        }

        $user = $userProfileResponse['data'];

        // In a real application, you would render a profile view
        // For now, we can just show a placeholder or redirect to the dashboard
        // For this refactoring, let's assume we want to show the dashboard with the user's profile data
        $this->render('dashboard', [
            'title' => 'Profile',
            'user' => $user,
        ]);
    }
}
