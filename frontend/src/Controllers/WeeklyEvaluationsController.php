<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\WeeklyEvaluationService;

class WeeklyEvaluationsController extends BaseController
{
    protected WeeklyEvaluationService $weeklyEvaluationService;

    public function __construct(ApiClient $apiClient, WeeklyEvaluationService $weeklyEvaluationService)
    {
        parent::__construct($apiClient);
        $this->weeklyEvaluationService = $weeklyEvaluationService;
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
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $weeklyProgress = $this->weeklyEvaluationService->getWeeklyEvaluationProgressByStudentID();

        if ($weeklyProgress === null) {
            $_SESSION['messages'] = ['error' => 'Failed to load weekly evaluation progress.'];
            $weeklyProgress = [];
        }

        $this->render('siswa/weekly-evaluations', [
            'user' => $user,
            'messages' => $messages,
            'weeklyProgress' => $weeklyProgress,
        ]);
    }
}