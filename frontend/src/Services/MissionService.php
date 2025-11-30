<?php

namespace App\Services;

use App\Core\ApiClient;

class MissionService
{
    protected ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getAllMissions(string $search = '', int $page = 1, int $limit = 10): ?array
    {
        $queryParams = [
            'page' => $page,
            'limit' => $limit,
        ];
        if (!empty($search)) {
            $queryParams['search'] = $search;
        }

        $response = $this->apiClient->request('GET', '/api/v1/missions', ['query' => $queryParams]);

        if ($response['success']) {
            return $response;
        }

        return null;
    }

    public function getMissionById(int $id): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/missions/' . $id);

        if ($response['success']) {
            return $response['data'];
        }

        return null;
    }

    public function createMission(array $missionData): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/missions', ['json' => $missionData]);

        if ($response['success']) {
            return $response['data'];
        } else {
            $_SESSION['messages'] = $response['message'] ?? 'Failed to create mission.';
        }

        return null;
    }

    public function updateMission(int $id, array $missionData): ?array
    {
        $response = $this->apiClient->request('PUT', '/api/v1/missions/' . $id, ['json' => $missionData]);

        if ($response['success']) {
            return $response['data'];
        } else {
            $_SESSION['messages'] = $response['message'] ?? 'Failed to update mission.';
        }

        return null;
    }

    public function deleteMission(int $id): ?bool
    {
        $response = $this->apiClient->request('DELETE', '/api/v1/missions/' . $id);

        if ($response['success']) {
            return true;
        } else {
            $_SESSION['messages'] = $response['message'] ?? 'Failed to delete mission.';
        }

        return false;
    }

    public function startMission(int $missionId, int $userId): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/missions/' . $missionId . '/start', ['json' => ['mission_id' => $missionId, 'user_id' => $userId]]);

        if ($response['success']) {
            return $response['data'];
        } else {
            $_SESSION['messages'] = $response['message'] ?? 'Failed to start mission.';
        }

        return null;
    }

    public function updateUserMissionStatus(int $userMissionId, array $statusData): ?array
    {
        $response = $this->apiClient->request('PUT', '/api/v1/missions/' . $userMissionId . '/status', ['json' => $statusData]);

        if ($response['success']) {
            return $response['data'];
        } else {
            $_SESSION['messages'] = $response['message'] ?? 'Failed to update user mission status.';
        }

        return null;
    }

    public function getUserMissions(int $userId): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/missions?user_id=' . $userId);

        if ($response['success']) {
            return $response;
        }

        return null;
    }

    public function getAllUserMissions(int $page = 1, int $limit = 10, string $search = ''): ?array
    {
        $queryParams = [
            'page' => $page,
            'limit' => $limit,
        ];
        if (!empty($search)) {
            $queryParams['search'] = $search;
        }

        $response = $this->apiClient->request('GET', '/api/v1/missions/progress', ['query' => $queryParams]);

        if ($response['success']) {
            return $response;
        }

        return null;
    }
}
