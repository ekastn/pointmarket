<?php

namespace App\Controllers;

use App\Core\ApiClient;

class WeeklyEvaluationsController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function index(): void
    {
        session_start();
        $user = $_SESSION['user_data'] ?? null;

        if (!$user || $user['role'] !== 'siswa') {
            $_SESSION['messages'] = ['error' => 'Akses ditolak. Hanya siswa yang dapat melihat halaman ini.'];
            $this->redirect('/dashboard');
            return;
        }

        $weeklyProgress = [];
        $response = $this->apiClient->getWeeklyEvaluationProgressByStudentID(52); // Fetch for a full year

        if ($response['success']) {
            $weeklyProgress = $response['data'] ?? [];
        } else {
            $_SESSION['messages'] = ['error' => $response['error'] ?? 'Gagal memuat progres evaluasi mingguan.'];
        }

        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $this->render('siswa/weekly-evaluations', [
            'user' => $user,
            'messages' => $messages,
            'weeklyProgress' => $weeklyProgress,
        ]);
    }
}
