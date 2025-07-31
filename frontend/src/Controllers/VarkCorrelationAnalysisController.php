<?php

namespace App\Controllers;

use App\Core\ApiClient;

class VarkCorrelationAnalysisController extends BaseController
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

        // In a real application, you would fetch these from the Go backend
        $vark_data = [
            'Visual' => 12,
            'Auditory' => 10,
            'Reading/Writing' => 14,
            'Kinesthetic' => 8
        ];
        $dominant_style = array_search(max($vark_data), $vark_data);

        $this->render('siswa/vark-correlation-analysis', [
            'title' => 'VARK Correlation Analysis',
            'user' => $user,
            'vark_data' => $vark_data,
            'dominant_style' => $dominant_style,
        ]);
    }
}