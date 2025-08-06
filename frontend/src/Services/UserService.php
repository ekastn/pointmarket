<?php

namespace App\Services;

use App\Core\ApiClient;

class UserService
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getAllUsers(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/users');

        if ($response['success']) {
            return $response['data'];
        }

        return null;
    }

    public function updateUserRole(int $id, string $role): ?array
    {
        $response = $this->apiClient->request('PUT', '/api/v1/users/' . $id . '/role', [
            'json' => [
                'role' => $role,
            ],
        ]);

        if ($response['success']) {
            return $response['data'];
        }

        return null;
    }

    public function deleteUser(int $id): ?array
    {
        $response = $this->apiClient->request('DELETE', '/api/v1/users/' . $id);

        if ($response['success']) {
            return $response['data'];
        }

        return null;
    }
}
