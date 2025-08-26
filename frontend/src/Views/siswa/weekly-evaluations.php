<?php
/**
 * @var array $evaluations
 * @var string $title
 */
?>

<div class="container-fluid">
    <?php $renderer->includePartial('components/partials/page_title', [
        'icon' => 'fas fa-calendar-check',
        'title' => htmlspecialchars($title ?: 'Evaluasi Mingguan'),
    ]); ?>

    <div class="row pm-section">
        <?php if (empty($evaluations)) : ?>
            <div class="col-12">
                <?php $renderer->includePartial('components/partials/empty_state', [
                    'icon' => 'fas fa-calendar-check',
                    'title' => 'Belum ada evaluasi mingguan',
                    'subtitle' => 'Evaluasi mingguan akan muncul di sini ketika dibuat untukmu.',
                ]); ?>
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
                            <p class="card-text">Tenggat: <?= htmlspecialchars(date('d M Y', strtotime($evaluation['due_date']))) ?></p>
                            <p class="card-text">Status: <span class="badge bg-<?= $evaluation['status'] === 'completed' ? 'success' : ($evaluation['status'] === 'pending' ? 'warning' : 'danger') ?>"><?= htmlspecialchars($evaluation['status']) ?></span></p>

                            <?php if ($evaluation['status'] === 'pending') : ?>
                                <a href="/questionnaires/<?= htmlspecialchars($evaluation['questionnaire_id']) ?>?weekly_evaluation_id=<?= htmlspecialchars($evaluation['id']) ?>" class="btn btn-primary">Mulai Evaluasi</a>
                            <?php elseif ($evaluation['status'] === 'completed') : ?>
                                <?php if (isset($evaluation['score'])) : ?>
                                    <p class="card-text">Skor: <span class="badge bg-info"><?= htmlspecialchars(number_format($evaluation['score'], 1)) ?></span></p>
                                <?php endif; ?>
                                <p class="card-text">Selesai: <?= htmlspecialchars(date('d M Y', strtotime($evaluation['completed_at']))) ?></p>
                                <a href="/questionnaires/results?weekly_evaluation_id=<?= htmlspecialchars($evaluation['id']) ?>" class="btn btn-outline-secondary">Lihat Detail</a>
                            <?php elseif ($evaluation['status'] === 'overdue') : ?>
                                <p class="card-text">Skor: <span class="badge bg-info"><?= htmlspecialchars(number_format($evaluation['score'], 1)) ?></span></p>
                                <?php if ($evaluation['completed_at']) : // Check if it was completed but marked overdue later ?>
                                    <p class="card-text">Selesai: <?= htmlspecialchars(date('d M Y', strtotime($evaluation['completed_at']))) ?></p>
                                    <a href="/questionnaires/results?weekly_evaluation_id=<?= htmlspecialchars($evaluation['id']) ?>" class="btn btn-outline-danger">Lihat Detail (Terlambat)</a>
                                <?php else : ?>
                                    <a href="/questionnaires/<?= htmlspecialchars($evaluation['questionnaire_id']) ?>?weekly_evaluation_id=<?= htmlspecialchars($evaluation['id']) ?>" class="btn btn-danger">Mulai Evaluasi (Terlambat)</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
