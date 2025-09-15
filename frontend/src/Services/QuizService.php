<?php

namespace App\Services;

use App\Core\ApiClient;

class QuizService
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getQuizzes(?int $teacherId = null): ?array
    {
        $uri = '/api/v1/quizzes';
        $options = [];
        $response = $this->apiClient->request('GET', $uri, $options);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function getQuizById(int $id): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/quizzes/' . $id);
        return $response['success'] ? $response['data'] : null;
    }

    public function getQuestions(int $quizId): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/quizzes/' . $quizId . '/questions');
        return $response['success'] ? $response['data'] : null;
    }

    public function createQuestion(int $quizId, array $data): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/quizzes/' . $quizId . '/questions', ['json' => $data]);
        return $response['success'] ? $response['data'] : null;
    }

    public function updateQuestion(int $quizId, int $questionId, array $data): ?array
    {
        $response = $this->apiClient->request('PUT', '/api/v1/quizzes/' . $quizId . '/questions/' . $questionId, ['json' => $data]);
        return $response['success'] ? $response['data'] : null;
    }

    public function deleteQuestion(int $quizId, int $questionId): bool
    {
        $response = $this->apiClient->request('DELETE', '/api/v1/quizzes/' . $quizId . '/questions/' . $questionId);
        return $response['success'] ?? false;
    }

    public function startQuiz(int $quizId): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/quizzes/' . $quizId . '/start');
        return $response['success'] ? $response['data'] : null;
    }

    public function submitQuiz(int $quizId, array $data = []): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/quizzes/' . $quizId . '/submit', ['json' => $data]);
        return $response['success'] ? $response['data'] : null;
    }

    public function createQuiz(array $data): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/quizzes', ['json' => $data]);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function updateQuiz(int $id, array $data): ?array
    {
        $response = $this->apiClient->request('PUT', '/api/v1/quizzes/' . $id, ['json' => $data]);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function deleteQuiz(int $id): ?array
    {
        $response = $this->apiClient->request('DELETE', '/api/v1/quizzes/' . $id);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }
}
