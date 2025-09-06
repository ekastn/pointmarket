<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\BadgeService;

class BadgesController extends BaseController
{
    protected BadgeService $badgeService;

    public function __construct(ApiClient $apiClient, BadgeService $badgeService)
    {
        parent::__construct($apiClient);
        $this->badgeService = $badgeService;
    }

    public function index(): void
    {
        $search = $_GET['search'] ?? '';
        $page = (int) ($_GET['page'] ?? 1);
        $limit = 10;

        $response = $this->badgeService->getAllBadges($search, $page, $limit);

        if ($response !== null) {
            $badges = $response['data'];
            $meta = $response['meta'];

            $this->render('admin/badges', [
                'user' => $_SESSION['user_data'],
                'title' => 'Badges',
                'badges' => $badges,
                'search' => $search,
                'page' => $meta['page'],
                'limit' => $meta['limit'],
                'total_data' => $meta['total_records'],
                'total_pages' => $meta['total_pages'],
            ]);
        } else {
            $_SESSION['messages']['error'] = 'Failed to fetch badges.';
            $this->redirect('/dashboard');
        }
    }

    public function myBadges(): void
    {
        $badges = $this->badgeService->getMyBadges();

        $this->render('siswa/my_badges', [
            'user' => $_SESSION['user_data'],
            'title' => 'My Badges',
            'badges' => $badges ?? [],
        ]);
    }

    public function store(): void
    {
        $badgeData = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? null,
            'points_min' => isset($_POST['points_min']) && $_POST['points_min'] !== '' ? (int) $_POST['points_min'] : null,
        ];

        $result = $this->badgeService->createBadge($badgeData);

        if ($result !== null) {
            $this->redirect('/badges');
        } else {
            throw new \Exception('Failed to create badge.');
        }
        exit;
    }

    public function update(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $badgeData = [
            'title' => $input['title'] ?? null,
            'description' => $input['description'] ?? null,
            'points_min' => isset($input['points_min']) ? (int) $input['points_min'] : null,
        ];

        $result = $this->badgeService->updateBadge($id, $badgeData);

        if ($result !== null) {
            echo json_encode(['success' => true, 'message' => 'Badge updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => $_SESSION['api_error_message'] ?? 'Failed to update badge.']);
        }
        exit;
    }

    public function destroy(int $id): void
    {
        $result = $this->badgeService->deleteBadge($id);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Badge deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => $_SESSION['api_error_message'] ?? 'Failed to delete badge.']);
        }
        exit;
    }

    public function award(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $badgeId = $input['badge_id'] ?? 0;
        $userId = $input['user_id'] ?? 0;

        $result = $this->badgeService->awardBadge($badgeId, $userId);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Badge awarded successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => $_SESSION['api_error_message'] ?? 'Failed to award badge.']);
        }
        exit;
    }

    public function revoke(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $badgeId = $input['badge_id'] ?? 0;
        $userId = $input['user_id'] ?? 0;

        $result = $this->badgeService->revokeBadge($badgeId, $userId);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Badge revoked successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => $_SESSION['api_error_message'] ?? 'Failed to revoke badge.']);
        }
        exit;
    }
}
