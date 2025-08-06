<?php

namespace App\Services;

use App\Core\ApiClient;

class WeeklyEvaluationService
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getStudentEvaluationStatus(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/evaluations/weekly/teacher/status');
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function getWeeklyEvaluationOverview(int $weeks = 4): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/evaluations/weekly/teacher/overview?weeks=' . $weeks);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function getWeeklyEvaluationProgressByStudentID(int $weeks = 8): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/evaluations/weekly/student/progress?weeks=' . $weeks);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function getPendingWeeklyEvaluations(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/evaluations/weekly/student/pending');
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }
}
