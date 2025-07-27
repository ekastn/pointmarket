<?php

namespace App\Controllers;

use App\Services\ApiClient;

class QuizController extends BaseController
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

        $quizzes = [];
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $quizzesResponse = $this->apiClient->getQuizzes();

        if ($quizzesResponse['success']) {
            $quizzes = $quizzesResponse['data']['quizzes'] ?? [];
        } else {
            $_SESSION['messages'] = ['error' => $quizzesResponse['error'] ?? 'Failed to fetch quizzes.'];
        }

        $this->render('quizzes', [
            'title' => 'Quizzes',
            'user' => $user,
            'quizzes' => $quizzes,
            'messages' => $messages,
        ]);
    }
}
