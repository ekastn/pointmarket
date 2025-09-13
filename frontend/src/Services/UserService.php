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

    public function getAllUsers(string $search = '', string $role = '', int $page = 1, int $limit = 10): ?array
    {
        $queryParams = [
            'page' => $page,
            'limit' => $limit,
        ];
        if (!empty($search)) {
            $queryParams['search'] = $search;
        }
        if (!empty($role)) {
            $queryParams['role'] = $role;
        }

        $response = $this->apiClient->request('GET', '/api/v1/users', ['query' => $queryParams]);

        if ($response['success']) {
            return $response; 
        }

        return null;
    }

    public function getRoles(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/roles');

        if ($response['success']) {
            return $response['data'];
        }

        return null;
    }

    public function createUser(array $userData): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/users', [
            'json' => $userData,
        ]);

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

    public function updateUser(int $id, array $userData): void
    {
        $response = $this->apiClient->request('PUT', '/api/v1/users/' . $id, [
            'json' => $userData,
        ]);

        if (!$response['success']) {
            throw new \Exception($response['message']);
        }
    }

    public function getUserStats(int $id): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/users/' . $id . '/stats');
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function adjustUserStats(int $id, array $payload): ?array
    {
        // Expecting payload: [ 'delta' => int, 'reason' => ?string, 'reference_type' => ?string, 'reference_id' => ?int ]
        $response = $this->apiClient->request('POST', '/api/v1/users/' . $id . '/stats', [
            'json' => $payload,
        ]);
        if ($response['success']) {
            return $response['data'];
        }
        $_SESSION['api_error_message'] = $response['message'] ?? 'Failed to adjust user stats.';
        return null;
    }
}
