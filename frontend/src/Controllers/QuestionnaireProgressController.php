<?php

namespace App\Controllers;

use App\Services\ApiClient;

class QuestionnaireProgressController extends BaseController
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
        $questionnaires = []; // Placeholder
        $history = []; // Placeholder
        $stats = []; // Placeholder
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $this->render('questionnaire-progress', [
            'title' => 'Questionnaire Progress',
            'user' => $user,
            'questionnaires' => $questionnaires,
            'history' => $history,
            'stats' => $stats,
            'messages' => $messages,
        ]);
    }
}
