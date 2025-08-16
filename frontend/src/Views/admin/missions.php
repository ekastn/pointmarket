<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Missions Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#missionModal" id="addMissionBtn">Add New Mission</button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Mission List</h6>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" id="missionSearch" class="form-control" placeholder="Search missions..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="table-responsive">
                <?php
                $renderer->includePartial('components/partials/tabel_missions', [
                    'missions' => $missions,
                    'role' => $role,
                ]);
                ?>
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                            <a class="page-link" href="/missions?page=<?= $i ?>&search=<?= htmlspecialchars($search) ?>"> <?= $i ?> </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Mission Modal -->
<div class="modal fade" id="missionModal" tabindex="-1" aria-labelledby="missionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="missionModalLabel">Add/Edit Mission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="missionForm">
                    <input type="hidden" id="missionId" name="id">
                    <div class="mb-3">
                        <label for="missionTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="missionTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="missionDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="missionDescription" name="description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="missionRewardPoints" class="form-label">Reward Points</label>
                        <input type="number" class="form-control" id="missionRewardPoints" name="reward_points">
                    </div>
                    <div class="mb-3">
                        <label for="missionMetadata" class="form-label">Metadata (JSON)</label>
                        <textarea class="form-control" id="missionMetadata" name="metadata"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Mission</button>
                </form>
            </div>
        </div>
    </div>
</div>
