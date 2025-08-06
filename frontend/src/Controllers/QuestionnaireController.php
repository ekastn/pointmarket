<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\QuestionnaireService;

class QuestionnaireController extends BaseController
{
    protected QuestionnaireService $questionnaireService;

    public function __construct(ApiClient $apiClient, QuestionnaireService $questionnaireService)
    {
        parent::__construct($apiClient);
        $this->questionnaireService = $questionnaireService;
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
        $questionnaires = $this->questionnaireService->getAllQuestionnaires();
        if ($questionnaires === null) {
            $_SESSION['messages']['error'] = 'Failed to fetch questionnaires.';
            $questionnaires = [];
        }

        // Fetch questionnaire history
        $history = $this->questionnaireService->getQuestionnaireHistory();
        if ($history === null) {
            $_SESSION['messages']['error'] = 'Failed to fetch questionnaire history.';
            $history = [];
        }

        // Fetch questionnaire stats
        $stats = $this->questionnaireService->getQuestionnaireStats();
        if ($stats === null) {
            $_SESSION['messages']['error'] = 'Failed to fetch questionnaire statistics.';
            $stats = [];
        }

        // Fetch VARK result
        $varkResult = $this->questionnaireService->getLatestVARKResult();
        if ($varkResult === null) {
            // Error already handled by service, just ensure it's null
            $varkResult = null;
        }

        $this->render('siswa/questionnaire', [
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
