<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\AdminItemsService;

class AdminItemsController extends BaseController
{
    private AdminItemsService $itemsService;

    public function __construct(ApiClient $apiClient, AdminItemsService $itemsService)
    {
        parent::__construct($apiClient);
        $this->itemsService = $itemsService;
    }

    public function index(): void
    {
        // Handle quick actions via GET to support table action links
        $do = $_GET['do'] ?? '';
        if ($do === 'toggle' && isset($_GET['id'], $_GET['to'])) {
            $id = (int) $_GET['id'];
            $to = (int) $_GET['to'] === 1;
            if ($id > 0) {
                $resp = $this->itemsService->toggle($id, $to);
                $_SESSION['messages'] = !empty($resp['success']) ? ['success' => 'Item toggled'] : ['error' => ($resp['error'] ?? ($resp['message'] ?? 'Toggle failed'))];
                // Redirect without query action params
                header('Location: /admin/recommendations/items');
                exit;
            }
        } elseif ($do === 'delete' && isset($_GET['id'])) {
            $id = (int) $_GET['id'];
            if ($id > 0) {
                $resp = $this->itemsService->delete($id, false);
                $_SESSION['messages'] = !empty($resp['success']) ? ['success' => 'Item deleted'] : ['error' => ($resp['error'] ?? ($resp['message'] ?? 'Delete failed'))];
                header('Location: /admin/recommendations/items');
                exit;
            }
        }

        $filters = [
            'state' => $_GET['state'] ?? '',
            'state_like' => $_GET['state_like'] ?? '',
            'action_code' => $_GET['action_code'] ?? '',
            'ref_type' => $_GET['ref_type'] ?? '',
            'ref_id' => $_GET['ref_id'] ?? '',
            'active' => $_GET['active'] ?? '',
            'limit' => $_GET['limit'] ?? 20,
            'offset' => $_GET['offset'] ?? 0,
        ];
        $filters = array_filter($filters, fn($v) => $v !== '' && $v !== null);

        // Separate filters for API (numeric limit) vs View (string "all")
        $apiFilters = $filters;
        if (isset($apiFilters['limit']) && $apiFilters['limit'] === 'all') {
            $apiFilters['limit'] = 100000;
        }

        $data = $this->itemsService->list($apiFilters) ?? ['items' => [], 'meta' => ['total' => 0, 'limit' => 20, 'offset' => 0]];
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $this->render('admin/recommendations/items/index', [
            'items' => $data['items'] ?? [],
            'stats' => $data['stats'] ?? [],
            'meta' => $data['meta'] ?? ['total' => 0, 'limit' => 20, 'offset' => 0],
            'filters' => $filters,
            'messages' => $messages,
        ]);
    }

    public function store(): void
    {
        $payload = [
            'state' => $_POST['state'] ?? '',
            'action_code' => isset($_POST['action_code']) ? (int) $_POST['action_code'] : null,
            'ref_type' => $_POST['ref_type'] ?? '',
            'ref_id' => isset($_POST['ref_id']) ? (int) $_POST['ref_id'] : null,
            'is_active' => isset($_POST['is_active']) ? (bool) $_POST['is_active'] : true,
        ];

        $resp = $this->itemsService->create($payload);
        if (!empty($resp['success'])) {
            $_SESSION['messages'] = ['success' => 'Item created'];
        } else {
            $_SESSION['messages'] = ['error' => $resp['error'] ?? ($resp['message'] ?? 'Create failed')];
        }
        $this->redirect('/admin/recommendations/items');
    }

    public function update(): void
    {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            $_SESSION['messages'] = ['error' => 'Invalid item id'];
            $this->redirect('/admin/recommendations/items');
            return;
        }
        $payload = [];
        foreach (['state', 'action_code', 'ref_type', 'ref_id', 'is_active'] as $k) {
            if (isset($_POST[$k]) && $_POST[$k] !== '') {
                $payload[$k] = $_POST[$k];
            }
        }
        if (isset($payload['action_code']))
            $payload['action_code'] = (int) $payload['action_code'];
        if (isset($payload['ref_id']))
            $payload['ref_id'] = (int) $payload['ref_id'];
        if (isset($payload['is_active']))
            $payload['is_active'] = (bool) $payload['is_active'];

