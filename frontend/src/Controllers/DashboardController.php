<?php

namespace App\Controllers;

use App\Services\ApiClient;

class DashboardController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function showDashboard(): void
    {
        session_start();
        $jwt = $_SESSION['jwt_token'] ?? null;

        if (!$jwt) {
            $_SESSION['messages'] = ['error' => 'Sesi Anda telah berakhir. Silakan login kembali.'];
            $this->redirect('/login');
            return;
        }

        $this->apiClient->setJwtToken($jwt);

        $userProfileResponse = $this->apiClient->getUserProfile();

        if (!$userProfileResponse['success']) {
            $_SESSION['messages'] = ['error' => $userProfileResponse['error'] ?? 'Gagal memuat profil pengguna.'];
            session_destroy(); // Clear session on API error
            $this->redirect('/login');
            return;
        }

        $user = $userProfileResponse['data']['data'];

        // Fetch other dashboard data using $this->apiClient
        // For now, just pass the user data and messages
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $this->render('dashboard', [
            'user' => $user,
            'messages' => $messages,
            // Add other dashboard data here once API methods are implemented
            'studentStats' => null, // Placeholder
            'questionnaireScores' => null, // Placeholder
            'counts' => null, // Placeholder
        ]);
    }
}