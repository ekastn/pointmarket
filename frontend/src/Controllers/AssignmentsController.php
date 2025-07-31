<?php

namespace App\Controllers;

use App\Core\ApiClient;

class AssignmentsController extends BaseController
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

        $assignments = [];
        $stats = [
            'total_assignments' => 0,
            'completed' => 0,
            'in_progress' => 0,
            'overdue' => 0,
            'total_points' => 0,
            'average_score' => 0
        ];
        $subjects = [];
        $pendingEvaluations = []; // Placeholder for now

        $assignmentsResponse = $this->apiClient->getAssignments(null, $user['id']);

        if ($assignmentsResponse['success']) {
            $assignments = $assignmentsResponse['data'] ?? [];

            // Calculate stats and subjects based on fetched data
            $totalPointsSum = 0;
            $completedCount = 0;
            $inProgressCount = 0;
            $overdueCount = 0;
            $subjects = [];

            foreach ($assignments as $assignment) {
                $stats['total_assignments']++;
                if ($assignment['student_status'] === 'completed') {
                    $stats['completed']++;
                    $totalPointsSum += $assignment['points'];
                    if ($assignment['score'] !== null) {
                        $completedCount++;
                        // $totalScoreSum += $assignment['score']; // This is not directly available in the new DTO
                    }
                } elseif ($assignment['student_status'] === 'in_progress') {
                    $stats['in_progress']++;
                }

                if ($assignment['urgency_status'] === 'overdue') {
                    $stats['overdue']++;
                }

                // Collect subjects and their counts
                $subjectFound = false;
                foreach ($subjects as &$subject) {
                    if ($subject['subject'] === $assignment['subject']) {
                        $subject['total_assignments']++;
                        if ($assignment['student_status'] === 'completed') {
                            $subject['completed_assignments']++;
                        }
                        $subjectFound = true;
                        break;
                    }
                }
                unset($subject); // Break the reference

                if (!$subjectFound) {
                    $subjects[] = [
                        'subject' => $assignment['subject'],
                        'total_assignments' => 1,
                        'completed_assignments' => ($assignment['student_status'] === 'completed') ? 1 : 0
                    ];
                }
            }

            $stats['total_points'] = $totalPointsSum;
            // Average score calculation might need to be done on backend or re-evaluated based on available data
            // For now, keep it simple or remove if not directly supported by backend DTO
            $stats['average_score'] = 0; // Reset or calculate based on new DTO

        } else {
            $_SESSION['messages'] = ['error' => $assignmentsResponse['error'] ?? 'Failed to fetch assignments.'];
        }

        $status_filter = $_GET['status'] ?? 'all';
        $subject_filter = $_GET['subject'] ?? 'all';
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $this->render('siswa/assignments', [
            'title' => 'Assignments',
            'user' => $user,
            'assignments' => $assignments,
            'stats' => $stats,
            'subjects' => $subjects,
            'pendingEvaluations' => $pendingEvaluations,
            'status_filter' => $status_filter,
            'subject_filter' => $subject_filter,
            'messages' => $messages,
        ]);
    }

    public function startAssignment(): void
    {
        session_start();
        $jwt = $_SESSION['jwt_token'] ?? null;

        if (!$jwt) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $this->apiClient->setJwtToken($jwt);

        $input = json_decode(file_get_contents('php://input'), true);
        $assignmentId = $input['assignment_id'] ?? null;

        if (!$assignmentId) {
            echo json_encode(['success' => false, 'message' => 'Invalid assignment ID']);
            return;
        }

        $response = $this->apiClient->startAssignment($assignmentId);

        if ($response['success']) {
            echo json_encode(['success' => true, 'message' => 'Assignment started successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => $response['error'] ?? 'Failed to start assignment.']);
        }
    }

    public function submitAssignment(): void
    {
        session_start();
        $jwt = $_SESSION['jwt_token'] ?? null;

        if (!$jwt) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $this->apiClient->setJwtToken($jwt);

        $input = json_decode(file_get_contents('php://input'), true);
        $assignmentId = $input['assignment_id'] ?? null;
        $submissionText = $input['submission_text'] ?? '';

        if (!$assignmentId || empty($submissionText)) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            return;
        }

        $response = $this->apiClient->submitAssignment($assignmentId, $submissionText);

        if ($response['success']) {
            echo json_encode(['success' => true, 'message' => 'Assignment submitted successfully!', 'score' => $response['data']['score'] ?? null]);
        } else {
            echo json_encode(['success' => false, 'message' => $response['error'] ?? 'Failed to submit assignment.']);
        }
    }
}
