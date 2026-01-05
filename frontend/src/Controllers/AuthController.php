<?php

namespace App\Controllers;

use App\Core\ApiClient;

class AuthController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function showLoginForm(): void
    {
        // Check if user is already authenticated, redirect to dashboard
        session_start();
        if (isset($_SESSION['jwt_token'])) {
            $this->redirect('/dashboard');
            return;
        }

        // Messages will be handled by the session and passed to the view
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']); // Clear messages after displaying

        $this->render('login', ['messages' => $messages], 'login_layout');
    }

    public function showRegisterForm(): void
    {
        session_start();
        if (isset($_SESSION['jwt_token'])) {
            $this->redirect('/dashboard');
            return;
        }

        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $this->render('register', ['messages' => $messages], 'login_layout');
    }

    public function processLogin(): void
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['messages'] = ['error' => 'Semua field harus diisi.'];
            $this->redirect('/login');
            return;
        }

        // Call Go backend API for login
        $response = $this->apiClient->login($username, $password);

        if ($response['success']) {
            // Store JWT token in session
            session_start(); // Ensure session is started before setting
            $_SESSION['jwt_token'] = $response['data']['token'];
            // Optionally store user info from response
            $_SESSION['user_data'] = $response['data']['user'] ?? null;

            $_SESSION['messages'] = ['success' => 'Login berhasil! Selamat datang.'];
            $this->redirect('/dashboard');
        } else {
            $_SESSION['messages'] = ['error' => 'Username atau password salah. Silakan coba lagi.'];
            $this->redirect('/login');
        }
    }

    public function logout(): void
    {
        session_start();
        session_unset();   // Unset all session variables
        session_destroy(); // Destroy the session
        $this->redirect('/login');
    }

    // Handle student self-registration (user-only), with auto-login on success
    public function processRegister(): void
    {
        session_start();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        // Basic validations
        if ($name === '' || $email === '' || $username === '' || $password === '' || $confirm === '') {
            $_SESSION['messages'] = ['error' => 'Semua field harus diisi.'];
            // Hint UI (optional): switch to register tab on next load
            $_SESSION['auth_active_tab'] = 'register';
            $this->redirect('/login');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['messages'] = ['error' => 'Format email tidak valid.'];
            $_SESSION['auth_active_tab'] = 'register';
            $this->redirect('/login');
            return;
        }

        if ($password !== $confirm) {
            $_SESSION['messages'] = ['error' => 'Konfirmasi password tidak cocok.'];
            $_SESSION['auth_active_tab'] = 'register';
            $this->redirect('/login');
            return;
        }

        // Call backend register API; force role siswa on client side
        $registerResponse = $this->apiClient->request('POST', '/api/v1/auth/register', [
            'json' => [
                'username' => $username,
                'password' => $password,
                'name'     => $name,
                'email'    => $email,
                'role'     => 'siswa',
            ],
        ]);

        if (!($registerResponse['success'] ?? false)) {
            $err = $registerResponse['error'] ?? ($registerResponse['message'] ?? 'Registrasi gagal.');
            $_SESSION['messages'] = ['error' => $err];
            $_SESSION['auth_active_tab'] = 'register';
            $this->redirect('/login');
            return;
        }

        // Auto-login using the same credentials
        $loginResponse = $this->apiClient->login($username, $password);
        if (($loginResponse['success'] ?? false) && isset($loginResponse['data']['token'])) {
            // Set session JWT and user data
            $_SESSION['jwt_token'] = $loginResponse['data']['token'];
            $_SESSION['user_data'] = $loginResponse['data']['user'] ?? null;

            $_SESSION['messages'] = ['success' => 'Registrasi berhasil! Anda telah masuk otomatis.'];
            $this->redirect('/dashboard');
            return;
        }

        // Fallback: registration succeeded but login failed
        $_SESSION['messages'] = ['success' => 'Registrasi berhasil! Silakan login.'];
        $this->redirect('/login');
    }
}
