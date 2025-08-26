<?php
/**
 * @var array $dashboardData
 * @var string $title
 */
?>

<div class="container-fluid">
<?php $renderer->includePartial('components/partials/page_title', [
    'icon' => 'fas fa-chart-line',
    'title' => htmlspecialchars($title ?: 'Monitoring Evaluasi'),
]); ?>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Weekly Evaluation Monitoring</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Completion Rate</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($dashboardData)) : ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No data available.</td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($dashboardData as $data) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($data['student_name']) ?></td>
                                            <td><?= htmlspecialchars(number_format($data['completion_rate'] * 100, 2)) ?>%</td>
                                            <td>
                                                <span class="badge bg-<?= $data['status'] === 'completed' ? 'success' : ($data['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                                    <?= htmlspecialchars($data['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
