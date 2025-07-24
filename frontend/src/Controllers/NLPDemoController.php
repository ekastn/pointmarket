<?php

namespace App\Controllers;

use App\Services\ApiClient;

class NLPDemoController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function show(): void
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

        $user = $userProfileResponse['data'];

        $nlpStats = null;
        $nlpStatsResponse = $this->apiClient->getNLPStats();
        if ($nlpStatsResponse['success'] && !empty($nlpStatsResponse['data'])) {
            $nlpStats = $nlpStatsResponse['data'];
        }

        $nlpResult = $_SESSION['nlp_result'] ?? null;
        unset($_SESSION['nlp_result']); // Clear after displaying

        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $this->render('nlp-demo', [
            'title' => 'NLP Demo',
            'user' => $user,
            'nlpStats' => $nlpStats,
            'nlpResult' => $nlpResult,
            'messages' => $messages,
        ]);
    }

    public function analyze(): void
    {
        session_start();
        $jwt = $_SESSION['jwt_token'] ?? null;

        if (!$jwt) {
            $this->redirect('/login');
            return;
        }

        $this->apiClient->setJwtToken($jwt);

        $textToAnalyze = $_POST['text_to_analyze'] ?? '';
        $contextType = $_POST['context_type'] ?? 'general';
        $assignmentId = isset($_POST['assignment_id']) && $_POST['assignment_id'] !== '' ? (int)$_POST['assignment_id'] : null;
        $quizId = isset($_POST['quiz_id']) && $_POST['quiz_id'] !== '' ? (int)$_POST['quiz_id'] : null;

        if (empty($textToAnalyze)) {
            $_SESSION['messages'] = ['error' => 'Text to analyze cannot be empty.'];
            $this->redirect('/nlp-demo');
            return;
        }

        $response = $this->apiClient->analyzeText($textToAnalyze, $contextType, $assignmentId, $quizId);

        if ($response['success']) {
            $_SESSION['nlp_result'] = $response['data'];
            $_SESSION['messages'] = ['success' => 'Text analyzed successfully!'];
        } else {
            $_SESSION['messages'] = ['error' => $response['error'] ?? 'Failed to perform NLP analysis.'];
        }

        $this->redirect('/nlp-demo');
    }
}
