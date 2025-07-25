<?php

namespace App\Controllers;

use App\Services\ApiClient;

class UsersController
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function index(): void
    {
        session_start();
        if (!isset($_SESSION['jwt_token'])) {
            header('Location: /login');
            exit();
        }

        $this->apiClient->setJwtToken($_SESSION['jwt_token']);
        $response = $this->apiClient->getAllUsers();

        if (!$response['success']) {
            // Handle error, e.g., redirect to error page or show message
            echo "Error fetching users: " . ($response['error'] ?? 'Unknown error');
            return;
        }

        $users = $response['data'];

        require_once __DIR__ . '/../Views/users.php';
    }

    public function updateUserRole(): void
    {
        session_start();
        if (!isset($_SESSION['jwt_token'])) {
            header('Location: /login');
            exit();
        }

        $this->apiClient->setJwtToken($_SESSION['jwt_token']);

        $input = json_decode(file_get_contents('php://input'), true);
        $userId = $input['id'] ?? null;
        $role = $input['role'] ?? null;

        if (!$userId || !$role) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            return;
        }

        $response = $this->apiClient->updateUserRole($userId, $role);

        echo json_encode($response);
    }

    public function deleteUser(): void
    {
        session_start();
        if (!isset($_SESSION['jwt_token'])) {
            header('Location: /login');
            exit();
        }

        $this->apiClient->setJwtToken($_SESSION['jwt_token']);

        $input = json_decode(file_get_contents('php://input'), true);
        $userId = $input['id'] ?? null;

        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            return;
        }

        $response = $this->apiClient->deleteUser($userId);

        echo json_encode($response);
    }
}
