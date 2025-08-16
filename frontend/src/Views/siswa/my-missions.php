<div class="container-fluid mt-4">
    <h1 class="h3 mb-4 text-gray-800">My Missions</h1>

    <?php if (empty($missions)): ?>
        <div class="alert alert-info" role="alert">
            You currently have no missions assigned or started.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($missions as $mission): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><?= htmlspecialchars($mission['mission_title']) ?></h6>
                        </div>
                        <div class="card-body">
                            <p><?= htmlspecialchars($mission['mission_description'] ?? 'No description provided.') ?></p>
                            <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($mission['status'])) ?></p>
                            <p><strong>Started:</strong> <?= htmlspecialchars(date('d M Y H:i', strtotime($mission['started_at']))) ?></p>
                            <?php if ($mission['completed_at']): ?>
                                <p><strong>Completed:</strong> <?= htmlspecialchars(date('d M Y H:i', strtotime($mission['completed_at']))) ?></p>
                            <?php endif; ?>
                            <p><strong>Progress:</strong> <?= htmlspecialchars($mission['progress'] ?? 0) ?>%</p>

                            <?php if ($mission['status'] !== 'completed'): ?>
                                <button class="btn btn-success btn-sm start-mission-btn" data-mission-id="<?= $mission['mission_id'] ?>" data-user-mission-id="<?= $mission['id'] ?>">Start/Continue Mission</button>
                                <button class="btn btn-info btn-sm update-status-btn" data-user-mission-id="<?= $mission['id'] ?>" data-bs-toggle="modal" data-bs-target="#updateStatusModal">Update Status</button>
                            <?php else: ?>
                                <span class="badge bg-success">Completed</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">Update Mission Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateStatusForm">
                    <input type="hidden" id="userMissionId" name="user_mission_id">
                    <div class="mb-3">
                        <label for="missionStatus" class="form-label">Status</label>
                        <select class="form-control" id="missionStatus" name="status">
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="on_hold">On Hold</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="missionProgress" class="form-label">Progress (%)</label>
                        <input type="number" class="form-control" id="missionProgress" name="progress" min="0" max="100">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
