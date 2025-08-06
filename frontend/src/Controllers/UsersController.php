<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\UserService;

class UsersController extends BaseController
{
    protected UserService $userService;

    public function __construct(ApiClient $apiClient, UserService $userService)
    {
        parent::__construct($apiClient);
        $this->userService = $userService;
    }

    public function index(): void
    {
        $usersData = $this->userService->getAllUsers();

        if ($usersData !== null) {
            $users = $usersData;
            $this->render('admin/users', ['users' => $users]);
        } else {
            $_SESSION['messages']['error'] = 'Failed to fetch users.';
            $this->redirect('/dashboard'); 
        }
    }

    public function updateUserRole(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $role = $input['role'] ?? null;

        if (! $id || ! $role) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);

            return;
        }

        $result = $this->userService->updateUserRole($id, $role);

        if ($result !== null) {
            echo json_encode(['success' => true, 'message' => 'User role updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update user role']);
        }
    }

    public function deleteUser(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $userId = $input['id'] ?? null;

        if (! $userId) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);

            return;
        }

        $result = $this->userService->deleteUser($userId);

        if ($result !== null) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
        }
    }
}
