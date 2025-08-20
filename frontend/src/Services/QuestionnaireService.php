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
        $response = $this->apiClient->request('POST', '/api/v1/questionnaires', [
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

    public function submitVARK(int $questionnaireId, array $answers, string $nlpText): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/questionnaires/vark', [
            'json' => [
                'questionnaire_id' => $questionnaireId,
                'answers' => $answers,
                'text' => $nlpText,
            ],
        ]);

        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function getLatestVARKResult(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/questionnaires/vark');
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
