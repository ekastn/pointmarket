<?php

namespace App\Services;

use App\Core\ApiClient;

class CourseService
{
    protected ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getAllCourses(string $search = '', string $role = '', int $page = 1, int $limit = 10): ?array
    {
        $queryParams = [
            'page' => $page,
            'limit' => $limit,
        ];
        if (!empty($search)) {
            $queryParams['search'] = $search;
        }
        if (!empty($role)) {
            $queryParams['role'] = $role;
        }

        $response = $this->apiClient->request('GET', '/api/v1/courses', ['query' => $queryParams]);

        if ($response['success']) {
            return $response;
        }

        return null;
    }

    public function getCourseById(int $id): ?array
    {
        $response = $this->apiClient->request('GET', '/api/v1/courses/' . $id);

        if ($response['success']) {
            return $response['data'];
        }

        return null;
    }

    public function createCourse(array $courseData): ?array
    {
        $response = $this->apiClient->request('POST', '/api/v1/courses', ['json' => $courseData]);

        if ($response['success']) {
            return $response['data'];
        } else {
            $_SESSION['api_error_message'] = $response['message'] ?? 'Failed to create course.';
        }

        return null;
    }

    public function updateCourse(int $id, array $courseData): ?array
    {
        $response = $this->apiClient->request('PUT', '/api/v1/courses/' . $id, ['json' => $courseData]);

        if ($response['success']) {
            return $response['data'];
        } else {
            $_SESSION['api_error_message'] = $response['message'] ?? 'Failed to update course.';
        }

        return null;
    }

    public function deleteCourse(int $id): ?bool
    {
        $response = $this->apiClient->request('DELETE', '/api/v1/courses/' . $id);

        if ($response['success']) {
            return true;
        } else {
            $_SESSION['api_error_message'] = $response['message'] ?? 'Failed to delete course.';
        }

        return false;
    }

    public function enrollStudent(int $courseId, int $userId): ?bool
    {
        $response = $this->apiClient->request('POST', '/api/v1/courses/' . $courseId . '/enroll', ['json' => ['user_id' => $userId, 'course_id' => $courseId]]);

        if ($response['success']) {
            return true;
        } else {
            $_SESSION['api_error_message'] = $response['message'] ?? 'Failed to enroll student.';
        }

        return false;
    }

    public function unenrollStudent(int $courseId, int $userId): ?bool
    {
        $response = $this->apiClient->request('DELETE', '/api/v1/courses/' . $courseId . '/unenroll', ['json' => ['user_id' => $userId, 'course_id' => $courseId]]);

        if ($response['success']) {
            return true;
        } else {
            $_SESSION['api_error_message'] = $response['message'] ?? 'Failed to unenroll student.';
        }

        return false;
    }
}
