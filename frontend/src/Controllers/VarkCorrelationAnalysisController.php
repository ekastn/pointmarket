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

        // Fetch VARK data
        $vark_data = [];
        $dominant_style = 'N/A';
        $varkResult = $this->apiClient->getLatestVARKResult();
        if ($varkResult['success'] && isset($varkResult['data'])) {
            $vark_data = [
                'Visual' => $varkResult['data']['visual_score'] ?? 0,
                'Auditory' => $varkResult['data']['auditory_score'] ?? 0,
                'Reading/Writing' => $varkResult['data']['reading_score'] ?? 0,
                'Kinesthetic' => $varkResult['data']['kinesthetic_score'] ?? 0
            ];
            // Ensure dominant_style is correctly extracted and not null
            $dominant_style = $varkResult['data']['dominant_style'] ?? 'N/A';
        } else {
            $_SESSION['messages']['warning'] = ($_SESSION['messages']['warning'] ?? '') . 'Gagal memuat data VARK. ';
        }

        // Fetch MSLQ data
        $mslq_score = 'N/A';
        $mslqResult = $this->apiClient->getLatestQuestionnaireResultByType('mslq');
        if ($mslqResult['success'] && isset($mslqResult['data']['total_score'])) {
            $mslq_score = $mslqResult['data']['total_score'];
        } else {
            $_SESSION['messages']['warning'] = ($_SESSION['messages']['warning'] ?? '') . 'Gagal memuat data MSLQ. ';
        }

        // Fetch AMS data
        $ams_score = 'N/A';
        $amsResult = $this->apiClient->getLatestQuestionnaireResultByType('ams');
        if ($amsResult['success'] && isset($amsResult['data']['total_score'])) {
            $ams_score = $amsResult['data']['total_score'];
        } else {
            $_SESSION['messages']['warning'] = ($_SESSION['messages']['warning'] ?? '') . 'Gagal memuat data AMS. ';
        }

        // Analyze correlation
        $correlation_results = null;
        if ($vark_data !== [] && $mslq_score !== 'N/A' && $ams_score !== 'N/A') {
            $correlationResponse = $this->apiClient->analyzeCorrelation($vark_data, (float)$mslq_score, (float)$ams_score);
            if ($correlationResponse['success'] && isset($correlationResponse['data'])) {
                $correlation_results = $correlationResponse['data'];
            } else {
                $_SESSION['messages']['warning'] = ($_SESSION['messages']['warning'] ?? '') . 'Gagal menganalisis korelasi. ';
            }
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