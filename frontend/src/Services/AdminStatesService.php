<?php

namespace App\Services;

use App\Core\ApiClient;

class AdminStatesService
{
    public function __construct(private ApiClient $apiClient) {}

    public function list(array $filters = []): ?array
    {
        $resp = $this->apiClient->request('GET', '/api/v1/admin/recommendations/unique-states', ['query' => $filters]);
        if (!empty($resp['success'])) {
            return $resp['data'] ?? ['states' => [], 'meta' => ['total' => 0, 'limit' => 20, 'offset' => 0]];
        }
        return null;
    }

    public function create(array $payload): array
    {
        return $this->apiClient->request('POST', '/api/v1/admin/recommendations/unique-states', ['json' => $payload]);
    }

    public function update(int $id, array $payload): array
    {
        return $this->apiClient->request('PUT', '/api/v1/admin/recommendations/unique-states/'.$id, ['json' => $payload]);
    }

    public function delete(int $id, bool $force=false): array
    {
        $uri = '/api/v1/admin/recommendations/unique-states/'.$id.($force ? '?force=1' : '');
        return $this->apiClient->request('DELETE', $uri);
    }
}

