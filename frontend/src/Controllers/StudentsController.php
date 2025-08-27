<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\StudentService;

class StudentsController extends BaseController
{
    protected StudentService $studentService;

    public function __construct(ApiClient $apiClient, StudentService $studentService)
    {
        parent::__construct($apiClient);
        $this->studentService = $studentService;
    }

    public function index(): void
    {
        $params = [
            'search' => $_GET['search'] ?? '',
            'program_id' => $_GET['program_id'] ?? '',
            'cohort_year' => $_GET['cohort_year'] ?? '',
            'status' => $_GET['status'] ?? '',
            'page' => isset($_GET['page']) ? (int)$_GET['page'] : 1,
            'limit' => 10,
        ];

        $programs = $this->studentService->listPrograms() ?? [];
        $resp = $this->studentService->search($params);

        if ($resp === null) {
            $_SESSION['messages']['error'] = 'Gagal memuat data siswa.';
            $this->redirect('/dashboard');
            return;
        }

        $data = $resp['data'];
        $meta = $resp['meta'];
        $pagination = [
            'current_page' => $meta['page'],
            'total_pages' => $meta['total_pages'],
            'total_records' => $meta['total_records'],
            'start_record' => ($meta['page'] - 1) * $meta['limit'] + 1,
            'end_record' => min($meta['page'] * $meta['limit'], $meta['total_records']),
            'base_params' => [
                'search' => $params['search'],
                'program_id' => $params['program_id'],
                'cohort_year' => $params['cohort_year'],
                'status' => $params['status'],
                'limit' => $params['limit'],
            ],
        ];

        $this->render('admin/students', [
            'user' => $_SESSION['user_data'],
            'title' => 'Siswa',
            'students' => $data,
            'programs' => $programs,
            'filters' => $params,
            'pagination' => $pagination,
        ]);
    }

    public function update(int $userId): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $payload = [
            'student_id' => $input['student_id'] ?? '',
            'program_id' => isset($input['program_id']) ? (int)$input['program_id'] : null,
            'cohort_year' => isset($input['cohort_year']) && $input['cohort_year'] !== '' ? (int)$input['cohort_year'] : null,
            'status' => $input['status'] ?? null,
            'phone' => $input['phone'] ?? null,
        ];

        if (empty($payload['student_id']) || empty($payload['program_id'])) {
            echo json_encode(['success' => false, 'message' => 'student_id dan program wajib diisi']);
            return;
        }

        $ok = $this->studentService->upsert($userId, $payload);
        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Data siswa tersimpan']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data siswa']);
        }
    }
}

