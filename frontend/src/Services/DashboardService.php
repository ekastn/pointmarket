<?php

namespace App\Services;

use App\Core\ApiClient;

class DashboardService
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Fetches all dashboard data from the backend.
     * @return array|null Returns the dashboard data array on success, or null on failure.
     */
    public function getDashboardData(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/dashboard');

        if ($response['success']) {
            return $response['data'];
        }

        return null;
    }
}
