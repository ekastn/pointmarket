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
use App\Services\StudentService;
use App\Services\ReportsService;

class DashboardController extends BaseController
{
    protected DashboardService $dashboardService;
    protected RecommendationService $recommendationService;
    protected AssignmentService $assignmentService;
    protected QuizService $quizService;
    protected WeeklyEvaluationService $weeklyEvaluationService;
    protected AnalyticsService $analyticsService;
    protected QuestionnaireService $questionnaireService;
    protected StudentService $studentService;
    protected ReportsService $reportsService;

    public function __construct(
        ApiClient $apiClient,
        DashboardService $dashboardService,
        RecommendationService $recommendationService = null,
        AssignmentService $assignmentService = null,
        QuizService $quizService = null,
        WeeklyEvaluationService $weeklyEvaluationService = null,
        AnalyticsService $analyticsService = null,
        QuestionnaireService $questionnaireService = null,
        StudentService $studentService = null,
        ReportsService $reportsService = null,
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
        $this->studentService = $studentService ?? new StudentService($apiClient);
        $this->reportsService = $reportsService ?? new ReportsService($apiClient);
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
                // Try to fetch scheduler status for admin card
                $schedulerStatus = null;
                try { $schedulerStatus = $this->weeklyEvaluationService->getSchedulerStatus(); } catch (\Throwable $e) { $schedulerStatus = null; }
                // Admin: optional recommendations trace search & view
                $traceQuery = isset($_GET['trace_q']) ? trim((string)$_GET['trace_q']) : '';
                $traceResults = null;
                if ($traceQuery !== '') {
                    try {
                        $traceResults = $this->studentService->search(['search' => $traceQuery, 'limit' => 10]);
                    } catch (\Throwable $e) {
                        $traceResults = null;
                    }
                }
                $tracePayload = null;
                $traceError = null;
                $traceStudentId = isset($_GET['trace_student_id']) ? trim((string)$_GET['trace_student_id']) : '';
                if ($traceStudentId !== '') {
                    try {
                        $traceResp = $this->reportsService->getRecommendationsTrace($traceStudentId);
                        error_log('Trace response: ' . print_r($traceResp, true));
                        if (is_array($traceResp) && array_key_exists('__error', $traceResp)) {
                            $traceError = (string)$traceResp['__error'];
                            $tracePayload = null;
                        } else {
                            $tracePayload = $traceResp;
                        }
                    } catch (\Throwable $e) {
                        $traceError = 'Trace request failed';
                        $tracePayload = null;
                    }
                }

                $this->render('admin/dashboard', [
                    'adminStats' => $adminStats,
                    'schedulerStatus' => $schedulerStatus,
                    'traceQuery' => $traceQuery,
                    'traceResults' => $traceResults,
                    'tracePayload' => $tracePayload,
                    'traceError' => $traceError,
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

                // Evaluation snapshot (counts by status) and monitoring data for student search
                $evalSummary = ['completed' => 0, 'pending' => 0, 'overdue' => 0, 'total' => 0];
                $teacherMonitoring = [];
                try {
                    $monitoring = $this->weeklyEvaluationService->getTeacherDashboard() ?? [];
                    $teacherMonitoring = $monitoring ?: [];
                    foreach ($teacherMonitoring as $row) {
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
                    'teacherMonitoring' => $teacherMonitoring,
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

                // Build weekly chart data (Option A: fetch a large window ~ all time)
                $weeklyChart = null;
                try {
                    $list = $this->weeklyEvaluationService->getWeeklyEvaluations(520) ?? [];
                    if (!empty($list)) {
                        $points = [];
                        foreach ($list as $row) {
                            $status = strtolower((string)($row['status'] ?? ''));
                            if ($status !== 'completed') { continue; }
                            if (!isset($row['score'])) { continue; }
                            $score = (float)$row['score'];
                            $dateStr = $row['completed_at'] ?? ($row['due_date'] ?? null);
                            if (!$dateStr) { continue; }
                            $ts = strtotime((string)$dateStr);
                            if (!$ts) { continue; }
                            $label = date('o-\WW', $ts); // ISO year-week label
                            $type = strtoupper((string)($row['questionnaire_type'] ?? ''));
                            if (!isset($points[$label])) { $points[$label] = ['mslq' => null, 'ams' => null, 'ts' => $ts]; }
                            if ($type === 'MSLQ') { $points[$label]['mslq'] = $score; }
                            if ($type === 'AMS')  { $points[$label]['ams']  = $score; }
                        }
                        if (!empty($points)) {
                            uasort($points, function($a, $b) { return $a['ts'] <=> $b['ts']; });
                            $labels = array_keys($points);
                            $mslq = [];$ams = [];
                            foreach ($labels as $lbl) { $mslq[] = $points[$lbl]['mslq']; $ams[] = $points[$lbl]['ams']; }
                            $weeklyChart = ['labels' => $labels, 'mslq' => $mslq, 'ams' => $ams];
                        }
                    }
                } catch (\Throwable $e) {
                    $weeklyChart = null;
                }

                // Build VARK history chart (scores over time)
                $varkChart = null;
                $varkLabelProgress = null;
                $varkComposite = null;
                try {
                    $vh = $this->questionnaireService->getVarkHistory(200, 0) ?? [];
                    if (!empty($vh)) {
                        $rows = [];
                        $labelRows = [];
                        foreach ($vh as $row) {
                            $when = $row['completed_at'] ?? null;
                            $scores = $row['vark_scores'] ?? null;
                            if (!$when || !is_array($scores)) { continue; }
                            $ts = strtotime((string)$when);
                            if (!$ts) { continue; }
                            $label = date('Y-m-d', $ts); // simple date label
                            $rows[] = [
                                'ts' => $ts,
                                'label' => $label,
                                'visual' => isset($scores['visual']) ? (float)$scores['visual'] : null,
                                'auditory' => isset($scores['auditory']) ? (float)$scores['auditory'] : null,
                                'reading' => isset($scores['reading']) ? (float)$scores['reading'] : null,
                                'kinesthetic' => isset($scores['kinesthetic']) ? (float)$scores['kinesthetic'] : null,
                            ];
                            $styleLabel = $row['vark_style_label'] ?? null;
                            $labelRows[] = [ 'ts' => $ts, 'label' => $label, 'style' => $styleLabel ];
                        }
                        if (!empty($rows)) {
                            usort($rows, function($a,$b){ return $a['ts'] <=> $b['ts']; });
                            $labels = array_column($rows, 'label');
                            $visual = array_map(fn($r)=>$r['visual'], $rows);
                            $auditory = array_map(fn($r)=>$r['auditory'], $rows);
                            $reading = array_map(fn($r)=>$r['reading'], $rows);
                            $kinesthetic = array_map(fn($r)=>$r['kinesthetic'], $rows);
                            $varkChart = [
                                'labels' => $labels,
                                'visual' => $visual,
                                'auditory' => $auditory,
                                'reading' => $reading,
                                'kinesthetic' => $kinesthetic,
                            ];

                            // Label progress (style label per date)
                            if (!empty($labelRows)) {
                                usort($labelRows, function($a,$b){ return $a['ts'] <=> $b['ts']; });
                                $varkLabelProgress = [
                                    'labels' => array_column($labelRows, 'label'),
                                    'styles' => array_map(fn($r)=> (string)($r['style'] ?? ''), $labelRows),
                                ];
                            }

                            // Composite progress aligned with RecommendationService:
                            // For each submission, scale each of the four scores by (score / maxOfFour) * 10, then average.
                            $composites = [];
                            foreach ($rows as $r) {
                                $raw = [ $r['visual'], $r['auditory'], $r['reading'], $r['kinesthetic'] ];
                                $vals = array_values(array_filter($raw, fn($x) => $x !== null));
                                if (empty($vals)) { $composites[] = null; continue; }
                                $max = max($vals);
                                if ($max <= 0) { $composites[] = null; continue; }
                                $norms = array_map(function($v) use ($max) { return ($v / $max) * 10.0; }, $vals);
                                $composites[] = array_sum($norms) / count($norms);
                            }
                            $varkComposite = [ 'labels' => $labels, 'composite' => $composites ];
                        }
                    }
                } catch (\Throwable $e) {
                    $varkChart = null;
                    $varkLabelProgress = null;
                    $varkComposite = null;
                }

                $this->render('siswa/dashboard', [
                    'studentStats' => $studentStats,
                    'weekly_evaluations' => $weeklyEvaluations,
                    'recommendations' => $recommendations,
                    'missingAssessments' => $missingAssessments,
                    'varkQuestionnaireLink' => $varkQuestionnaireLink,
                    'weeklyChart' => $weeklyChart,
                    'varkChart' => $varkChart,
                    'varkLabelProgress' => $varkLabelProgress,
                    'varkComposite' => $varkComposite,
                ]);
                return;
        }
    }
}
