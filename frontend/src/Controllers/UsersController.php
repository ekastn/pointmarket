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
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10; 

        $usersData = $this->userService->getAllUsers($search, $role);
        $rolesData = $this->userService->getRoles();

        if ($usersData !== null && $rolesData !== null) {
            $users = $usersData;
            $roles = $rolesData;

            // Manual pagination logic for the view
            $total_data = count($users);
            $total_pages = ceil($total_data / $limit);
            $offset = ($page - 1) * $limit;
            $users_paginated = array_slice($users, $offset, $limit);

            $start = $offset + 1;
            $end = min($offset + $limit, $total_data);

            $this->render('admin/users', [
                'user' => $_SESSION['user_data'],
                'title' => 'User',
                'users' => $users_paginated,
                'roles' => $roles,
                'search' => $search,
                'role' => $role,
                'page' => $page,
                'limit' => $limit,
                'total_data' => $total_data,
                'total_pages' => $total_pages,
                'start' => $start,
                'end' => $end,
            ]);
        } else {
            $_SESSION['messages']['error'] = 'Failed to fetch users or roles.';
            $this->redirect('/dashboard');
        }
    }

    public function saveUser(): void
    {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $roleId = $_POST['role_id'] ?? '';

        if (empty($username) || empty($email) || empty($roleId)) {
            $_SESSION['messages']['error'] = 'All fields are required.';
            $this->redirect('/admin/users');
            return;
        }

        $userData = [
            'username' => $username,
            'email' => $email,
            'role_id' => (int)$roleId,
        ];

        $result = $this->userService->createUser($userData);

        if ($result !== null) {
            $_SESSION['messages']['success'] = 'User created successfully!';
        } else {
            $_SESSION['messages']['error'] = 'Failed to create user.';
        }
        $this->redirect('/admin/users');
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
