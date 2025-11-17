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

    public function submitQuestionnaire(int $questionnaireId, array $answers, int $weekNumber, int $year, int | null $weeklyEvaluationId): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/questionnaires', [
            'json' => [
                'questionnaire_id' => $questionnaireId,
                'answers' => $answers,
                'week_number' => $weekNumber,
                'year' => $year,
                'weekly_evaluation_id' => $weeklyEvaluationId
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
            $data = $response['data'] ?? null;
            if (is_array($data)) {
                // Normalize to legacy keys expected by the view
                $style = $data['style'] ?? [];
                $label = $style['label'] ?? null; // dominant style label
                $type  = $style['type'] ?? null;  // learning preference type (dominant|multimodal)
                $scores = $style['scores'] ?? [];
                return [
                    'dominant_style' => $label,
                    'learning_preference' => $type,
                    'scores' => $scores,
                    'completed_at' => $data['completed_at'] ?? null,
                ];
            }
            return $data;
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
            // Backend returns paginated shape; the view expects a flat list
            $items = $response['data'] ?? [];
            if (is_array($items)) {
                return $items;
            }
            return [];
        }
        return null;
    }

    // Fetch VARK history specifically with paging options (default large enough window)
    public function getVarkHistory(int $limit = 200, int $offset = 0): ?array
    {
        if ($limit <= 0) { $limit = 50; }
        if ($limit > 500) { $limit = 500; }
        if ($offset < 0) { $offset = 0; }
        $query = http_build_query(['type' => 'VARK', 'limit' => $limit, 'offset' => $offset]);
        $response = $this->apiClient->request('GET', '/api/v1/questionnaires/history?'.$query);
        if ($response['success']) {
            $items = $response['data'] ?? [];
            if (is_array($items)) { return $items; }
            return [];
        }
        return null;
    }

    public function getQuestionnaireStats(): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/questionnaires/stats');
        if ($response['success']) {
            $data = $response['data'] ?? [];
            // Normalize to flat array for the view (MSLQ, AMS, and VARK pseudo entry)
            $flat = [];
            if (isset($data['likert']) && is_array($data['likert'])) {
                foreach ($data['likert'] as $row) {
                    if (!is_array($row)) continue;
                    $flat[] = [
                        'type' => $row['type'] ?? '',
                        'total_completed' => $row['total_completed'] ?? 0,
                        'average_score' => $row['average_score'] ?? null,
                        'best_score' => $row['best_score'] ?? null,
                        'lowest_score' => $row['lowest_score'] ?? null,
                        'last_completed' => $row['last_completed'] ?? null,
                    ];
                }
            }
            if (isset($data['vark']) && is_array($data['vark'])) {
                $flat[] = [
                    'type' => 'vark',
                    'total_completed' => $data['vark']['total_completed'] ?? 0,
                    'average_score' => null,
                    'best_score' => null,
                    'lowest_score' => null,
                    'last_completed' => $data['vark']['last_completed'] ?? null,
                ];
            }
            return $flat;
        }
        return null;
    }

    public function createQuestionnaire(array $data): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/questionnaires', ['json' => $data]);
        if ($response['success']) {
            return $response['data'];
        }
        return null;
    }

    public function updateQuestionnaire(int $id, array $data): ?array
    {
        $response = $this->apiClient->request('PUT', '/api/v1/questionnaires/' . $id, ['json' => $data]);
        if ($response['success']) {
            return $response;
        }
        return null;
    }

    public function deleteQuestionnaire(int $id): ?array
    {
        $response = $this->apiClient->request('DELETE', '/api/v1/questionnaires/' . $id);
        if ($response['success']) {
            return $response;
        }
        return null;
    }
}
