<?php

namespace App\Services;

use App\Core\ApiClient;

class QuestionnaireService
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getQuestionnaire(int $id): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/questionnaires/' . $id);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function submitQuestionnaire(int $questionnaireId, array $answers, int $weekNumber, int $year): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/questionnaires/submit', [
            'json' => [
                'questionnaire_id' => $questionnaireId,
                'answers' => $answers,
                'week_number' => $weekNumber,
                'year' => $year,
            ],
        ]);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function getVARKAssessment(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/vark');
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function submitVARK(array $answers): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/vark/submit', ['json' => ['answers' => $answers]]);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function getLatestQuestionnaireResultByType(string $type): ?array
    {
        $response = $this->apiClient->request('GET', "/api/v1/questionnaires/latest-by-type?type=" . urlencode($type));
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function getLatestVARKResult(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/vark/latest');
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function getAllQuestionnaires(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/questionnaires');
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function getQuestionnaireHistory(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/questionnaires/history');
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function getQuestionnaireStats(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/questionnaires/stats');
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }
}
