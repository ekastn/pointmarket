<?php

namespace App\Services;

use App\Core\ApiClient;

class AdminItemsService
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function list(array $filters = []): ?array
    {
        $resp = $this->apiClient->request('GET', '/api/v1/admin/recommendations/items', [ 'query' => $filters ]);
        if (!empty($resp['success'])) {
            return $resp['data'] ?? ['items' => [], 'meta' => ['total' => 0, 'limit' => 20, 'offset' => 0]];
        }
        return null;
    }

    public function create(array $item): array
    {
        return $this->apiClient->request('POST', '/api/v1/admin/recommendations/items', [ 'json' => $item ]);
    }

    public function update(int $id, array $item): array
    {
        // Use PUT with JSON
        return $this->apiClient->request('PUT', '/api/v1/admin/recommendations/items/'.$id, [ 'json' => $item ]);
    }

    public function toggle(int $id, bool $isActive): array
    {
        return $this->apiClient->request('PATCH', '/api/v1/admin/recommendations/items/'.$id.'/toggle', [ 'json' => ['is_active' => $isActive] ]);
    }

    public function delete(int $id, bool $force = false): array
    {
        $uri = '/api/v1/admin/recommendations/items/'.$id . ($force ? '?force=1' : '');
        return $this->apiClient->request('DELETE', $uri);
    }

    public function searchStates(string $q, int $limit = 10): array
    {
        return $this->apiClient->request('GET', '/api/v1/admin/recommendations/states', ['query' => ['q' => $q, 'limit' => $limit]]);
    }

    public function searchRefs(string $refType, string $q, int $limit = 10): array
    {
        return $this->apiClient->request('GET', '/api/v1/admin/recommendations/refs', ['query' => ['ref_type' => $refType, 'q' => $q, 'limit' => $limit]]);
    }
}
