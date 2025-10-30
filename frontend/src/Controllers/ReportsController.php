<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\ReportsService;

class ReportsController extends BaseController
{
    private ReportsService $reportsService;

    public function __construct(ApiClient $apiClient, ReportsService $reportsService)
    {
        parent::__construct($apiClient);
        $this->reportsService = $reportsService;
    }

    public function recommendationsTrace(): void
    {
        $studentId = $_GET['student_id'] ?? '';
        $trace = null;
        if ($studentId !== '') {
            $trace = $this->reportsService->getRecommendationsTrace($studentId);
        }
        $this->render('admin/reports/recommendations_trace', [
            'student_id' => $studentId,
            'trace' => $trace,
        ]);
    }
}

