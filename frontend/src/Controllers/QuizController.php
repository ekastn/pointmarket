<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\QuizService;

class QuizController extends BaseController
{
    protected QuizService $quizService;

    public function __construct(ApiClient $apiClient, QuizService $quizService)
    {
        parent::__construct($apiClient);
        $this->quizService = $quizService;
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

        $quizzesData = $this->quizService->getQuizzes();

        if ($quizzesData !== null) {
            $quizzes = $quizzesData['quizzes'] ?? [];
        } else {
            $_SESSION['messages'] = ['error' => 'Failed to fetch quizzes.'];
        }

        $this->render('siswa/quizzes', [
            'title' => 'Quizzes',
            'user' => $user,
            'quizzes' => $quizzes,
            'messages' => $messages,
        ]);
    }

    public function show(int $id): void
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

        $quizData = $this->quizService->getQuizById($id) ?? [];
        $quiz = $quizData ?: [];
        $questions = $this->quizService->getQuestions($id) ?? [];

        $this->render('siswa/quiz_detail', [
            'title' => 'Detail Kuis',
            'user' => $user,
            'quiz' => $quiz,
            'questions' => $questions,
        ]);
    }
}
