<?php

namespace App\Services;

use App\Core\ApiClient;

class ProfileService
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getUserProfile(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/profile');
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function updateProfile(array $data): bool
    {
        $response = $this->apiClient->request('PUT', '/api/v1/profile', ['json' => $data]);
        return isset($response['success']) && $response['success'] === true;
    }

    public function getAssignmentStatsByStudentID(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/dashboard/student/assignments/stats');
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function getNLPStats(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/nlp/stats');
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function getWeeklyEvaluationProgressByStudentID(int $weeks = 8): ?array
    {
        // Updated to the existing backend endpoint
        $response = $this->apiClient->request('GET', '/api/v1/weekly-evaluations?weeks=' . $weeks);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function getRecentActivityByUserID(int $limit = 10): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/dashboard/student/activity?limit=' . $limit);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function changePassword(array $data): bool
    {
        $response = $this->apiClient->request('PUT', '/api/v1/profile/password', [
            'json' => [
                'current_password' => $data['current_password'] ?? '',
                'new_password' => $data['new_password'] ?? '',
                'confirm_password' => $data['confirm_password'] ?? '',
            ],
        ]);
        return isset($response['success']) && $response['success'] === true;
    }

    public function uploadAvatar(array $file): ?string
    {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            error_log('ProfileService: tmp file missing or not an uploaded file: ' . ($file['tmp_name'] ?? 'null'));
            return null;
        }
        $tmp = $file['tmp_name'];
        $filename = $file['name'] ?? 'avatar';
        $mime = function_exists('mime_content_type') ? mime_content_type($tmp) : ($file['type'] ?? 'application/octet-stream');
        $fh = fopen($tmp, 'r');
        if ($fh === false) {
            error_log('ProfileService: fopen failed for tmp file: ' . $tmp);
            return null;
        }
        $multipart = [
            [
                'name' => 'file',
                'contents' => $fh,
                'filename' => $filename,
                'headers' => [
                    'Content-Type' => $mime,
                ],
            ],
        ];
        $resp = $this->apiClient->requestMultipart('PATCH', '/api/v1/profile/avatar', $multipart);
        if (is_resource($fh) && get_resource_type($fh) === 'stream') {
            @fclose($fh);
        }
        if (!empty($resp['success']) && isset($resp['data']['avatar_url'])) {
            return $resp['data']['avatar_url'];
        }
        error_log('ProfileService: upload avatar API response indicates failure or missing avatar_url');
        return null;
    }
}
