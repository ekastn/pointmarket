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
                            <h5 class="card-title"><?= htmlspecialchars($evaluation['questionnaire_title']) ?></h5>
                            <?php if ($evaluation['questionnaire_description']) : ?>
                                <p class="card-text small text-muted"><?= htmlspecialchars($evaluation['questionnaire_description']) ?></p>
                            <?php endif; ?>
                            <p class="card-text">Due Date: <?= htmlspecialchars(date('M d, Y', strtotime($evaluation['due_date']))) ?></p>
                            <p class="card-text">Status: <span class="badge bg-<?= $evaluation['status'] === 'completed' ? 'success' : ($evaluation['status'] === 'pending' ? 'warning' : 'danger') ?>"><?= htmlspecialchars($evaluation['status']) ?></span></p>

                            <?php if ($evaluation['status'] === 'pending') : ?>
                                <a href="/questionnaire?weekly_evaluation_id=<?= htmlspecialchars($evaluation['id']) ?>" class="btn btn-primary">Start Evaluation</a>
                            <?php elseif ($evaluation['status'] === 'completed') : ?>
                                <?php if (isset($evaluation['score'])) : ?>
                                    <p class="card-text">Score: <span class="badge bg-info"><?= htmlspecialchars(number_format($evaluation['score'], 1)) ?></span></p>
                                <?php endif; ?>
                                <p class="card-text">Completed: <?= htmlspecialchars(date('M d, Y', strtotime($evaluation['completed_at']))) ?></p>
                                <a href="/questionnaire/results?weekly_evaluation_id=<?= htmlspecialchars($evaluation['id']) ?>" class="btn btn-outline-secondary">View Details</a>
                            <?php elseif ($evaluation['status'] === 'overdue') : ?>
                                <p class="card-text">Score: <span class="badge bg-info"><?= htmlspecialchars(number_format($evaluation['score'], 1)) ?></span></p>
                                <?php if ($evaluation['completed_at']) : // Check if it was completed but marked overdue later ?>
                                    <p class="card-text">Completed: <?= htmlspecialchars(date('M d, Y', strtotime($evaluation['completed_at']))) ?></p>
                                    <a href="/questionnaire/results?weekly_evaluation_id=<?= htmlspecialchars($evaluation['id']) ?>" class="btn btn-outline-danger">View Details (Overdue)</a>
                                <?php else : ?>
                                    <a href="/questionnaire?weekly_evaluation_id=<?= htmlspecialchars($evaluation['id']) ?>" class="btn btn-danger">Start Evaluation (Overdue)</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
