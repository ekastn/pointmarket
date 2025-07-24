<?php

namespace App\Controllers;

use App\Services\ApiClient;

class VarkCorrelationAnalysisController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function index(): void
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

        $user = $userProfileResponse['data']['data'];

        // In a real application, you would fetch these from the Go backend
        $vark_data = [
            'Visual' => 12,
            'Auditory' => 10,
            'Reading/Writing' => 14,
            'Kinesthetic' => 8
        ];
        $dominant_style = array_search(max($vark_data), $vark_data);

        $this->render('vark-correlation-analysis', [
            'title' => 'VARK Correlation Analysis',
            'user' => $user,
            'vark_data' => $vark_data,
            'dominant_style' => $dominant_style,
        ]);
    }
}
