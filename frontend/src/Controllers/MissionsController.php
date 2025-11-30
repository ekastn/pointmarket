<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\MissionService;

class MissionsController extends BaseController
{
    protected MissionService $missionService;

    public function __construct(ApiClient $apiClient, MissionService $missionService)
    {
        parent::__construct($apiClient);
        $this->missionService = $missionService;
    }

    public function index(): void
    {
        $user = $_SESSION['user_data'] ?? null;
        $role = $user['role'] ?? '';
        $userId = $user['id'] ?? 0;

        $search = $_GET['search'] ?? '';
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 10);

        $missions = [];
        $totalMissions = 0;
        $totalPages = 0;

        if ($role === 'admin' || $role === 'guru') {
            $response = $this->missionService->getAllMissions($search, $page, $limit);
            if ($response && $response['success']) {
                $missions = $response['data']['missions'] ?? [];
                $totalMissions = $response['data']['total'] ?? 0;
                $totalPages = ceil($totalMissions / $limit);
            }

            // If admin, also fetch all user mission progress
            if ($role === 'admin') {
                $progressPage = (int) ($_GET['progress_page'] ?? 1);
                $progressSearch = $_GET['progress_search'] ?? '';
                $progressLimit = 10;

                $progressResponse = $this->missionService->getAllUserMissions($progressPage, $progressLimit, $progressSearch);
                $progressData = $progressResponse['data'] ?? [];
                $progressMeta = $progressResponse['meta'] ?? [];
            }
        } elseif ($role === 'siswa') {
            $response = $this->missionService->getUserMissions($userId);
            if ($response && $response['success']) {
                $missions = $response['data']['user_missions'] ?? [];
                $totalMissions = $response['data']['total'] ?? 0;
                $totalPages = ceil($totalMissions / $limit);
            }
        }

        $this->render(
            ($role === 'admin' || $role === 'guru') ? 'admin/missions' : 'siswa/my-missions',
            [
                'title' => 'Missions',
                'missions' => $missions,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalMissions' => $totalMissions,
                'search' => $search,
                'role' => $role,
                'progress' => $progressData ?? [],
                'progress_meta' => $progressMeta ?? [],
                'progress_search' => $progressSearch ?? '',
            ]
        );
    }

    public function store(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $missionData = [
            'title' => $input['title'] ?? '',
            'description' => $input['description'] ?? null,
            'reward_points' => isset($input['reward_points']) ? (int) $input['reward_points'] : null,
            'metadata' => isset($input['metadata']) ? $input['metadata'] : null, // metadata is already decoded JSON
        ];

        $response = $this->missionService->createMission($missionData);

        if ($response) {
            echo json_encode(['success' => true, 'message' => 'Mission created successfully!']);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => $_SESSION['api_error_message'] ?? 'Failed to create mission.']);
            exit;
        }
    }

    public function update(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $missionData = [
            'title' => $input['title'] ?? '',
            'description' => $input['description'] ?? null,
            'reward_points' => isset($input['reward_points']) ? (int) $input['reward_points'] : null,
            'metadata' => isset($input['metadata']) ? $input['metadata'] : null, // metadata is already decoded JSON
        ];

        $response = $this->missionService->updateMission($id, $missionData);

        if ($response) {
            echo json_encode(['success' => true, 'message' => 'Mission updated successfully!']);
            exit;
        }

        echo json_encode(['success' => false, 'message' => $_SESSION['api_error_message'] ?? 'Failed to update mission.']);
        exit;
    }

    public function destroy(int $id): void
    {
        $success = $this->missionService->deleteMission($id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Mission deleted successfully!']);
            exit;
        }

        echo json_encode(['success' => false, 'message' => $_SESSION['api_error_message'] ?? 'Failed to delete mission.']);
        exit;
    }

    public function start(int $id): void
    {
        $userId = $_SESSION['user_data']['id'] ?? 0;
        if (! $userId) {
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit;
        }

        $response = $this->missionService->startMission($id, $userId);

        if ($response) {
            echo json_encode(['success' => true, 'message' => 'Mission started successfully!']);
            exit;
        }

        echo json_encode(['success' => false, 'message' => $_SESSION['api_error_message'] ?? 'Failed to start mission.']);
        exit;
    }

    public function updateStatus(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $statusData = [
            'status' => $input['status'] ?? '',
            'progress' => isset($input['progress']) ? (int) $input['progress'] : null,
            'completed_at' => ($input['status'] === 'completed') ? date('Y-m-d H:i:s') : null,
        ];

        $response = $this->missionService->updateUserMissionStatus($id, $statusData);

        if ($response) {
            echo json_encode(['success' => true, 'message' => 'Mission status updated successfully!']);
            exit;
        }

        echo json_encode(['success' => false, 'message' => $_SESSION['api_error_message'] ?? 'Failed to update mission status.']);
        exit;
    }

    public function show(int $id): void
    {
        $user = $_SESSION['user_data'] ?? null;
        $userId = $user['id'] ?? 0;

        // Fetch mission detail
        $mission = $this->missionService->getMissionById($id) ?? [];

        // Fetch user missions and find current one (if exists)
        $userMission = null;
        $resp = $this->missionService->getUserMissions($userId);
        if ($resp && ($resp['success'] ?? false)) {
            $list = $resp['data']['user_missions'] ?? [];
            foreach ($list as $um) {
                if ((int)($um['mission_id'] ?? 0) === $id) {
                    $userMission = $um;
                    break;
                }
            }
        }

        $this->render('siswa/mission_detail', [
            'title' => 'Detail Misi',
            'mission' => $mission,
            'userMission' => $userMission,
        ]);
    }
}
