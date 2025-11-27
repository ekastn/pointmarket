<?php
/**
 * @var array $student StudentDetailsDTO
 */
$student = $student ?? [];
$vark = $student['vark_result'] ?? null;
$mslq = $student['mslq_result'] ?? null;
$ams = $student['ams_result'] ?? null;
?>

<div class="container-fluid">
    <?php
    $renderer->includePartial('components/partials/page_title', [
        'icon' => 'fas fa-user-graduate',
        'title' => htmlspecialchars($student['display_name'] ?? 'N/A'),
        'right' => '<a href="/admin/students" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>',
    ]);
    ?>

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-user"></i> Informasi Profil</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nama Lengkap</dt>
                        <dd class="col-sm-8">: <?= htmlspecialchars($student['display_name'] ?? 'N/A'); ?></dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">: <?= htmlspecialchars($student['email'] ?? 'N/A'); ?></dd>

                        <dt class="col-sm-4">NIM</dt>
                        <dd class="col-sm-8">: <?= htmlspecialchars($student['student_id'] ?? 'N/A'); ?></dd>

                        <dt class="col-sm-4">Program Studi</dt>
                        <dd class="col-sm-8">: <?= htmlspecialchars($student['program_name'] ?? 'N/A'); ?></dd>

                        <dt class="col-sm-4">Angkatan</dt>
                        <dd class="col-sm-8">: <?= htmlspecialchars($student['cohort_year'] ?? '-'); ?></dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">: <?= htmlspecialchars($student['status'] ?? 'N/A'); ?></dd>

                        <dt class="col-sm-4">Skor Akademik</dt>
                        <dd class="col-sm-8">: <strong><?= htmlspecialchars(number_format($student['academic_score'] ?? 0, 2)); ?></strong></dd>

                        <dt class="col-sm-4">Tanggal Lahir</dt>
                        <dd class="col-sm-8">: <?php
                            $birthDate = $student['birth_date'] ?? null;
                            echo $birthDate ? htmlspecialchars(date('d M Y', strtotime($birthDate))) : '-';
                        ?></dd>

                        <dt class="col-sm-4">Gender</dt>
                        <dd class="col-sm-8">: <?= htmlspecialchars($student['gender'] ?? '-'); ?></dd>

                        <dt class="col-sm-4">Telepon</dt>
                        <dd class="col-sm-8">: <?= htmlspecialchars($student['phone'] ?? '-'); ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-brain"></i> Gaya Belajar (VARK) Terbaru</h6>
                </div>
                <div class="card-body">
                    <?php if ($vark): ?>
                        <dl class="row mb-0">
                            <dt class="col-sm-6">Visual</dt>
                            <dd class="col-sm-6">: <?= htmlspecialchars($vark['scores']['visual'] ?? '0'); ?></dd>

                            <dt class="col-sm-6">Auditory</dt>
                            <dd class="col-sm-6">: <?= htmlspecialchars($vark['scores']['auditory'] ?? '0'); ?></dd>

                            <dt class="col-sm-6">Reading/Writing</dt>
                            <dd class="col-sm-6">: <?= htmlspecialchars($vark['scores']['reading'] ?? '0'); ?></dd>

                            <dt class="col-sm-6">Kinesthetic</dt>
                            <dd class="col-sm-6">: <?= htmlspecialchars($vark['scores']['kinesthetic'] ?? '0'); ?></dd>

                            <dt class="col-sm-6">Terakhir Diselesaikan</dt>
                            <dd class="col-sm-6">: <?php
                                $varkCompletedAt = $vark['created_at'] ?? null;
                                echo $varkCompletedAt ? htmlspecialchars(date('d M Y H:i', strtotime($varkCompletedAt))) : '-';
                            ?></dd>
                        </dl>
                    <?php else: ?>
                        <p class="text-muted">Belum ada data gaya belajar VARK yang tercatat.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-lightbulb"></i> Motivasi Belajar (MSLQ) Terbaru</h6>
                </div>
                <div class="card-body">
                    <?php if ($mslq): ?>
                        <dl class="row mb-0">
                            <dt class="col-sm-6">Total Skor</dt>
                            <dd class="col-sm-6">: <?= htmlspecialchars(number_format($mslq['total_score'] ?? 0, 2)); ?></dd>

                            <?php if (!empty($mslq['subscale_scores'])): ?>
                                <dt class="col-sm-12 mt-2">Skor Subskala:</dt>
                                <?php foreach ($mslq['subscale_scores'] as $subscaleName => $subscaleScore): ?>
                                    <dt class="col-sm-6 text-truncate ps-4">- <?= htmlspecialchars($subscaleName); ?></dt>
                                    <dd class="col-sm-6">: <?= htmlspecialchars(number_format($subscaleScore, 2)); ?></dd>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <dt class="col-sm-6 mt-2">Terakhir Diselesaikan</dt>
                            <dd class="col-sm-6">: <?php
                                $mslqCompletedAt = $mslq['created_at'] ?? null;
                                echo $mslqCompletedAt ? htmlspecialchars(date('d M Y H:i', strtotime($mslqCompletedAt))) : '-';
                            ?></dd>
                        </dl>
                    <?php else: ?>
                        <p class="text-muted">Belum ada data motivasi belajar MSLQ yang tercatat.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-warning text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-line"></i> Strategi Belajar (AMS) Terbaru</h6>
                </div>
                <div class="card-body">
                    <?php if ($ams): ?>
                        <dl class="row mb-0">
                            <dt class="col-sm-6">Total Skor</dt>
                            <dd class="col-sm-6">: <?= htmlspecialchars(number_format($ams['total_score'] ?? 0, 2)); ?></dd>

                            <?php if (!empty($ams['subscale_scores'])): ?>
                                <dt class="col-sm-12 mt-2">Skor Subskala:</dt>
                                <?php foreach ($ams['subscale_scores'] as $subscaleName => $subscaleScore): ?>
                                    <dt class="col-sm-6 text-truncate ps-4">- <?= htmlspecialchars($subscaleName); ?></dt>
                                    <dd class="col-sm-6">: <?= htmlspecialchars(number_format($subscaleScore, 2)); ?></dd>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <dt class="col-sm-6 mt-2">Terakhir Diselesaikan</dt>
                            <dd class="col-sm-6">: <?php
                                $amsCompletedAt = $ams['created_at'] ?? null;
                                echo $amsCompletedAt ? htmlspecialchars(date('d M Y H:i', strtotime($amsCompletedAt))) : '-';
                            ?></dd>
                        </dl>
                    <?php else: ?>
                        <p class="text-muted">Belum ada data strategi belajar AMS yang tercatat.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
