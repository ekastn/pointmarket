<?php

namespace App\Services;

use App\Core\ApiClient;

class VarkCorrelationService
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function analyzeCorrelation(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/questionnaires/correlations');
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }
}
