<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\DashboardService;
use App\Services\RecommendationService;
use App\Services\AssignmentService;
use App\Services\QuizService;
use App\Services\WeeklyEvaluationService;
use App\Services\AnalyticsService;
use App\Services\QuestionnaireService;

class DashboardController extends BaseController
{
    protected DashboardService $dashboardService;
    protected RecommendationService $recommendationService;
    protected AssignmentService $assignmentService;
    protected QuizService $quizService;
    protected WeeklyEvaluationService $weeklyEvaluationService;
    protected AnalyticsService $analyticsService;
    protected QuestionnaireService $questionnaireService;

    public function __construct(
        ApiClient $apiClient,
        DashboardService $dashboardService,
        RecommendationService $recommendationService = null,
        AssignmentService $assignmentService = null,
        QuizService $quizService = null,
        WeeklyEvaluationService $weeklyEvaluationService = null,
        AnalyticsService $analyticsService = null,
        QuestionnaireService $questionnaireService = null,
    )
    {
        parent::__construct($apiClient);
        $this->dashboardService = $dashboardService;
        $this->recommendationService = $recommendationService ?? new RecommendationService($apiClient);
        $this->assignmentService = $assignmentService ?? new AssignmentService($apiClient);
        $this->quizService = $quizService ?? new QuizService($apiClient);
        $this->weeklyEvaluationService = $weeklyEvaluationService ?? new WeeklyEvaluationService($apiClient);
        $this->analyticsService = $analyticsService ?? new AnalyticsService($apiClient);
        $this->questionnaireService = $questionnaireService ?? new QuestionnaireService($apiClient);
    }

    public function showDashboard(): void
    {
        // Make a single API call to get all dashboard data
        $dashboardData = $this->dashboardService->getDashboardData();

        if ($dashboardData === null) {
            throw new \Exception('Failed to fetch dashboard data.');
        }

        $userRole = $_SESSION['user_data']['role'];

        switch ($userRole) {
            case 'admin':
                $adminStats = $dashboardData['admin_stats'] ?? null;
                $this->render('admin/dashboard', [
                    'adminStats' => $adminStats
                ]);
                return;
            case 'guru':
                $teacherStats = $dashboardData['teacher_stats'] ?? [];

                // Recent items (take last 5 by created_at desc)
                $recentAssignments = [];
                $recentQuizzes = [];
                try {
                    $assignmentsResp = $this->assignmentService->getAssignments();
                    $assignments = $assignmentsResp['assignments'] ?? ($assignmentsResp ?? []);
                    usort($assignments, function($a, $b){
                        return strtotime($b['created_at'] ?? '0') <=> strtotime($a['created_at'] ?? '0');
                    });
                    $recentAssignments = array_slice($assignments, 0, 5);
                } catch (\Throwable $e) { /* ignore */ }

                try {
                    $quizzesResp = $this->quizService->getQuizzes();
                    $quizzes = $quizzesResp['quizzes'] ?? ($quizzesResp ?? []);
                    usort($quizzes, function($a, $b){
                        return strtotime($b['created_at'] ?? '0') <=> strtotime($a['created_at'] ?? '0');
                    });
                    $recentQuizzes = array_slice($quizzes, 0, 5);
                } catch (\Throwable $e) { /* ignore */ }

                // Evaluation snapshot (counts by status)
                $evalSummary = ['completed' => 0, 'pending' => 0, 'overdue' => 0, 'total' => 0];
                try {
                    $monitoring = $this->weeklyEvaluationService->getTeacherDashboard() ?? [];
                    foreach (($monitoring ?: []) as $row) {
                        $status = strtolower((string)($row['status'] ?? ''));
                        if (isset($evalSummary[$status])) {
                            $evalSummary[$status]++;
                        }
                        $evalSummary['total']++;
                    }
                } catch (\Throwable $e) { /* ignore */ }

                // Course insights for charts
                $courseInsights = [];
                try { $courseInsights = $this->analyticsService->getTeacherCourseInsights(10) ?? []; } catch (\Throwable $e) { /* ignore */ }

                $this->render('guru/dashboard', [
                    'teacherStats' => $teacherStats,
                    'recentAssignments' => $recentAssignments,
                    'recentQuizzes' => $recentQuizzes,
                    'evalSummary' => $evalSummary,
                    'courseInsights' => $courseInsights,
                ]);
                return;
            case 'siswa':
                $studentStats = $dashboardData['student_stats'] ?? null;
                $weeklyEvaluations = $studentStats['weekly_evaluations'] ?? []; // Extract the new data
                $userId = $_SESSION['user_data']['id'] ?? null; // Pass current user id for lazy loads
                // Determine if we have sufficient psychometric coverage to request recommendations
                $recommendations = null;
                $missingAssessments = [];
                if ($studentStats && $userId) {
                    $hasMSLQ = !empty($studentStats['mslq_score']) && $studentStats['mslq_score'] > 0;
                    $hasAMS  = !empty($studentStats['ams_score'])  && $studentStats['ams_score'] > 0;
                    $learningStyle = $studentStats['learning_style'] ?? null;
                    $scores = $learningStyle['scores'] ?? [];
                    $hasVARK = isset($scores['visual'], $scores['auditory'], $scores['reading'], $scores['kinesthetic'])
                        && ($scores['visual'] ?? 0) > 0; // assume others >0 if one >0

                    if (!$hasVARK) $missingAssessments[] = 'VARK';
                    if (!$hasMSLQ) $missingAssessments[] = 'MSLQ';
                    if (!$hasAMS)  $missingAssessments[] = 'AMS';

                    if (empty($missingAssessments)) {
                        // Full coverage -> fetch
                        $recommendations = $this->recommendationService->getStudentRecommendations((int)$userId) ?? [];
                    }
                }

                // Compute dynamic VARK questionnaire link (Option A)
                $varkQuestionnaireLink = '/questionnaires';
                try {
                    $allQs = $this->questionnaireService->getAllQuestionnaires() ?? [];
                    // filter VARK, prefer active, pick latest by id
                    $varkQs = array_values(array_filter($allQs, function ($q) {
                        $isVark = isset($q['type']) && strtoupper((string)$q['type']) === 'VARK';
                        $isActive = !isset($q['status']) || strtolower((string)$q['status']) === 'active';
                        return $isVark && $isActive;
                    }));
                    if (empty($varkQs)) {
                        // fallback: any VARK regardless of status
                        $varkQs = array_values(array_filter($allQs, function ($q) {
                            return isset($q['type']) && strtoupper((string)$q['type']) === 'VARK';
                        }));
                    }
                    if (!empty($varkQs)) {
                        usort($varkQs, function ($a, $b) {
                            return (int)($a['id'] ?? 0) <=> (int)($b['id'] ?? 0);
                        });
                        $latest = end($varkQs);
                        if (!empty($latest['id'])) {
                            $varkQuestionnaireLink = '/questionnaires/' . (int)$latest['id'];
                        }
                    }
                } catch (\Throwable $e) {
                    // leave default fallback
                }

                $this->render('siswa/dashboard', [
                    'studentStats' => $studentStats,
                    'weekly_evaluations' => $weeklyEvaluations,
                    'recommendations' => $recommendations,
                    'missingAssessments' => $missingAssessments,
                    'varkQuestionnaireLink' => $varkQuestionnaireLink,
                ]);
                return;
        }
    }
}
