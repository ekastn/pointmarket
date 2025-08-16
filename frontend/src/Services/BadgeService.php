<?php

namespace App\Services;

use App\Core\ApiClient;

class BadgeService
{
    protected ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getAllBadges(string $search = '', int $page = 1, int $limit = 10): ?array
    {
        $queryParams = [
            'page' => $page,
            'limit' => $limit,
        ];
        if (!empty($search)) {
            $queryParams['search'] = $search;
        }

        $response = $this->apiClient->request('GET', '/api/v1/badges', ['query' => $queryParams]);

        if ($response['success']) {
            return $response;
        }

        return null;
    }

    public function getBadgeById(int $id): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/badges/' . $id);

        if ($response['success']) {
            return $response['data'];
        }

        return null;
    }

    public function createBadge(array $badgeData): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/badges', ['json' => $badgeData]);

        if ($response['success']) {
            return $response['data'];
        } else {
            $_SESSION['api_error_message'] = $response['message'] ?? 'Failed to create badge.';
        }

        return null;
    }

    public function updateBadge(int $id, array $badgeData): ?array
    {
        $response = $this->apiClient->request('PUT', '/api/v1/badges/' . $id, ['json' => $badgeData]);

        if ($response['success']) {
            return $response['data'];
        } else {
            $_SESSION['api_error_message'] = $response['message'] ?? 'Failed to update badge.';
        }

        return null;
    }

    public function deleteBadge(int $id): ?bool
    {
        $response = $this->apiClient->request('DELETE', '/api/v1/badges/' . $id);

        if ($response['success']) {
            return true;
        } else {
            $_SESSION['api_error_message'] = $response['message'] ?? 'Failed to delete badge.';
        }

        return false;
    }

    public function awardBadge(int $badgeId, int $userId): ?bool
    {
        $response = $this->apiClient->request('POST', '/api/v1/badges/' . $badgeId . '/award', ['json' => ['user_id' => $userId, 'badge_id' => $badgeId]]);

        if ($response['success']) {
            return true;
        } else {
            $_SESSION['api_error_message'] = $response['message'] ?? 'Failed to award badge.';
        }

        return false;
    }

    public function revokeBadge(int $badgeId, int $userId): ?bool
    {
        $response = $this->apiClient->request('DELETE', '/api/v1/badges/' . $badgeId . '/revoke', ['json' => ['user_id' => $userId, 'badge_id' => $badgeId]]);

        if ($response['success']) {
            return true;
        } else {
            $_SESSION['api_error_message'] = $response['message'] ?? 'Failed to revoke badge.';
        }

        return false;
    }

    public function getMyBadges(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/my-badges');

        if ($response['success']) {
            return $response['data'];
        }

        return null;
    }
}
