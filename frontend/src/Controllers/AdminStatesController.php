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
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = (int)($_GET['limit'] ?? 20);
        if ($limit <= 0) { $limit = 20; }
        $offset = isset($_GET['page']) ? ($page - 1) * $limit : (int)($_GET['offset'] ?? 0);

        $filters = [
            'q' => $_GET['q'] ?? '',
            'limit' => $limit,
            'offset' => $offset,
            'sort' => $_GET['sort'] ?? 'state asc',
        ];
        $filters = array_filter($filters, fn($v) => $v !== '' && $v !== null);
        $data = $this->states->list($filters) ?? ['states' => [], 'meta' => ['total'=>0,'limit'=>20,'offset'=>0]];
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $this->render('admin/recommendations/states/index', [
            'states' => $data['states'] ?? [],
            'meta' => $data['meta'] ?? ['total'=>0,'limit'=>20,'offset'=>0],
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
        $_SESSION['messages'] = !empty($resp['success']) ? ['success'=>'State created'] : ['error'=>($resp['error'] ?? ($resp['message'] ?? 'Create failed'))];
        $this->redirect('/admin/recommendations/states');
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { $_SESSION['messages']=['error'=>'Invalid id']; $this->redirect('/admin/recommendations/states'); return; }
        $payload = [];
        if (isset($_POST['state'])) $payload['state'] = trim($_POST['state']);
        if (isset($_POST['description'])) $payload['description'] = trim($_POST['description']);
        if (isset($_POST['update_items'])) $payload['update_items'] = (bool)$_POST['update_items'];
        $resp = $this->states->update($id, $payload);
        $_SESSION['messages'] = !empty($resp['success']) ? ['success'=>'State updated'] : ['error'=>($resp['error'] ?? ($resp['message'] ?? 'Update failed'))];
        $this->redirect('/admin/recommendations/states');
    }

    public function destroy(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $force = isset($_POST['force']) && $_POST['force'] == '1';
        if ($id <= 0) { $_SESSION['messages']=['error'=>'Invalid id']; $this->redirect('/admin/recommendations/states'); return; }
        $resp = $this->states->delete($id, $force);
        $_SESSION['messages'] = !empty($resp['success']) ? ['success'=>'State deleted'] : ['error'=>($resp['error'] ?? ($resp['message'] ?? 'Delete failed'))];
        $this->redirect('/admin/recommendations/states');
    }
}
