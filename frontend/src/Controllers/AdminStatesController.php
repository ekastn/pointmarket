<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\AdminStatesService;

class AdminStatesController extends BaseController
{
    public function __construct(ApiClient $apiClient, private AdminStatesService $states)
    {
        parent::__construct($apiClient);
    }

    public function index(): void
    {
        // Derive offset from page when provided to support table pagination links
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

        $rawLimit = $_GET['limit'] ?? '20';
        if ($rawLimit === 'all') {
            $limit = 100000; // Arbitrary high number to fetch all
        } else {
            $limit = (int) $rawLimit;
            if ($limit <= 0) {
                $limit = 20;
            }
        }

        $offset = isset($_GET['page']) ? ($page - 1) * $limit : (int) ($_GET['offset'] ?? 0);

        $filters = [
            'q' => $_GET['q'] ?? '',
            'limit' => $rawLimit === 'all' ? 'all' : $limit,
            'offset' => $offset,
            'sort' => $_GET['sort'] ?? 'state asc',
        ];

        // API requires numeric limit
        $apiFilters = array_merge($filters, ['limit' => $limit]);
        $apiFilters = array_filter($apiFilters, fn($v) => $v !== '' && $v !== null);

        $data = $this->states->list($apiFilters) ?? ['states' => [], 'meta' => ['total' => 0, 'limit' => 20, 'offset' => 0]];
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $this->render('admin/recommendations/states/index', [
            'states' => $data['states'] ?? [],
            'meta' => $data['meta'] ?? ['total' => 0, 'limit' => 20, 'offset' => 0],
            'filters' => $filters,
            'messages' => $messages,
        ]);
    }

    public function store(): void
    {
        $payload = [
            'state' => trim($_POST['state'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
        ];
        $resp = $this->states->create($payload);
        $_SESSION['messages'] = !empty($resp['success']) ? ['success' => 'State created'] : ['error' => ($resp['error'] ?? ($resp['message'] ?? 'Create failed'))];
        $this->redirect('/admin/recommendations/states');
    }

    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['messages'] = ['error' => 'Invalid id'];
            $this->redirect('/admin/recommendations/states');
            return;
        }
        $payload = [];
        if (isset($_POST['state']))
            $payload['state'] = trim($_POST['state']);
        if (isset($_POST['description']))
            $payload['description'] = trim($_POST['description']);
        if (isset($_POST['update_items']))
            $payload['update_items'] = (bool) $_POST['update_items'];
        $resp = $this->states->update($id, $payload);
        $_SESSION['messages'] = !empty($resp['success']) ? ['success' => 'State updated'] : ['error' => ($resp['error'] ?? ($resp['message'] ?? 'Update failed'))];
        $this->redirect('/admin/recommendations/states');
    }

    public function destroy(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $force = isset($_POST['force']) && $_POST['force'] == '1';
        if ($id <= 0) {
            $_SESSION['messages'] = ['error' => 'Invalid id'];
            $this->redirect('/admin/recommendations/states');
            return;
        }
        $resp = $this->states->delete($id, $force);
        $_SESSION['messages'] = !empty($resp['success']) ? ['success' => 'State deleted'] : ['error' => ($resp['error'] ?? ($resp['message'] ?? 'Delete failed'))];
        $this->redirect('/admin/recommendations/states');
    }

    public function export(): void
    {
        $response = $this->states->export();
        if ($response && $response->getStatusCode() === 200) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="unique_states.csv"');
            echo $response->getBody();
            exit;
        }
        $_SESSION['messages'] = ['error' => 'Export failed'];
        $this->redirect('/admin/recommendations/states');
    }
}
