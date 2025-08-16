<table class="table table-bordered" id="missionsTable" width="100%" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Description</th>
            <th>Reward Points</th>
            <th>Created At</th>
            <th>Updated At</th>
            <?php if ($role === 'admin'): ?>
                <th>Actions</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($missions)): ?>
            <?php foreach ($missions as $mission): ?>
                <tr>
                    <td><?= htmlspecialchars($mission['id']) ?></td>
                    <td><?= htmlspecialchars($mission['title']) ?></td>
                    <td><?= htmlspecialchars($mission['description'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($mission['reward_points'] ?? '-') ?></td>
                    <td><?= htmlspecialchars(date('d M Y H:i', strtotime($mission['created_at']))) ?></td>
                    <td><?= htmlspecialchars(date('d M Y H:i', strtotime($mission['updated_at']))) ?></td>
                    <?php if ($role === 'admin'): ?>
                        <td>
                            <button class="btn btn-warning btn-sm edit-mission-btn" data-id="<?= $mission['id'] ?>" data-title="<?= htmlspecialchars($mission['title']) ?>" data-description="<?= htmlspecialchars($mission['description'] ?? '') ?>" data-reward-points="<?= htmlspecialchars($mission['reward_points'] ?? '') ?>" data-metadata="<?= htmlspecialchars(json_encode($mission['metadata'])) ?>" data-bs-toggle="modal" data-bs-target="#missionModal">Edit</button>
                            <button class="btn btn-danger btn-sm delete-mission-btn" data-id="<?= $mission['id'] ?>">Delete</button>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?= ($role === 'admin') ? 7 : 6 ?>" class="text-center">No missions found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
