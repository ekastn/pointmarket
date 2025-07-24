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
