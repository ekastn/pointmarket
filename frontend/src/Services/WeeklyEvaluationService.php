<?php

namespace App\Services;

use App\Core\ApiClient;

class WeeklyEvaluationService
{
    protected ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getWeeklyEvaluations(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/weekly-evaluations');

        if ($response['success']) {
            return $response['data'];
        }

        return null;
    }

    public function getTeacherDashboard(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/weekly-evaluations?view=monitoring');

        if ($response['success']) {
            return $response['data'];
        }

        return null;
    }

    public function initializeWeeklyEvaluations(): ?bool
    {
        $response = $this->apiClient->request('POST', '/api/v1/weekly-evaluations/initialize');

        return $response['success'] ?? false;
    }
}
