<?php /** @var array $course */ ?>
<div class="col-lg-4 col-md-6 mb-4">
    <div class="card card-hover h-100">
        <img src="/public/images/product_placeholder.png" class="card-img-top" alt="<?= htmlspecialchars($course['title']) ?>">
        <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="card-title mb-0"><?= htmlspecialchars($course['title']) ?></h5>
                <?php if (isset($course['is_owner']) && $course['is_owner']): ?>
                    <span class="badge bg-info">Owned</span>
                <?php endif; ?>
            </div>
            <?php 
                $ownerRaw = $course['owner_display_name'] ?? null;
                $ownerTitled = $ownerRaw ? ucwords(strtolower($ownerRaw)) : null;
            ?>
            <div class="text-muted small mb-2">
                <strong>Guru:</strong> <?= htmlspecialchars($ownerTitled ?? '-') ?>
            </div>
            <p class="card-text text-muted small mb-3"><?= htmlspecialchars($course['description'] ?? '') ?></p>
            <div class="mt-auto d-grid gap-2">
                <a href="/courses/<?= htmlspecialchars($course['slug']) ?>" class="btn btn-primary">Lihat kelas</a>
                <?php if (isset($course['is_enrolled'])): ?>
                    <?php if ($course['is_enrolled']): ?>
                        <button class="btn btn-outline-danger btn-unenroll" data-course-id="<?= (int)$course['id'] ?>">Batalkan</button>
                    <?php else: ?>
                        <button class="btn btn-success btn-enroll" data-course-id="<?= (int)$course['id'] ?>">Daftar</button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
