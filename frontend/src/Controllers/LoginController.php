<?php

namespace App\Controllers;

use App\Services\ApiClient;

class LoginController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function showLoginForm(): void
    {
        session_start();
        // If user is already logged in, redirect to dashboard
        if (isset($_SESSION['jwt_token'])) {
            $this->redirect('/dashboard');
            return;
        }

        // Messages will be handled by the session and passed to the view
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']); // Clear messages after displaying

        $this->render('login', ['messages' => $messages], 'login_layout');
    }

    public function processLogin(): void
    {
        session_start();
        // Basic input validation
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';

        if (empty($username) || empty($password) || empty($role)) {
            $_SESSION['messages'] = ['error' => 'Semua field harus diisi.'];
            $this->redirect('/login');
            return;
        }

        // Call Go backend API for login
        $response = $this->apiClient->login($username, $password, $role);

        if ($response['success']) {
            // Store JWT token in session
            session_start(); // Ensure session is started before setting
            $_SESSION['jwt_token'] = $response['data']['token'];
            // Optionally store user info from response
            $_SESSION['user_data'] = $response['data']['user'] ?? null;

            $_SESSION['messages'] = ['success' => 'Login berhasil! Selamat datang.'];
            $this->redirect('/dashboard');
        } else {
            $_SESSION['messages'] = ['error' => $response['error'] ?? 'Login failed. Please check your credentials.'];
            $this->redirect('/login');
        }
    }
}