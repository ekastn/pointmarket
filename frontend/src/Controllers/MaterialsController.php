<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\MaterialService;

class MaterialsController extends BaseController
{
    protected MaterialService $materialService;

    public function __construct(ApiClient $apiClient, MaterialService $materialService)
    {
        parent::__construct($apiClient);
        $this->materialService = $materialService;
    }

    public function index(): void
    {
        session_start();
        $user = $_SESSION['user_data'] ?? null;

        $materials = [];
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

                $materialsData = $this->materialService->getAllMaterials();
        if ($materialsData !== null) {
            $materials = $materialsData ?? [];
        } else {
            $_SESSION['messages'] = ['error' => 'Failed to fetch materials.'];
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'subject' => $_POST['subject'] ?? '',
                'file_path' => $_POST['file_path'] ?? '',
                'file_type' => $_POST['file_type'] ?? '',
            ];

                        $result = $this->materialService->createMaterial($data);

            if ($result !== null) {
                $_SESSION['messages'] = ['success' => 'Material created successfully!'];
                $this->redirect('/materials');
            } else {
                $_SESSION['messages']['error'] = 'Failed to create material.';
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

        $materialData = $this->materialService->getMaterialByID($id);
        if ($materialData === null) {
            $_SESSION['messages'] = ['error' => 'Material not found.'];
            $this->redirect('/materials');
            return;
        }
        $material = $materialData;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'subject' => $_POST['subject'] ?? '',
                'file_path' => $_POST['file_path'] ?? '',
                'file_type' => $_POST['file_type'] ?? '',
                'status' => $_POST['status'] ?? 'active',
            ];

            $result = $this->materialService->updateMaterial($id, $data);

            if ($result !== null) {
                $_SESSION['messages'] = ['success' => 'Material updated successfully!'];
                $this->redirect('/materials');
            } else {
                $_SESSION['messages'] = ['error' => 'Failed to update material.'];
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

        $result = $this->materialService->deleteMaterial($id);

        if ($result !== null) {
            $_SESSION['messages'] = ['success' => 'Material deleted successfully!'];
        } else {
            $_SESSION['messages']['error'] = 'Failed to delete material.';
        }
        $this->redirect('/materials');
    }
}
