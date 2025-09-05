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

        $viewName = 'profile';

        $this->render($viewName, [
            'title' => 'Profil',
            'user' => $userProfile,
        ]);
    }

    public function updateProfile(): void
    {
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
        error_log('Avatar upload: handler start');

        // Detect request too large (post_max_size exceeded) — $_FILES may be empty in this case
        if (empty($_FILES) && isset($_SERVER['CONTENT_LENGTH'])) {
            error_log('Avatar upload: empty $_FILES with CONTENT_LENGTH=' . ($_SERVER['CONTENT_LENGTH'] ?? '')); 
            $_SESSION['messages'] = ['error' => 'Request body too large. Please upload a smaller image.'];
            $this->redirect('/profile');
            return;
        }

        if (!isset($_FILES['file'])) {
            error_log('Avatar upload: no file field present');
            $_SESSION['messages'] = ['error' => 'No image file provided.'];
            $this->redirect('/profile');
            return;
        }

        // Basic validations and clearer PHP upload error handling
        $file = $_FILES['file'];
        $uploadError = $file['error'] ?? UPLOAD_ERR_OK;
        if ($uploadError !== UPLOAD_ERR_OK) {
            $msg = 'Upload failed.';
            switch ($uploadError) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $msg = 'File is too large. Max 5 MB.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $msg = 'Upload was not completed. Please try again.';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $msg = 'No file was uploaded.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                case UPLOAD_ERR_CANT_WRITE:
                case UPLOAD_ERR_EXTENSION:
                    $msg = 'Server error storing upload. Please contact support.';
                    break;
            }
            error_log('Avatar upload: PHP upload error code=' . $uploadError . ' message=' . $msg);
            $_SESSION['messages'] = ['error' => $msg];
            $this->redirect('/profile');
            return;
        }

        $size = $file['size'] ?? 0;
        if ($size <= 0) {
            error_log('Avatar upload: invalid size=' . $size);
            $_SESSION['messages'] = ['error' => 'Invalid file.'];
            $this->redirect('/profile');
            return;
        }

        if ($size > 6 * 1024 * 1024) { // slight headroom over backend limit
            error_log('Avatar upload: file too large size=' . $size);
            $_SESSION['messages'] = ['error' => 'File is too large. Max 5 MB.'];
            $this->redirect('/profile');
            return;
        }

        error_log('Avatar upload: calling service with file name=' . ($file['name'] ?? '') . ' size=' . $size);
        $ok = $this->profileService->uploadAvatar($file);
        if ($ok) {
            $_SESSION['messages'] = ['success' => 'Profile photo updated.'];
            // Refresh session user data
            $user = $this->profileService->getUserProfile();
            if ($user !== null) {
                $_SESSION['user_data'] = $user;
            }
        } else {
            error_log('Avatar upload: API call failed or returned no URL');
            $_SESSION['messages'] = ['error' => 'Failed to upload photo.'];
        }
        $this->redirect('/profile');
    }
}
