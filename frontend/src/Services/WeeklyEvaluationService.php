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

    public function getWeeklyEvaluations(int $weeks = 8): ?array
    {
        $query = ($weeks > 0) ? ('?weeks=' . $weeks) : '';
        $response = $this->apiClient->request('GET', '/api/v1/weekly-evaluations' . $query);

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

    // Teacher/Admin: get a specific student's weekly evaluations
    public function getStudentWeeklyEvaluations(int $studentId, int $weeks = 520): ?array
    {
        $weeks = $weeks > 0 ? $weeks : 520;
        $response = $this->apiClient->request('GET', '/api/v1/weekly-evaluations?student_id=' . $studentId . '&weeks=' . $weeks);
        if ($response['success'] ?? false) {
            return $response['data'] ?? null;
        }
        return null;
    }
}
