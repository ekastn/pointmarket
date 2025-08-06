<?php

namespace App\Services;

use App\Core\ApiClient;

class AssignmentService
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getAssignments(?int $teacherId = null, ?int $studentId = null): ?array
    {
        $uri = '/api/v1/assignments';
        $options = [];
        if ($teacherId !== null) {
            $options['query']['teacher_id'] = $teacherId;
        }
        if ($studentId !== null) {
            $options['query']['student_id'] = $studentId;
        }
        $response = $this->apiClient->request('GET', $uri, $options);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function getAssignmentByID(int $id, ?int $studentId = null): ?array
    {
        $uri = '/api/v1/assignments/' . $id;
        $options = [];
        if ($studentId !== null) {
            $options['query']['student_id'] = $studentId;
        }
        $response = $this->apiClient->request('GET', $uri, $options);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function startAssignment(int $assignmentId): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/assignments/' . $assignmentId . '/start');
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function submitAssignment(int $assignmentId, string $submissionContent): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/assignments/' . $assignmentId . '/submit', [
            'json' => [
                'submission_content' => $submissionContent,
            ],
        ]);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function createAssignment(array $data): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/assignments', ['json' => $data]);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function updateAssignment(int $id, array $data): ?array
    {
        $response = $this->apiClient->request('PUT', '/api/v1/assignments/' . $id, ['json' => $data]);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function deleteAssignment(int $id): ?array
    {
        $response = $this->apiClient->request('DELETE', '/api/v1/assignments/' . $id);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }
}
