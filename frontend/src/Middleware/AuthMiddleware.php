<?php

namespace App\Middleware;

use App\Services\ApiClient;
use App\Controllers\BaseController;

class AuthMiddleware extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function requireLogin(): bool
    {
        session_start();
        if (!isset($_SESSION['jwt_token'])) {
            $_SESSION['messages'] = ['error' => 'Sesi Anda telah berakhir. Silakan login kembali.'];
            $this->redirect('/login');
            return false;
        }
        $this->apiClient->setJwtToken($_SESSION['jwt_token']);
        return true;
    }

    public function requireRole(string $role): bool
    {
        session_start();
        if (!isset($_SESSION['jwt_token'])) {
            $_SESSION['messages'] = ['error' => 'Sesi Anda telah berakhir. Silakan login kembali.'];
            $this->redirect('/login');
            return false;
        }

        $this->apiClient->setJwtToken($_SESSION['jwt_token']);
        $userProfileResponse = $this->apiClient->getUserProfile();

        if (!$userProfileResponse['success']) {
            $_SESSION['messages'] = ['error' => $userProfileResponse['error'] ?? 'Gagal memuat profil pengguna.'];
            session_destroy();
            $this->redirect('/login');
            return false;
        }

        $user = $userProfileResponse['data'];

        if ($user['role'] !== $role && $user['role'] !== 'admin') {
            $_SESSION['messages'] = ['error' => 'Anda tidak memiliki akses ke halaman ini.'];
            $this->redirect('/dashboard');
            return false;
        }
        return true;
    }

    public function requireAdmin(): bool
    {
        return $this->requireRole('admin');
    }

    public function requireTeacher(): bool
    {
        return $this->requireRole('guru');
    }

    public function requireStudent(): bool
    {
        return $this->requireRole('siswa');
    }
}
