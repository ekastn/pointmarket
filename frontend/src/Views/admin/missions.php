<div class="container-fluid mt-4">
    <?php $renderer->includePartial('components/partials/page_title', [
        'icon' => 'fas fa-trophy',
        'title' => 'Kelola Misi',
        'right' => '<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#missionModal" id="addMissionBtn"><i class="fas fa-plus"></i> Tambah Misi</button>'
    ]); ?>

    <?php
    $renderer->includePartial('components/partials/table_missions', [
        'missions' => $missions,
        'role' => $role,
    ]);
    ?>
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
