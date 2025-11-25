<?php

namespace App\Services;

use App\Core\ApiClient;

class StudentService
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function listPrograms(): ?array
    {
        $resp = $this->apiClient->request('GET', '/api/v1/programs');
        if ($resp['success']) {
            return $resp['data'];
        }
        return null;
    }

    public function search(array $params): ?array
    {
        $query = [];
        foreach (['search','status','page','limit'] as $k) {
            if (isset($params[$k]) && $params[$k] !== '') $query[$k] = $params[$k];
        }
        foreach (['program_id','cohort_year'] as $k) {
            if (!empty($params[$k])) $query[$k] = $params[$k];
        }
        $resp = $this->apiClient->request('GET', '/api/v1/students', ['query' => $query]);
        if ($resp['success']) {
            return $resp;
        }
        return null;
    }

    public function getByUserID(int $userId): ?array
    {
        $resp = $this->apiClient->request('GET', '/api/v1/students/'.$userId);
        if ($resp['success']) return $resp['data'];
        return null;
    }

    public function upsert(int $userId, array $payload): bool
    {
        $resp = $this->apiClient->request('PUT', '/api/v1/students/'.$userId, ['json' => $payload]);
        return $resp['success'] ?? false;
    }

    public function getStudentDetails(int $userId): ?array
    {
        $resp = $this->apiClient->request('GET', '/api/v1/students/' . $userId . '/details');
        error_log(print_r($resp, true));
        if ($resp['success']) {
            return $resp['data'];
        }
        return null;
    }
}

