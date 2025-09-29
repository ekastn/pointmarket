<?php

namespace App\Services;

use App\Core\ApiClient;

class AnalyticsService
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getTeacherCourseInsights(int $limit = 10): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/teachers/course-insights', [
            'query' => ['limit' => $limit]
        ]);
        if (($response['success'] ?? false) === true) {
            return $response['data'];
        }
        return null;
    }
}

