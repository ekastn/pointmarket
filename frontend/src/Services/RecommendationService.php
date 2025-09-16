<?php

namespace App\Services;

use App\Core\ApiClient;

/**
 * RecommendationService
 * Dedicated service to fetch student learning recommendations from the backend.
 * Uses the pure API route: /api/v1/students/:user_id/recommendations
 */
class RecommendationService
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Get recommendations for a student by user ID.
     * Returns an array of recommendation items or null on failure.
     */
    public function getStudentRecommendations(int $userId): ?array
    {
        $resp = $this->apiClient->request('GET', '/api/v1/students/' . $userId . '/recommendations');
        error_log(print_r($resp, true));
        if (!empty($resp['success']) && isset($resp['data'])) {
            return $resp['data'];
        }
        return null;
    }
}
