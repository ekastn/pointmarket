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
        if ($teacherId !== null) {
            $options['query']['teacher_id'] = $teacherId;
        }
        $response = $this->apiClient->request('GET', $uri, $options);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
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
