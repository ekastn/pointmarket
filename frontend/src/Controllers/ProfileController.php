<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\ProfileService;

class ProfileController extends BaseController
{
    protected ProfileService $profileService;

    public function __construct(ApiClient $apiClient, ProfileService $profileService)
    {
        parent::__construct($apiClient);
        $this->profileService = $profileService;
    }

    public function showProfile(): void
    {
        $userProfile = $this->profileService->getUserProfile();
        if ($userProfile === null) {
            $_SESSION['messages'] = ['error' => 'Gagal memuat profil pengguna.'];
        }

        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $viewName = 'profile';

        $this->render($viewName, [
            'title' => 'Profil',
            'user' => $userProfile,
            'messages' => $messages,
        ]);
    }

    public function updateProfile(): void
    {
        if (!isset($_SESSION['jwt_token'])) {
            $this->redirect('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'avatar' => $_POST['avatar'] ?? null, // Assuming avatar is a URL or path
                'bio' => $_POST['bio'] ?? null,
            ];

            $ok = $this->profileService->updateProfile($data);

            if ($ok) {
                $_SESSION['messages'] = ['success' => 'Profil berhasil diperbarui.'];
                // Update user_data in session after successful profile update
                $userProfileData = $this->profileService->getUserProfile();
                if ($userProfileData !== null) {
                    $_SESSION['user_data'] = $userProfileData;
                }
                $this->redirect('/profile');
            } else {
                $_SESSION['messages'] = ['error' => 'Gagal memperbarui profil.'];
                $this->redirect('/profile');
            }
        } else {
            $this->redirect('/profile');
        }
    }

    public function changePassword(): void
    {
        if (!isset($_SESSION['jwt_token'])) {
            $this->redirect('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'current_password' => $_POST['current_password'] ?? '',
                'new_password' => $_POST['new_password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? '',
            ];

            $ok = $this->profileService->changePassword($data);
            if ($ok) {
                $_SESSION['messages'] = ['success' => 'Kata sandi berhasil diubah.'];
            } else {
                $_SESSION['messages'] = ['error' => 'Gagal mengubah kata sandi. Periksa kembali isian Anda.'];
            }
            $this->redirect('/profile');
        } else {
            $this->redirect('/profile');
        }
    }

    public function uploadAvatar(): void
    {
        if (!isset($_FILES['file'])) {
            $_SESSION['messages'] = ['error' => 'No image file provided.'];
            $this->redirect('/profile');
            return;
        }

        // Basic validations
        $file = $_FILES['file'];
        $size = $file['size'] ?? 0;

        if ($size <= 0) {
            $_SESSION['messages'] = ['error' => 'Invalid file.'];
            $this->redirect('/profile');
            return;
        }

        if ($size > 6 * 1024 * 1024) { // slight headroom over backend limit
            $_SESSION['messages'] = ['error' => 'File is too large. Max 5 MB.'];
            $this->redirect('/profile');
            return;
        }

        $ok = $this->profileService->uploadAvatar($file);
        if ($ok) {
            $_SESSION['messages'] = ['success' => 'Profile photo updated.'];
            // Refresh session user data
            $user = $this->profileService->getUserProfile();
            if ($user !== null) {
                $_SESSION['user_data'] = $user;
            }
        } else {
            $_SESSION['messages'] = ['error' => 'Failed to upload photo.'];
        }
        $this->redirect('/profile');
    }
}
