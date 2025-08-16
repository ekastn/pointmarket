<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>

    <?php if (isset($_SESSION['messages'])): ?>
        <div class="alert alert-<?= key($_SESSION['messages']) ?> alert-dismissible fade show" role="alert">
            <?= reset($_SESSION['messages']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['messages']); ?>
    <?php endif; ?>

    <div class="row">
        <?php if (empty($badges)) : ?>
            <div class="col-12">
                <div class="alert alert-info" role="alert">
                    You have not earned any badges yet.
                </div>
            </div>
        <?php else : ?>
            <?php foreach ($badges as $badge) : ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-trophy fa-3x text-warning mb-3"></i>
                            <h5 class="card-title"><?= htmlspecialchars($badge['badge_title']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($badge['badge_description'] ?? 'No description provided.') ?></p>
                        </div>
                        <div class="card-footer text-muted text-center">
                            Awarded on: <?= htmlspecialchars(date('d M Y', strtotime($badge['awarded_at']))) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
