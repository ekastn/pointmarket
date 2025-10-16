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

    // Admin-only scheduler controls
    public function getSchedulerStatus(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/weekly-evaluations/status');
        return ($response['success'] ?? false) ? ($response['data'] ?? null) : null;
    }

    public function startScheduler(): bool
    {
        $response = $this->apiClient->request('POST', '/api/v1/weekly-evaluations/start');
        return $response['success'] ?? false;
    }

    public function stopScheduler(): bool
    {
        $response = $this->apiClient->request('POST', '/api/v1/weekly-evaluations/stop');
        return $response['success'] ?? false;
    }

}
