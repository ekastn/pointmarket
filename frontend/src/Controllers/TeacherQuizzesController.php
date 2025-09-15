<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\QuizService;
use App\Services\CourseService;

class TeacherQuizzesController extends BaseController
{
    public function __construct(
        ApiClient $apiClient,
        private QuizService $quizService,
        private CourseService $courseService,
    ) {
        parent::__construct($apiClient);
    }

    public function index(): void
    {
        $data = $this->quizService->getQuizzes();
        $quizzes = $data['quizzes'] ?? [];
        $this->render('guru/quizzes_list', [
            'title' => 'Kelola Kuis',
            'quizzes' => $quizzes,
        ]);
    }

    public function create(): void
    {
        $coursesResp = $this->courseService->getAllCourses('', 'guru', 1, 100);
        $courses = $coursesResp['data']['courses'] ?? ($coursesResp['courses'] ?? []);
        $this->render('guru/quizzes_form', [
            'title' => 'Buat Kuis',
            'quiz' => null,
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
            'duration_minutes' => !empty($input['duration_minutes']) ? (int)$input['duration_minutes'] : null,
            'status' => $input['status'] ?? 'draft',
        ];
        $res = $this->quizService->createQuiz($payload);
        if ($res) { $_SESSION['messages']['success']='Kuis berhasil dibuat.'; $this->redirect('/guru/quizzes'); return; }
        $this->render('guru/quizzes_form', [ 'title' => 'Buat Kuis', 'quiz' => $payload, 'error' => 'Gagal menyimpan kuis.' ]);
    }

    public function edit(int $id): void
    {
        $quiz = $this->quizService->getQuizById($id) ?? [];
        $coursesResp = $this->courseService->getAllCourses('', 'guru', 1, 100);
        $courses = $coursesResp['data']['courses'] ?? ($coursesResp['courses'] ?? []);
        $this->render('guru/quizzes_form', [ 'title' => 'Edit Kuis', 'quiz' => $quiz, 'courses' => $courses ]);
    }

    public function update(int $id): void
    {
        $input = $_POST;
        $payload = [
            'title' => $input['title'] ?? null,
            'description' => $input['description'] ?? null,
            'course_id' => isset($input['course_id']) ? (int)$input['course_id'] : null,
            'reward_points' => isset($input['reward_points']) ? (int)$input['reward_points'] : null,
            'duration_minutes' => isset($input['duration_minutes']) ? (int)$input['duration_minutes'] : null,
            'status' => $input['status'] ?? null,
        ];
        $res = $this->quizService->updateQuiz($id, $payload);
        if ($res) { $_SESSION['messages']['success']='Kuis berhasil diperbarui.'; $this->redirect('/guru/quizzes'); return; }
        $this->render('guru/quizzes_form', [ 'title' => 'Edit Kuis', 'quiz' => array_merge(['id'=>$id], $payload), 'error' => 'Gagal memperbarui kuis.' ]);
    }

    public function destroy(int $id): void
    {
        $this->quizService->deleteQuiz($id);
        $_SESSION['messages']['success']='Kuis berhasil dihapus.';
        $this->redirect('/guru/quizzes');
    }
}
