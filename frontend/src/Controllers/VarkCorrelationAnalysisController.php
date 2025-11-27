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
        $vark_data = [];
        $dominant_style = 'N/A';
        $mslq_score = 'N/A';
        $ams_score = 'N/A';
        $mslq_insight = '';
        $ams_insight = '';
        $correlation_results = null;

        $correlationResults = $this->varkCorrelationService->analyzeCorrelation();

        $user = $_SESSION['user_data'] ?? null;

        if ($correlationResults !== null) {
            $vark_data = $correlationResults['vark_scores'] ?? [];
            $dominant_style = $correlationResults['dominant_vark_style'] ?? 'N/A';
            $mslq_score = $correlationResults['mslq_score'] ?? 'N/A';
            $ams_score = $correlationResults['ams_score'] ?? 'N/A';
            $mslq_insight = $correlationResults['mslq_insight'] ?? '';
            $ams_insight = $correlationResults['ams_insight'] ?? '';
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
            'mslq_insight' => $mslq_insight,
            'ams_insight' => $ams_insight,
            'correlation_results' => $correlation_results,
        ]);
    }
}
