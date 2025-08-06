<?php

namespace App\Controllers;

use App\Core\ApiClient;

class DemoController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function showNLPDemo(): void
    {
        session_start();
        $user = $_SESSION['user_data'] ?? null;

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
        $nlpStatsResponse = $this->apiClient->request('GET', '/api/v1/nlp/stats');
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

    public function analyzeNLP(): void
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

        $response = $this->apiClient->request('POST', '/api/v1/nlp/analyze', [
            'json' => [
                'text' => $textToAnalyze,
                'context_type' => $contextType,
                'assignment_id' => $assignmentId,
                'quiz_id' => $quizId,
            ],
        ]);

        if ($response['success']) {
            echo json_encode(['success' => true, 'data' => $response['data']]);
        } else {
            echo json_encode(['success' => false, 'message' => $response['error'] ?? 'Failed to perform NLP analysis.']);
        }
    }

    public function showAIExplanation(): void
    {
        session_start();
        $user = $_SESSION['user_data'] ?? null;

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

        $this->render('siswa/ai-explanation', [
            'title' => 'AI Explanation',
            'user' => $user,
        ]);
    }

    public function showAIRecommendations(): void
    {
        session_start();
        $user = $_SESSION['user_data'] ?? null;

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

        // In a real application, you would fetch these from the Go backend
        $aiMetrics = [
            'nlp' => ['accuracy' => 89, 'samples_processed' => 1247, 'avg_score' => 78.5, 'improvement_rate' => 12.3],
            'rl' => ['accuracy' => 87, 'decisions_made' => 892, 'avg_reward' => 0.84, 'learning_rate' => 0.95],
            'cbf' => ['accuracy' => 92, 'recommendations' => 567, 'click_through_rate' => 34.2, 'user_satisfaction' => 4.6]
        ];
        $sampleRecommendations = [
            ['type' => 'NLP', 'title' => 'Tingkatkan Kualitas Essay Anda', 'description' => 'Berdasarkan analisis teks terakhir, fokus pada struktur kalimat dan variasi kata.', 'action' => 'Pelajari Teknik Penulisan', 'confidence' => 89],
            ['type' => 'RL', 'title' => 'Waktu Belajar Optimal', 'description' => 'Performa terbaik Anda di pagi hari (08:00-10:00). Atur jadwal belajar di waktu ini.', 'action' => 'Atur Reminder', 'confidence' => 87],
            ['type' => 'CBF', 'title' => 'Materi Rekomendasi', 'description' => 'Siswa dengan profil serupa sukses dengan video "Matematika Visual".', 'action' => 'Tonton Video', 'confidence' => 92]
        ];

        $this->render('siswa/ai-recommendations', [
            'title' => 'AI Recommendations',
            'user' => $user,
            'aiMetrics' => $aiMetrics,
            'sampleRecommendations' => $sampleRecommendations,
        ]);
    }
}
