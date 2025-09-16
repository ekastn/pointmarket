<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\AssignmentService;

class AssignmentsController extends BaseController
{
    protected AssignmentService $assignmentService;

    public function __construct(ApiClient $apiClient, AssignmentService $assignmentService)
    {
        parent::__construct($apiClient);
        $this->assignmentService = $assignmentService;
    }

    public function index(): void
    {
        $assignments = [];
        $statusMap = [];

        $userId = $_SESSION['user_data']['id'] ?? null;
        $data = $this->assignmentService->getAssignments(null, $userId);

        if ($data !== null) {
            $assignments = $data['assignments'] ?? ($data['student_assignments'] ?? []);
        } else {
            $_SESSION['messages'] = ['error' => 'Failed to fetch assignments.'];
        }

        // Build student assignment status map keyed by assignment_id
        if ($userId) {
            $saData = $this->assignmentService->getStudentAssignments($userId);
            $studentAssignments = $saData['student_assignments'] ?? [];
            foreach ($studentAssignments as $sa) {
                $aid = (int)($sa['assignment_id'] ?? 0);
                if ($aid > 0 && !empty($sa['status'])) {
                    $statusMap[$aid] = $sa['status'];
                }
            }
        }

        $this->render('siswa/assignments', [
            'title' => 'Assignments',
            'assignments' => $assignments,
            'studentStatusMap' => $statusMap,
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

        $result = $this->assignmentService->startAssignment($assignmentId);

        if ($result !== null) {
            echo json_encode(['success' => true, 'message' => 'Assignment started successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to start assignment.']);
        }
    }

    public function show(int $id): void
    {
        $userId = $_SESSION['user_data']['id'] ?? null;
        $assignment = $this->assignmentService->getAssignmentByID($id, $userId);

        // student status map (single assignment)
        $status = null;
        if ($userId) {
            $saData = $this->assignmentService->getStudentAssignments($userId);
            $studentAssignments = $saData['student_assignments'] ?? [];
            foreach ($studentAssignments as $sa) {
                if ((int)($sa['assignment_id'] ?? 0) === $id) {
                    $status = $sa['status'] ?? null;
                    break;
                }
            }
        }

        $this->render('siswa/assignment_detail', [
            'title' => ($assignment['title'] ?? $assignment['data']['title'] ?? 'Detail Tugas'),
            'assignment' => $assignment,
            'status' => $status,
        ]);
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

        $result = $this->assignmentService->submitAssignment($assignmentId, $submissionText);

        if ($result !== null) {
            echo json_encode(['success' => true, 'message' => 'Assignment submitted successfully!', 'score' => $result['score'] ?? null]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit assignment.']);
        }
    }
}
