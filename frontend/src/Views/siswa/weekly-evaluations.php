<?php
/**
 * @var array $evaluations
 * @var string $title
 */
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= htmlspecialchars($title) ?></h1>

    <div class="row">
        <?php if (empty($evaluations)) : ?>
            <div class="col-12">
                <div class="alert alert-info" role="alert">
                    No weekly evaluations found.
                </div>
            </div>
        <?php else : ?>
            <?php foreach ($evaluations as $evaluation) : ?>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <h5 class="card-title">Week starting <?= htmlspecialchars(date('M d, Y', strtotime($evaluation['due_date'] . ' - 6 days'))) ?></h5>
                            <p class="card-text">Due Date: <?= htmlspecialchars(date('M d, Y', strtotime($evaluation['due_date']))) ?></p>
                            <p class="card-text">Status: <span class="badge bg-<?= $evaluation['status'] === 'completed' ? 'success' : ($evaluation['status'] === 'pending' ? 'warning' : 'danger') ?>"><?= htmlspecialchars($evaluation['status']) ?></span></p>
                            <?php if ($evaluation['status'] === 'pending') : ?>
                                <a href="/questionnaire?weekly_evaluation_id=<?= htmlspecialchars($evaluation['id']) ?>" class="btn btn-primary">Start Evaluation</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
