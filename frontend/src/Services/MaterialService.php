<?php

namespace App\Services;

use App\Core\ApiClient;

class MaterialService
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getAllMaterials(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/materials');
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function getMaterialByID(int $id): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/materials/' . $id);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function createMaterial(array $data): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/materials', ['json' => $data]);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function updateMaterial(int $id, array $data): ?array
    {
        $response = $this->apiClient->request('PUT', '/api/v1/materials/' . $id, ['json' => $data]);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function deleteMaterial(int $id): ?array
    {
        $response = $this->apiClient->request('DELETE', '/api/v1/materials/' . $id);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }
}