        $resp = $this->itemsService->update($id, $payload);
        if (!empty($resp['success'])) {
            $_SESSION['messages'] = ['success' => 'Item updated'];
        } else {
            $_SESSION['messages'] = ['error' => $resp['error'] ?? ($resp['message'] ?? 'Update failed')];
        }
        $this->redirect('/admin/recommendations/items');
    }

    public function toggle(): void
    {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $isActive = isset($_POST['is_active']) ? (bool) $_POST['is_active'] : false;
        if ($id <= 0) {
            $_SESSION['messages'] = ['error' => 'Invalid item id'];
            $this->redirect('/admin/recommendations/items');
            return;
        }
        $resp = $this->itemsService->toggle($id, $isActive);
        if (!empty($resp['success'])) {
            $_SESSION['messages'] = ['success' => 'Item toggled'];
        } else {
            $_SESSION['messages'] = ['error' => $resp['error'] ?? ($resp['message'] ?? 'Toggle failed')];
        }
        $this->redirect('/admin/recommendations/items');
    }

    public function destroy(): void
    {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $force = isset($_POST['force']) ? (bool) $_POST['force'] : false;
        if ($id <= 0) {
            $_SESSION['messages'] = ['error' => 'Invalid item id'];
            $this->redirect('/admin/recommendations/items');
            return;
        }
        if (!empty($resp['success'])) {
            $_SESSION['messages'] = ['success' => 'Item deleted'];
        } else {
            $_SESSION['messages'] = ['error' => $resp['error'] ?? ($resp['message'] ?? 'Delete failed')];
        }
        $this->redirect('/admin/recommendations/items');
    }

    public function export(): void
    {
        $response = $this->itemsService->export();
        if ($response && $response->getStatusCode() === 200) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="recommendation_items.csv"');
            echo $response->getBody();
            exit;
        }
        $_SESSION['messages'] = ['error' => 'Export failed'];
        $this->redirect('/admin/recommendations/items');
    }

    // Typeahead proxy: states
    public function typeaheadStates(): void
    {
        header('Content-Type: application/json');
        $q = $_GET['q'] ?? '';
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
        $resp = $this->itemsService->searchStates($q, $limit);
        if (!empty($resp['success']) && isset($resp['data']['states'])) {
            echo json_encode(['states' => $resp['data']['states']]);
            return;
        }
        http_response_code(200);
        echo json_encode(['states' => []]);
    }

    // Typeahead proxy: refs
    public function typeaheadRefs(): void
    {
        header('Content-Type: application/json');
        $t = $_GET['ref_type'] ?? '';
        $q = trim($_GET['q'] ?? '');
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
        if ($t === '') {
            echo json_encode(['refs' => []]);
            return;
        }

        $refs = [];
        if ($t === 'product') {
            // Use backend products endpoint (supports search)
            $resp = $this->apiClient->request('GET', '/api/v1/products', ['query' => ['search' => $q, 'page' => 1, 'limit' => max(1, $limit)]]);
            if (!empty($resp['success']) && isset($resp['data'])) {
                foreach ($resp['data'] as $row) {
                    $refs[] = ['id' => (int) ($row['id'] ?? 0), 'title' => (string) ($row['name'] ?? '')];
                }
            }
        } elseif ($t === 'mission') {
            // Use backend missions endpoint (no search), filter client-side
            $resp = $this->apiClient->request('GET', '/api/v1/missions');
            if (!empty($resp['success']) && isset($resp['data'])) {
                $acc = [];
                foreach ($resp['data'] as $row) {
                    $title = (string) ($row['title'] ?? '');
                    if ($q === '' || stripos($title, $q) !== false) {
                        $acc[] = ['id' => (int) ($row['id'] ?? 0), 'title' => $title];
                        if (count($acc) >= $limit)
                            break;
                    }
                }
                $refs = $acc;
            }
        } else {
            // Unsupported types currently (reward/punishment/coaching): no suggestions
            $refs = [];
        }
        echo json_encode(['refs' => $refs]);
    }
}
