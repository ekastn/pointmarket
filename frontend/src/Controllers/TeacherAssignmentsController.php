<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\AssignmentService;
use App\Services\CourseService;

class TeacherAssignmentsController extends BaseController
{
    public function __construct(
        ApiClient $apiClient,
        private AssignmentService $assignmentService,
        private CourseService $courseService,
    ) {
        parent::__construct($apiClient);
    }

    public function index(): void
    {
        $data = $this->assignmentService->getAssignments();
        $assignments = $data['assignments'] ?? [];
        $this->render('guru/assignments_list', [
            'title' => 'Kelola Tugas',
            'assignments' => $assignments,
        ]);
    }

    public function submissions(int $id): void
    {
        $resp = $this->assignmentService->getSubmissions($id) ?? [];
        $submissions = $resp['student_assignments'] ?? [];
        $this->render('guru/assignment_submissions', [
            'title' => 'Submissions',
            'assignment_id' => $id,
            'submissions' => $submissions,
        ]);
    }

    public function grade(int $id, int $studentAssignmentId): void
    {
        $score = isset($_POST['score']) && $_POST['score'] !== '' ? (float)$_POST['score'] : null;
        $feedback = $_POST['feedback'] ?? null;
        $ok = $this->assignmentService->gradeSubmission($id, $studentAssignmentId, $score, $feedback);
        if ($ok) {
            $_SESSION['messages']['success'] = 'Nilai tersimpan.';
        } else {
            $_SESSION['messages']['error'] = 'Gagal menyimpan nilai';
        }
        $this->redirect('/guru/assignments/' . $id . '/submissions');
    }

    public function create(): void
    {
        $coursesResp = $this->courseService->getAllCourses('', 'guru', 1, 100);
        $courses = $coursesResp['data']['courses'] ?? ($coursesResp['courses'] ?? []);
        $this->render('guru/assignments_form', [
            'title' => 'Buat Tugas',
            'assignment' => null,
            'courses' => $courses,
        ]);
    }

    public function store(): void
    {
        $input = $_POST;
        $payload = [
            'title' => $input['title'] ?? '',
            'description' => $input['description'] ?? null,
            'course_id' => (int)($input['course_id'] ?? 0),
            'reward_points' => (int)($input['reward_points'] ?? 0),
            'due_date' => !empty($input['due_date']) ? date('c', strtotime($input['due_date'])) : null,
            'status' => $input['status'] ?? 'draft',
        ];
        $res = $this->assignmentService->createAssignment($payload);
        if ($res) {
            $_SESSION['messages']['success'] = 'Tugas berhasil dibuat.';
            $this->redirect('/guru/assignments');
            return;
        }
        $this->render('guru/assignments_form', [
            'title' => 'Buat Tugas',
            'assignment' => $payload,
            'error' => 'Gagal menyimpan tugas (periksa kepemilikan & isian).',
        ]);
    }

    public function edit(int $id): void
    {
        $assignment = $this->assignmentService->getAssignmentByID($id) ?? null;
        $coursesResp = $this->courseService->getAllCourses('', 'guru', 1, 100);
        $courses = $coursesResp['data']['courses'] ?? ($coursesResp['courses'] ?? []);
        $this->render('guru/assignments_form', [
            'title' => 'Edit Tugas',
            'assignment' => $assignment,
            'courses' => $courses,
        ]);
    }

    public function update(int $id): void
    {
        $input = $_POST;
        $payload = [
            'title' => $input['title'] ?? null,
            'description' => $input['description'] ?? null,
            'course_id' => isset($input['course_id']) ? (int)$input['course_id'] : null,
            'reward_points' => isset($input['reward_points']) ? (int)$input['reward_points'] : null,
            'due_date' => !empty($input['due_date']) ? date('c', strtotime($input['due_date'])) : null,
            'status' => $input['status'] ?? null,
        ];
        $res = $this->assignmentService->updateAssignment($id, $payload);
        if ($res) {
            $_SESSION['messages']['success'] = 'Tugas berhasil diperbarui.';
            $this->redirect('/guru/assignments');
            return;
        }
        $this->render('guru/assignments_form', [
            'title' => 'Edit Tugas',
            'assignment' => array_merge(['id'=>$id], $payload),
            'error' => 'Gagal memperbarui tugas (periksa kepemilikan & isian).',
        ]);
    }

    public function destroy(int $id): void
    {
        $this->assignmentService->deleteAssignment($id);
        $_SESSION['messages']['success'] = 'Tugas berhasil dihapus.';
        $this->redirect('/guru/assignments');
    }
}
