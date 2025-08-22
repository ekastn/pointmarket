<?php

namespace App\Controllers;

use App\Core\ApiClient;

class SettingsController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function index(): void
    {
        $role = $_SESSION['user_data']['role'] ?? null;

        if ($role === 'admin') {
            $res = $this->apiClient->request('GET', '/api/v1/scorings/multimodal');
            $this->render('admin/settings', [
                'multimodal_threshold' => $res['data']['threshold']
            ]);
        } else {
            $this->render('errors/404');
        }
    }

    public function updateMultimodalThreshold(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $threshold = $input['threshold'] ?? null;

        if ($threshold !== null) {
            $res = $this->apiClient->request('POST', '/api/v1/scorings/multimodal', ['json' => ['threshold' => $threshold]]);

            if ($res['success']) {
                echo json_encode(['success' => true, 'message' => 'Multimodal threshold updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update multimodal threshold']);
            }
        }
    }
}

