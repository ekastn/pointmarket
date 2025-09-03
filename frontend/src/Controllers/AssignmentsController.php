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

        $data = $this->assignmentService->getAssignments(null, $_SESSION['user_data']['id']);

        if ($data !== null) {
            $assignments = $data['assignments'] ?? [];
            error_log(print_r($assignments, true));
        } else {
            $_SESSION['messages'] = ['error' => 'Failed to fetch assignments.'];
        }

        $this->render('siswa/assignments', [
            'title' => 'Assignments',
            'assignments' => $assignments,
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
