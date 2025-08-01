<?php

namespace App\Controllers;

use App\Core\ApiClient;

class AIRecommendationsController extends BaseController
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