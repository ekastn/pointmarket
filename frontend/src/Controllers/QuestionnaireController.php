<?php

namespace App\Controllers;

use App\Services\ApiClient;

class QuestionnaireController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function index(): void
    {
        session_start();
        $user = $_SESSION['user_data'] ?? null;

        $questionnaires = [];
        $history = [];
        $stats = [];
        $varkResult = null;
        $pendingEvaluations = [];
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        // Fetch all questionnaires
        $questionnairesResponse = $this->apiClient->getAllQuestionnaires();
        if ($questionnairesResponse['success']) {
            $questionnaires = $questionnairesResponse['data'] ?? [];
        } else {
            $_SESSION['messages'] = ['error' => $questionnairesResponse['error'] ?? 'Failed to fetch questionnaires.'];
        }

        // Fetch questionnaire history
        $historyResponse = $this->apiClient->getQuestionnaireHistory();
        if ($historyResponse['success']) {
            $history = $historyResponse['data'] ?? [];
        } else {
            $_SESSION['messages'] = ['error' => $historyResponse['error'] ?? 'Failed to fetch questionnaire history.'];
        }

        // Fetch questionnaire stats
        $statsResponse = $this->apiClient->getQuestionnaireStats();
        if ($statsResponse['success']) {
            $stats = $statsResponse['data'] ?? [];
        } else {
            $_SESSION['messages'] = ['error' => $statsResponse['error'] ?? 'Failed to fetch questionnaire statistics.'];
        }

        // Fetch VARK result (already implemented in VarkAssessmentController, but needed here for display)
        $varkResultResponse = $this->apiClient->getLatestVARKResult();
        if ($varkResultResponse['success'] && !empty($varkResultResponse['data'])) {
            $varkResult = $varkResultResponse['data'];
        }

        // Fetch pending weekly evaluations
        $pendingEvaluationsResponse = $this->apiClient->getPendingWeeklyEvaluations(); 
        if ($pendingEvaluationsResponse['success']) {
            $pendingEvaluations = $pendingEvaluationsResponse['data'] ?? [];
        } else {
            $_SESSION['messages'] = ['error' => $pendingEvaluationsResponse['error'] ?? 'Failed to fetch pending evaluations.'];
        }

        $this->render('questionnaire', [
            'title' => 'Questionnaires',
            'user' => $user,
            'questionnaires' => $questionnaires,
            'history' => $history,
            'stats' => $stats,
            'pendingEvaluations' => $pendingEvaluations,
            'varkResult' => $varkResult,
            'messages' => $messages,
        ]);
    }
}
