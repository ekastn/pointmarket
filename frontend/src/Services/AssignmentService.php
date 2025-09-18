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
        $response = $this->apiClient->request('GET', $uri);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function getStudentAssignments(int $userId): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/students/' . $userId . '/assignments');
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
                'submission' => $submissionContent,
            ],
        ]);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function getSubmissions(int $assignmentId): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/assignments/' . $assignmentId . '/submissions');
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function gradeSubmission(int $assignmentId, int $studentAssignmentId, ?float $score, ?string $feedback): bool
    {
        $payload = [];
        if ($score !== null) { $payload['score'] = $score; }
        if ($feedback !== null) { $payload['feedback'] = $feedback; }
        $response = $this->apiClient->request('PUT', '/api/v1/assignments/' . $assignmentId . '/submissions/' . $studentAssignmentId, [
            'json' => $payload,
        ]);
        return $response['success'] === true;
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
