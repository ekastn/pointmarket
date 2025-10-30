<?php

namespace App\Services;

use App\Core\ApiClient;

class ReportsService
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getRecommendationsTrace(string $studentId): ?array
    {
        $resp = $this->apiClient->request('GET', '/api/v1/admin/recommendations/trace', [
            'query' => ['student_id' => $studentId],
        ]);
        if (!empty($resp['success']) && isset($resp['data'])) {
            return $resp['data'];
        }
        $msg = $resp['message'] ?? ($resp['error'] ?? 'Failed to fetch trace');
        $code = $resp['status'] ?? null;
        return ['__error' => $msg, '__status' => $code];
    }
}
