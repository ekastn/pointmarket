<?php

namespace App\Controllers;

use App\Services\ApiClient;

class AssignmentsController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function index(): void
    {
        session_start();
        $jwt = $_SESSION['jwt_token'] ?? null;

        if (!$jwt) {
            $this->redirect('/login');
            return;
        }

        $this->apiClient->setJwtToken($jwt);
        $userProfileResponse = $this->apiClient->getUserProfile();

        if (!$userProfileResponse['success']) {
            session_destroy();
            $this->redirect('/login');
            return;
        }

        $user = $userProfileResponse['data'];

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

        $assignmentsResponse = $this->apiClient->getAssignments();

        if ($assignmentsResponse['success']) {
            $assignments = $assignmentsResponse['data']['assignments'] ?? [];

            $totalPointsSum = 0;
            $completedCount = 0;
            $totalScoreSum = 0;

            foreach ($assignments as &$assignment) {
                // Calculate days remaining and urgency status
                $dueDate = new \DateTime($assignment['due_date']);
                $now = new \DateTime();
                $interval = $now->diff($dueDate);
                $daysRemaining = (int)$interval->format('%r%a');
                $assignment['days_remaining'] = $daysRemaining;

                if ($daysRemaining < 0) {
                    $assignment['urgency_status'] = 'overdue';
                } elseif ($daysRemaining <= 2) {
                    $assignment['urgency_status'] = 'urgent';
                } else {
                    $assignment['urgency_status'] = 'normal';
                }

                // Simulate student status and score for demo purposes
                // In a real app, this would come from student_assignments table
                $assignment['student_status'] = 'not_started'; // Default
                $assignment['score'] = null;
                $assignment['submitted_at'] = null;

                // Simple simulation: if assignment ID is even, it's completed
                // if ($assignment['id'] % 2 == 0) {
                //     $assignment['student_status'] = 'completed';
                //     $assignment['score'] = rand(60, 100); // Simulated score
                //     $assignment['submitted_at'] = date('Y-m-d H:i:s', strtotime('-' . rand(1, 10) . ' days'));
                // } else if ($assignment['id'] % 3 == 0) {
                //     $assignment['student_status'] = 'in_progress';
                // }

                // Update stats
                $stats['total_assignments']++;
                if ($assignment['student_status'] === 'completed') {
                    $stats['completed']++;
                    $totalPointsSum += $assignment['points'];
                    if ($assignment['score'] !== null) {
                        $completedCount++;
                        $totalScoreSum += $assignment['score'];
                    }
                } elseif ($assignment['student_status'] === 'in_progress') {
                    $stats['in_progress']++;
                } elseif ($assignment['urgency_status'] === 'overdue' && $assignment['student_status'] !== 'completed') {
                    $stats['overdue']++;
                }

                // Collect subjects
                if (!in_array($assignment['subject'], array_column($subjects, 'subject'))) {
                    $subjects[] = ['subject' => $assignment['subject'], 'total_assignments' => 0, 'completed_assignments' => 0];
                }
                // Update subject counts (this part needs to be done after collecting all subjects)
            }
            unset($assignment); // Break the reference

            // Recalculate subject counts after all assignments are processed
            foreach ($subjects as &$subject) {
                foreach ($assignments as $assignment) {
                    if ($assignment['subject'] === $subject['subject']) {
                        $subject['total_assignments']++;
                        if ($assignment['student_status'] === 'completed') {
                            $subject['completed_assignments']++;
                        }
                    }
                }
            }
            unset($subject);

            $stats['total_points'] = $totalPointsSum;
            $stats['average_score'] = $completedCount > 0 ? $totalScoreSum / $completedCount : 0;

        } else {
            $_SESSION['messages'] = ['error' => $assignmentsResponse['error'] ?? 'Failed to fetch assignments.'];
        }

        $status_filter = $_GET['status'] ?? 'all';
        $subject_filter = $_GET['subject'] ?? 'all';
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $this->render('assignments', [
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
}