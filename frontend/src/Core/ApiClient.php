<?php

namespace App\Core;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ApiClient
{
    private Client $client;
    private string $baseUrl;
    private ?string $jwtToken = null;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 5.0,
        ]);
    }

    public function setJwtToken(?string $token): void
    {
        $this->jwtToken = $token;
    }

    public function getJwtToken(): ?string
    {
        return $this->jwtToken;
    }

    public function request(string $method, string $uri, array $options = []): array
    {
        if ($this->jwtToken) {
            $options['headers']['Authorization'] = 'Bearer ' . $this->jwtToken;
        }

        try {
            $response = $this->client->request($method, $uri, $options);
            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            if ($statusCode >= 200 && $statusCode < 300) {
                return ['success' => true, 'data' => $body['data']];
            } else {
                return ['success' => false, 'error' => $body['message'] ?? 'Unknown API error', 'status' => $statusCode];
            }
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;
            $errorMessage = $e->getMessage();
            if ($e->hasResponse()) {
                $responseBody = json_decode($e->getResponse()->getBody()->getContents(), true);
                $errorMessage = $responseBody['message'] ?? $errorMessage;
            }
            return ['success' => false, 'error' => $errorMessage, 'status' => $statusCode];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Network error: ' . $e->getMessage(), 'status' => 500];
        }
    }

    public function login(string $username, string $password): array
    {
        $response = $this->request('POST', '/api/v1/auth/login', [
            'json' => [
                'username' => $username,
                'password' => $password,
            ],
        ]);

        if ($response['success'] && isset($response['data']['token'])) {
            $this->setJwtToken($response['data']['token']);
        }
        return $response;
    }

    public function getUserProfile(): array
    {
        return $this->request('GET', '/api/v1/profile');
    }

    public function getAssignments(?int $teacherId = null, ?int $studentId = null): array
    {
        $uri = '/api/v1/assignments';
        $options = [];
        if ($teacherId !== null) {
            $options['query']['teacher_id'] = $teacherId;
        }
        if ($studentId !== null) {
            $options['query']['student_id'] = $studentId;
        }
        return $this->request('GET', $uri, $options);
    }

    public function getAssignmentByID(int $id, ?int $studentId = null): array
    {
        $uri = '/api/v1/assignments/' . $id;
        $options = [];
        if ($studentId !== null) {
            $options['query']['student_id'] = $studentId;
        }
        return $this->request('GET', $uri, $options);
    }

    public function startAssignment(int $assignmentId): array
    {
        return $this->request('POST', '/api/v1/assignments/' . $assignmentId . '/start');
    }

    public function submitAssignment(int $assignmentId, string $submissionContent): array
    {
        $response = $this->request('POST', '/api/v1/assignments/' . $assignmentId . '/submit', [
            'json' => [
                'submission_content' => $submissionContent,
            ],
        ]);

        // Adjust to handle backend response for score
        if ($response['success'] && isset($response['data']['total_score'])) {
            $response['score'] = $response['data']['total_score'];
        }
        return $response;
    }

    public function createAssignment(array $data): array
    {
        return $this->request('POST', '/api/v1/assignments', ['json' => $data]);
    }

    public function updateAssignment(int $id, array $data): array
    {
        return $this->request('PUT', '/api/v1/assignments/' . $id, ['json' => $data]);
    }

    public function deleteAssignment(int $id): array
    {
        return $this->request('DELETE', '/api/v1/assignments/' . $id);
    }

    public function getQuizzes(?int $teacherId = null): array
    {
        $uri = '/api/v1/quizzes';
        $options = [];
        if ($teacherId !== null) {
            $options['query'] = ['teacher_id' => $teacherId];
        }
        return $this->request('GET', $uri, $options);
    }

    public function createQuiz(array $data): array
    {
        return $this->request('POST', '/api/v1/quizzes', ['json' => $data]);
    }

    public function updateQuiz(int $id, array $data): array
    {
        return $this->request('PUT', '/api/v1/quizzes/' . $id, ['json' => $data]);
    }

    public function deleteQuiz(int $id): array
    {
        return $this->request('DELETE', '/api/v1/quizzes/' . $id);
    }

    public function analyzeText(string $text, string $contextType, ?int $assignmentId = null, ?int $quizId = null): array
    {
        return $this->request('POST', '/api/v1/nlp/analyze', [
            'json' => [
                'text' => $text,
                'context_type' => $contextType,
                'assignment_id' => $assignmentId,
                'quiz_id' => $quizId,
            ],
        ]);
    }

    public function getNLPStats(): array
    {
        return $this->request('GET', '/api/v1/nlp/stats');
    }

    public function getStudentEvaluationStatus(): array
    {
        return $this->request('GET', '/api/v1/evaluations/weekly/teacher/status');
    }

    public function getWeeklyEvaluationOverview(int $weeks = 4): array
    {
        return $this->request('GET', '/api/v1/evaluations/weekly/teacher/overview?weeks=' . $weeks);
    }

    public function getStudentDashboardStats(): array
    {
        return $this->request('GET', '/api/v1/dashboard/student/stats');
    }

    public function getAdminDashboardCounts(): array
    {
        return $this->request('GET', '/api/v1/dashboard/admin/counts');
    }

    public function getTeacherDashboardCounts(): array
    {
        return $this->request('GET', '/api/v1/dashboard/teacher/counts');
    }

    public function getAllMaterials(): array
    {
        return $this->request('GET', '/api/v1/materials');
    }

    public function getMaterialByID(int $id): array
    {
        return $this->request('GET', '/api/v1/materials/' . $id);
    }

    public function createMaterial(array $data): array
    {
        return $this->request('POST', '/api/v1/materials', ['json' => $data]);
    }

    public function updateMaterial(int $id, array $data): array
    {
        return $this->request('PUT', '/api/v1/materials/' . $id, ['json' => $data]);
    }

    public function deleteMaterial(int $id): array
    {
        return $this->request('DELETE', '/api/v1/materials/' . $id);
    }

    public function getAssignmentStatsByStudentID(): array
    {
        return $this->request('GET', '/api/v1/dashboard/student/assignments/stats');
    }

    public function getRecentActivityByUserID(int $limit = 10): array
    {
        return $this->request('GET', '/api/v1/dashboard/student/activity?limit=' . $limit);
    }

    public function getWeeklyEvaluationProgressByStudentID(int $weeks = 8): array
    {
        return $this->request('GET', '/api/v1/evaluations/weekly/student/progress?weeks=' . $weeks);
    }

    public function getPendingWeeklyEvaluations(): array
    {
        return $this->request('GET', '/api/v1/evaluations/weekly/student/pending');
    }

    public function analyzeCorrelation(): array
    {
        return $this->request('GET', '/api/v1/correlation/analyze');
    }

    public function updateProfile(array $data): array
    {
        return $this->request('PUT', '/api/v1/profile', ['json' => $data]);
    }
}