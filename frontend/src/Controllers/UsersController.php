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

        // Fetch paginated data from the service
        $response = $this->userService->getAllUsers($search, $role, $page, $limit);
        $rolesData = $this->userService->getRoles();

        if ($response !== null && $rolesData !== null) {
            $users = $response['data'];
            $meta = $response['meta'];

            $total_data = $meta['total_records'];
            $start = ($page - 1) * $limit + 1;
            $end = min($start + $limit - 1, $total_data);

            $this->render('admin/users', [
                'user' => $_SESSION['user_data'],
                'title' => 'User',
                'users' => $users,
                'roles' => $rolesData,
                'search' => $search,
                'role' => $role,
                'page' => $meta['page'],
                'limit' => $meta['limit'],
                'total_data' => $total_data,
                'total_pages' => $meta['total_pages'],
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
        $name = $_POST['name'] ?? '';
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';

        if (empty($name) || empty($username) || empty($email) || empty($password) || empty($role)) {
            $_SESSION['messages']['error'] = 'All fields are required.';
            $this->redirect('/users');
            return;
        }

        $userData = [
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => $role,
        ];

        $result = $this->userService->createUser($userData);

        if ($result !== null) {
            $_SESSION['messages']['success'] = 'User created successfully!';
        } else {
            // Assuming the API might return a specific error message
            $errorMessage = $_SESSION['api_error_message'] ?? 'Failed to create user.';
            $_SESSION['messages']['error'] = $errorMessage;
        }
        unset($_SESSION['api_error_message']); // Clear the specific error message
        $this->redirect('/users');
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

    public function deleteUser(int $id): void
    {
        if (! $id) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);

            return;
        }

        $result = $this->userService->deleteUser($id);

        if ($result !== null) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
        }
    }

    public function updateUser(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $name = $input['name'] ?? '';
        $username = $input['username'] ?? '';
        $email = $input['email'] ?? '';
        $role = $input['role'] ?? '';

        if (empty($id) || empty($name) || empty($username) || empty($email) || empty($role)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            return;
        }

        $userData = [
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'role' => $role,
        ];

        $this->userService->updateUser($id, $userData);
    }
}
