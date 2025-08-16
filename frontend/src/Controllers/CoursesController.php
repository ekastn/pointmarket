<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\CourseService;

class CoursesController extends BaseController
{
    protected CourseService $courseService;

    public function __construct(ApiClient $apiClient, CourseService $courseService)
    {
        parent::__construct($apiClient);
        $this->courseService = $courseService;
    }

    public function index(): void
    {
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? ''; // This 'role' parameter might not be directly used for courses, but kept for consistency
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;

        // Fetch paginated data from the service
        $response = $this->courseService->getAllCourses($search, $role, $page, $limit);

        if ($response !== null) {
            $courses = $response['data'];
            $meta = $response['meta'];

            $total_data = $meta['total_records'];
            $start = ($page - 1) * $limit + 1;
            $end = min($start + $limit - 1, $total_data);

            // Determine which view to render based on user role
            $userRole = $_SESSION['user_data']['role'] ?? '';
            if ($userRole === 'admin' || $userRole === 'guru') {
                $this->render('admin/courses', [
                    'user' => $_SESSION['user_data'],
                    'title' => 'Courses',
                    'courses' => $courses,
                    'search' => $search,
                    'role' => $role,
                    'page' => $meta['page'],
                    'limit' => $meta['limit'],
                    'total_data' => $total_data,
                    'total_pages' => $meta['total_pages'],
                    'start' => $start,
                    'end' => $end,
                ]);
            } elseif ($userRole === 'siswa') {
                $this->render('siswa/courses', [
                    'user' => $_SESSION['user_data'],
                    'title' => 'My Courses',
                    'courses' => $courses,
                    'search' => $search,
                    'page' => $meta['page'],
                    'limit' => $meta['limit'],
                    'total_data' => $total_data,
                    'total_pages' => $meta['total_pages'],
                    'start' => $start,
                    'end' => $end,
                ]);
            } else {
                $_SESSION['messages']['error'] = 'Unauthorized access.';
                $this->redirect('/login');
            }
        } else {
            $_SESSION['messages']['error'] = 'Failed to fetch courses.';
            $this->redirect('/dashboard');
        }
    }

    public function create(): void
    {
        // Render a form for creating a new course
        $this->render('admin/courses_create', [
            'user' => $_SESSION['user_data'],
            'title' => 'Create Course',
        ]);
    }

    public function store(): void
    {
        $title = $_POST['title'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $description = $_POST['description'] ?? null;
        $ownerId = $_SESSION['user_data']['id'] ?? 0; // Owner is current authenticated user
        $metadata = $_POST['metadata'] ?? '{}'; // Assuming metadata is JSON string

        if (empty($title) || empty($slug) || empty($ownerId)) {
            $_SESSION['messages']['error'] = 'Title, Slug, and Owner are required.';
            $this->redirect('/courses/create');
            return;
        }

        $courseData = [
            'title' => $title,
            'slug' => $slug,
            'description' => $description,
            'owner_id' => (int)$ownerId,
            'metadata' => json_decode($metadata, true), // Decode JSON string to array
        ];

        $result = $this->courseService->createCourse($courseData);

        if ($result !== null) {
            $_SESSION['messages']['success'] = 'Course created successfully!';
            $this->redirect('/courses');
        } else {
            $errorMessage = $_SESSION['api_error_message'] ?? 'Failed to create course.';
            $_SESSION['messages']['error'] = $errorMessage;
            unset($_SESSION['api_error_message']);
            $this->redirect('/courses/create');
        }
    }

    public function edit(int $id): void
    {
        $course = $this->courseService->getCourseById($id);

        if ($course === null) {
            $_SESSION['messages']['error'] = 'Course not found.';
            $this->redirect('/courses');
            return;
        }

        $this->render('admin/courses_edit', [
            'user' => $_SESSION['user_data'],
            'title' => 'Edit Course',
            'course' => $course,
        ]);
    }

    public function update(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $title = $input['title'] ?? null;
        $slug = $input['slug'] ?? null;
        $description = $input['description'] ?? null;
        $metadata = $input['metadata'] ?? null; // Assuming metadata is already an array/object

        if (empty($id) || (empty($title) && empty($slug) && empty($description) && empty($metadata))) {
            echo json_encode(['success' => false, 'message' => 'Invalid input or no data to update.']);
            return;
        }

        $courseData = [
            'title' => $title,
            'slug' => $slug,
            'description' => $description,
            'metadata' => $metadata,
        ];

        $result = $this->courseService->updateCourse($id, $courseData);

        if ($result !== null) {
            echo json_encode(['success' => true, 'message' => 'Course updated successfully!']);
        } else {
            $errorMessage = $_SESSION['api_error_message'] ?? 'Failed to update course.';
            echo json_encode(['success' => false, 'message' => $errorMessage]);
            unset($_SESSION['api_error_message']);
        }
        return;
    }

    public function destroy(int $id): void
    {
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Invalid course ID.']);
            return;
        }

        $result = $this->courseService->deleteCourse($id);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Course deleted successfully!']);
        } else {
            $errorMessage = $_SESSION['api_error_message'] ?? 'Failed to delete course.';
            echo json_encode(['success' => false, 'message' => $errorMessage]);
            unset($_SESSION['api_error_message']);
        }
        return;
    }

    public function enroll(int $id): void
    {
        $userId = $_SESSION['user_data']['id'] ?? 0;

        if (empty($id) || empty($userId)) {
            $_SESSION['messages']['error'] = 'Invalid course or user ID.';
            $this->redirect('/courses');
            return;
        }

        $result = $this->courseService->enrollStudent($id, $userId);

        if ($result) {
            $_SESSION['messages']['success'] = 'Successfully enrolled in course!';
        } else {
            $errorMessage = $_SESSION['api_error_message'] ?? 'Failed to enroll in course.';
            $_SESSION['messages']['error'] = $errorMessage;
            unset($_SESSION['api_error_message']);
        }
        $this->redirect('/my-courses');
    }

    public function unenroll(int $id): void
    {
        $userId = $_SESSION['user_data']['id'] ?? 0;

        if (empty($id) || empty($userId)) {
            $_SESSION['messages']['error'] = 'Invalid course or user ID.';
            $this->redirect('/my-courses');
            return;
        }

        $result = $this->courseService->unenrollStudent($id, $userId);

        if ($result) {
            $_SESSION['messages']['success'] = 'Successfully unenrolled from course!';
        } else {
            $errorMessage = $_SESSION['api_error_message'] ?? 'Failed to unenroll from course.';
            $_SESSION['messages']['error'] = $errorMessage;
            unset($_SESSION['api_error_message']);
        }
        $this->redirect('/my-courses');
    }
}
