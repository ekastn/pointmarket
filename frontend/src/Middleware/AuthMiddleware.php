<?php

namespace App\Middleware;

use App\Controllers\BaseController;
use App\Core\ApiClient;
use App\Services\ProfileService;

class AuthMiddleware extends BaseController
{
    protected ProfileService $profileService;

    public function __construct(ApiClient $apiClient, ProfileService $profileService)
    {
        parent::__construct($apiClient);
        $this->profileService = $profileService;
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
        if (!$this->requireLogin()) {
            $this->redirect('/login');
            return false;
        }

        $user = $this->profileService->getUserProfile();

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
