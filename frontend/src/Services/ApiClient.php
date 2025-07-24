<?php

namespace App\Services;

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

    private function request(string $method, string $uri, array $options = []): array
    {
        if ($this->jwtToken) {
            $options['headers']['Authorization'] = 'Bearer ' . $this->jwtToken;
        }

        try {
            $response = $this->client->request($method, $uri, $options);
            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            if ($statusCode >= 200 && $statusCode < 300) {
                return ['success' => true, 'data' => $body];
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

    public function login(string $username, string $password, string $role): array
    {
        $response = $this->request('POST', '/auth/login', [
            'json' => [
                'username' => $username,
                'password' => $password,
                'role' => $role,
            ],
        ]);

        if ($response['success'] && isset($response['data']['data']['token'])) {
            $this->setJwtToken($response['data']['data']['token']);
        }
        return $response;
    }

    public function getUserProfile(): array
    {
        return $this->request('GET', '/profile');
    }

    // Add methods for other API endpoints as needed
    // Example for assignments:
    public function getAssignments(?int $teacherId = null): array
    {
        $uri = '/assignments';
        $options = [];
        if ($teacherId !== null) {
            $options['query'] = ['teacher_id' => $teacherId];
        }
        return $this->request('GET', $uri, $options);
    }

    public function createAssignment(array $data): array
    {
        return $this->request('POST', '/assignments', ['json' => $data]);
    }

    public function updateAssignment(int $id, array $data): array
    {
        return $this->request('PUT', '/assignments/' . $id, ['json' => $data]);
    }

    public function deleteAssignment(int $id): array
    {
        return $this->request('DELETE', '/assignments/' . $id);
    }

    // Example for quizzes:
    public function getQuizzes(?int $teacherId = null): array
    {
        $uri = '/quizzes';
        $options = [];
        if ($teacherId !== null) {
            $options['query'] = ['teacher_id' => $teacherId];
        }
        return $this->request('GET', $uri, $options);
    }

    public function createQuiz(array $data): array
    {
        return $this->request('POST', '/quizzes', ['json' => $data]);
    }

    public function updateQuiz(int $id, array $data): array
    {
        return $this->request('PUT', '/quizzes/' . $id, ['json' => $data]);
    }

    public function deleteQuiz(int $id): array
    {
        return $this->request('DELETE', '/quizzes/' . $id);
    }

    // Example for questionnaires:
    public function getQuestionnaire(int $id): array
    {
        return $this->request('GET', '/questionnaires/' . $id);
    }

    public function submitQuestionnaire(int $questionnaireId, array $answers, int $weekNumber, int $year): array
    {
        return $this->request('POST', '/questionnaires/submit', [
            'json' => [
                'questionnaire_id' => $questionnaireId,
                'answers' => $answers,
                'week_number' => $weekNumber,
                'year' => $year,
            ],
        ]);
    }

    // Example for VARK:
    public function getVARKAssessment(): array
    {
        return $this->request('GET', '/vark');
    }

    public function submitVARK(array $answers): array
    {
        return $this->request('POST', '/vark/submit', ['json' => ['answers' => $answers]]);
    }

    public function getLatestVARKResult(): array
    {
        return $this->request('GET', '/vark/latest');
    }

    // Example for NLP:
    public function analyzeText(string $text, string $contextType, ?int $assignmentId = null, ?int $quizId = null): array
    {
        return $this->request('POST', '/nlp/analyze', [
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
        return $this->request('GET', '/nlp/stats');
    }
}
