<?php

namespace App\Controllers;

use App\Core\ApiClient;

class MaterialsController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function index(): void
    {
        session_start();
        $user = $_SESSION['user_data'] ?? null;

        // This should ideally be handled by AuthMiddleware, but as a fallback
        if (!$user) {
            $userProfileResponse = $this->apiClient->getUserProfile();
            if ($userProfileResponse['success']) {
                $user = $userProfileResponse['data'];
                $_SESSION['user_data'] = $user;
            } else {
                $_SESSION['messages'] = ['error' => $userProfileResponse['error'] ?? 'Gagal memuat profil pengguna.'];
                session_destroy();
                $this->redirect('/login');
                return;
            }
        }

        $materials = [];
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $materialsResponse = $this->apiClient->getAllMaterials();
        if ($materialsResponse['success']) {
            $materials = $materialsResponse['data'] ?? [];
        } else {
            $_SESSION['messages'] = ['error' => $materialsResponse['error'] ?? 'Failed to fetch materials.'];
        }

        $this->render('guru/materials', [
            'title' => 'Study Materials',
            'user' => $user,
            'materials' => $materials,
            'messages' => $messages,
        ]);
    }

    public function create(): void
    {
        session_start();
        $user = $_SESSION['user_data'] ?? null;

        // This should ideally be handled by AuthMiddleware, but as a fallback
        if (!$user) {
            $userProfileResponse = $this->apiClient->getUserProfile();
            if ($userProfileResponse['success']) {
                $user = $userProfileResponse['data'];
                $_SESSION['user_data'] = $user;
            } else {
                $_SESSION['messages'] = ['error' => $userProfileResponse['error'] ?? 'Gagal memuat profil pengguna.'];
                session_destroy();
                $this->redirect('/login');
                return;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'subject' => $_POST['subject'] ?? '',
                'file_path' => $_POST['file_path'] ?? '',
                'file_type' => $_POST['file_type'] ?? '',
            ];

            $response = $this->apiClient->createMaterial($data);

            if ($response['success']) {
                $_SESSION['messages'] = ['success' => 'Material created successfully!'];
                $this->redirect('/materials');
            } else {
                $_SESSION['messages'] = ['error' => $response['error'] ?? 'Failed to create material.'];
                $this->redirect('/materials/create');
            }
        }

        $this->render('guru/materials_create', [
            'title' => 'Create Material',
            'user' => $user,
            'messages' => $_SESSION['messages'] ?? [],
        ]);
        unset($_SESSION['messages']);
    }

    public function edit(int $id): void
    {
        session_start();
        $user = $_SESSION['user_data'] ?? null;

        // This should ideally be handled by AuthMiddleware, but as a fallback
        if (!$user) {
            $userProfileResponse = $this->apiClient->getUserProfile();
            if ($userProfileResponse['success']) {
                $user = $userProfileResponse['data'];
                $_SESSION['user_data'] = $user;
            } else {
                $_SESSION['messages'] = ['error' => $userProfileResponse['error'] ?? 'Gagal memuat profil pengguna.'];
                session_destroy();
                $this->redirect('/login');
                return;
            }
        }

        $materialResponse = $this->apiClient->getMaterialByID($id);
        if (!$materialResponse['success']) {
            $_SESSION['messages'] = ['error' => $materialResponse['error'] ?? 'Material not found.'];
            $this->redirect('/materials');
            return;
        }
        $material = $materialResponse['data'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'subject' => $_POST['subject'] ?? '',
                'file_path' => $_POST['file_path'] ?? '',
                'file_type' => $_POST['file_type'] ?? '',
                'status' => $_POST['status'] ?? 'active',
            ];

            $response = $this->apiClient->updateMaterial($id, $data);

            if ($response['success']) {
                $_SESSION['messages'] = ['success' => 'Material updated successfully!'];
                $this->redirect('/materials');
            } else {
                $_SESSION['messages'] = ['error' => $response['error'] ?? 'Failed to update material.'];
                $this->redirect('/materials/edit/' . $id);
            }
        }

        $this->render('guru/materials_edit', [
            'title' => 'Edit Material',
            'user' => $user,
            'material' => $material,
            'messages' => $_SESSION['messages'] ?? [],
        ]);
        unset($_SESSION['messages']);
    }

    public function delete(int $id): void
    {
        session_start();
        // This should ideally be handled by AuthMiddleware, but as a fallback
        if (!isset($_SESSION['jwt_token'])) {
            $this->redirect('/login');
            return;
        }

        $this->apiClient->setJwtToken($_SESSION['jwt_token']);

        $response = $this->apiClient->deleteMaterial($id);

        if ($response['success']) {
            $_SESSION['messages'] = ['success' => 'Material deleted successfully!'];
        } else {
            $_SESSION['messages'] = ['error' => $response['error'] ?? 'Failed to delete material.'];
        }
        $this->redirect('/materials');
    }
}
