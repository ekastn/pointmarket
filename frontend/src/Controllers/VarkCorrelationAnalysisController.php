<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\VarkCorrelationService;

class VarkCorrelationAnalysisController extends BaseController
{
    protected VarkCorrelationService $varkCorrelationService;

    public function __construct(ApiClient $apiClient, VarkCorrelationService $varkCorrelationService)
    {
        parent::__construct($apiClient);
        $this->varkCorrelationService = $varkCorrelationService;
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

        $correlationResults = $this->varkCorrelationService->analyzeCorrelation();

        if ($correlationResults !== null) {
            $vark_data = $correlationResults['vark_scores'] ?? [];
            $dominant_style = $correlationResults['dominant_vark_style'] ?? 'N/A';
            $mslq_score = $correlationResults['mslq_score'] ?? 'N/A';
            $ams_score = $correlationResults['ams_score'] ?? 'N/A';
            $correlation_results = $correlationResults;
        } else {
            $_SESSION['messages']['warning'] = 'Failed to load correlation analysis.';
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