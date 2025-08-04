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

        $vark_data = [];
        $dominant_style = 'N/A';
        $mslq_score = 'N/A';
        $ams_score = 'N/A';
        $correlation_results = null;

        $correlationResponse = $this->apiClient->analyzeCorrelation();

        if ($correlationResponse['success'] && isset($correlationResponse['data'])) {
            $data = $correlationResponse['data'];
            $vark_data = $data['vark_scores'] ?? [];
            $dominant_style = $data['dominant_vark_style'] ?? 'N/A';
            $mslq_score = $data['mslq_score'] ?? 'N/A';
            $ams_score = $data['ams_score'] ?? 'N/A';
            $correlation_results = $data;
        } else {
            $_SESSION['messages']['warning'] = $correlationResponse['error'] ?? 'Gagal memuat analisis korelasi.';
        }

        $this->render('siswa/vark-correlation-analysis', [
            'title' => 'VARK Correlation Analysis',
            'user' => $user,
            'vark_data' => $vark_data,
            'dominant_style' => $dominant_style,
            'mslq_score' => $mslq_score,
            'ams_score' => $ams_score,
            'correlation_results' => $correlation_results,
        ]);
    }
}