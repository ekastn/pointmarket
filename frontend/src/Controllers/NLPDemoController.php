<?php

namespace App\Controllers;

use App\Core\ApiClient;

class NLPDemoController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function show(): void
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

        $nlpStats = null;
        $nlpStatsResponse = $this->apiClient->getNLPStats();
        if ($nlpStatsResponse['success'] && !empty($nlpStatsResponse['data'])) {
            $nlpStats = $nlpStatsResponse['data'];
        }

        $nlpResult = $_SESSION['nlp_result'] ?? null;
        unset($_SESSION['nlp_result']); // Clear after displaying

        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $this->render('siswa/nlp-demo', [
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
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $this->apiClient->setJwtToken($jwt);

        $input = json_decode(file_get_contents('php://input'), true);
        $textToAnalyze = $input['text_to_analyze'] ?? '';
        $contextType = $input['context_type'] ?? 'general';
        $assignmentId = isset($input['assignment_id']) && $input['assignment_id'] !== '' ? (int)$input['assignment_id'] : null;
        $quizId = isset($input['quiz_id']) && $input['quiz_id'] !== '' ? (int)$input['quiz_id'] : null;

        if (empty($textToAnalyze)) {
            echo json_encode(['success' => false, 'message' => 'Text to analyze cannot be empty.']);
            return;
        }

        $response = $this->apiClient->analyzeText($textToAnalyze, $contextType, $assignmentId, $quizId);

        if ($response['success']) {
            echo json_encode(['success' => true, 'data' => $response['data']]);
        } else {
            echo json_encode(['success' => false, 'message' => $response['error'] ?? 'Failed to perform NLP analysis.']);
        }
    }
}
