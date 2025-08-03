<?php
// Data for this view will be passed from the VarkCorrelationAnalysisController
$user = $user ?? ['name' => 'Guest'];
$vark_data = $vark_data ?? [];
$dominant_style = $dominant_style ?? 'N/A';
$mslq_score = $mslq_score ?? 'N/A';
$ams_score = $ams_score ?? 'N/A';
?>

<div class="hero-section text-center">
    <h1><i class="fas fa-chart-network me-3"></i>Analisis Korelasi VARK-MSLQ-AMS</h1>
    <p class="lead">Memahami hubungan antara gaya belajar, motivasi, dan strategi pembelajaran</p>
</div>

<!-- VARK Scores Display -->
<div class="correlation-card">
    <h4><i class="fas fa-eye me-2"></i>Skor VARK</h4>
    <div class="row mt-3">
        <?php foreach ($vark_data as $style => $score): ?>
        <div class="col-md-3 text-center mb-3">
            <div class="border rounded p-3 <?php echo $style === $dominant_style ? 'border-primary bg-light' : ''; ?>">
                <h6><?php echo $style; ?></h6>
                <div class="score-display <?php echo $style === $dominant_style ? 'text-primary' : 'text-muted'; ?>">
                    <?php echo $score; ?>
                </div>
                <?php if ($style === $dominant_style): ?>
                    <small class="text-primary"><strong>Dominan</strong></small>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center mt-3">
        <span class="style-badge bg-primary text-white">
            Gaya Belajar Dominan: <?php echo $dominant_style; ?>
        </span>
    </div>
</div>

<!-- Correlation Predictions -->
<div class="row">
    <div class="col-md-6">
        <div class="correlation-card">
            <h5><i class="fas fa-brain me-2 text-primary"></i>Korelasi MSLQ</h5>
            <p class="text-muted">Untuk <?php echo $dominant_style; ?> Learners:</p>
            
            <?php if (isset($correlation_results) && $correlation_results !== null): ?>
                <?php if ($dominant_style === 'Visual'): ?>
                <ul>
                    <li><strong>Elaborasi</strong> - Korelasi Kuat (r ≈ 0.65)</li>
                    <li><strong>Organisasi</strong> - Sangat Kuat (r ≈ 0.70)</li>
                    <li><strong>Berpikir Kritis</strong> - Moderat (r ≈ 0.55)</li>
                </ul>
                <div class="alert alert-primary">
                    <strong>Insight:</strong> Unggul dalam membuat model mental visual dan alat organisasi.
                </div>
                <?php elseif ($dominant_style === 'Auditory'): ?>
                <ul>
                    <li><strong>Mencari Bantuan</strong> - Sangat Kuat (r ≈ 0.75)</li>
                    <li><strong>Pembelajaran Sejawat</strong> - Kuat (r ≈ 0.68)</li>
                    <li><strong>Pengulangan</strong> - Kuat (r ≈ 0.60)</li>
                </ul>
                <div class="alert alert-primary">
                    <strong>Insight:</strong> Sangat menyukai penjelasan verbal dan diskusi kelompok.
                </div>
                <?php elseif ($dominant_style === 'Reading/Writing'): ?>
                <ul>
                    <li><strong>Elaborasi</strong> - Sangat Kuat (r ≈ 0.80)</li>
                    <li><strong>Regulasi Diri Metakognitif</strong> - Sangat Kuat (r ≈ 0.75)</li>
                    <li><strong>Organisasi</strong> - Kuat (r ≈ 0.72)</li>
                </ul>
                <div class="alert alert-primary">
                    <strong>Insight:</strong> Unggul dalam elaborasi tertulis dan pemantauan diri melalui tulisan.
                </div>
                <?php elseif ($dominant_style === 'Kinesthetic'): ?>
                <ul>
                    <li><strong>Regulasi Usaha</strong> - Kuat (r ≈ 0.72)</li>
                    <li><strong>Berpikir Kritis</strong> - Kuat (r ≈ 0.62)</li>
                    <li><strong>Mencari Bantuan</strong> - Kuat (r ≈ 0.58)</li>
                </ul>
                <div class="alert alert-primary">
                    <strong>Insight:</strong> Ketekunan tinggi dalam tugas-tugas praktis dan belajar melalui eksperimen.
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    Tidak ada data korelasi MSLQ yang tersedia secara rinci.
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    Tidak ada data korelasi MSLQ yang tersedia secara rinci.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="correlation-card">
            <h5><i class="fas fa-heart me-2 text-success"></i>Korelasi AMS</h5>
            <p class="text-muted">Motivasi untuk <?php echo $dominant_style; ?> Learners:</p>
            
            <?php if (isset($correlation_results) && $correlation_results !== null): ?>
                <?php if ($dominant_style === 'Visual'): ?>
                <ul>
                    <li><strong>Intrinsik - Untuk Mengetahui</strong> - Kuat (r ≈ 0.60)</li>
                    <li><strong>Intrinsik - Mencapai</strong> - Kuat (r ≈ 0.58)</li>
                    <li><strong>Intrinsik - Stimulasi</strong> - Moderat (r ≈ 0.45)</li>
                </ul>
                <div class="alert alert-success">
                    <strong>Insight:</strong> Penemuan visual mendorong rasa ingin tahu dan representasi pencapaian visual.
                </div>
                <?php elseif ($dominant_style === 'Auditory'): ?>
                <ul>
                    <li><strong>Intrinsik - Stimulasi</strong> - Kuat (r ≈ 0.65)</li>
                    <li><strong>Eksternal - Teridentifikasi</strong> - Kuat (r ≈ 0.55)</li>
                    <li><strong>Intrinsik - Untuk Mengetahui</strong> - Moderat (r ≈ 0.50)</li>
                </ul>
                <div class="alert alert-success">
                    <strong>Insight:</strong> Kegembiraan dari interaksi verbal dan pengakuan sosial memotivasi.
                </div>
                <?php elseif ($dominant_style === 'Reading/Writing'): ?>
                <ul>
                    <li><strong>Intrinsik - Untuk Mengetahui</strong> - Sangat Kuat (r ≈ 0.78)</li>
                    <li><strong>Intrinsik - Mencapai</strong> - Kuat (r ≈ 0.70)</li>
                    <li><strong>Eksternal - Teridentifikasi</strong> - Moderat (r ≈ 0.48)</li>
                </ul>
                <div class="alert alert-success">
                    <strong>Insight:</strong> Motivasi tertinggi untuk akuisisi pengetahuan melalui membaca dan pencapaian melalui tulisan.
                </div>
                <?php elseif ($dominant_style === 'Kinesthetic'): ?>
                <ul>
                    <li><strong>Intrinsik - Stimulasi</strong> - Sangat Kuat (r ≈ 0.75)</li>
                    <li><strong>Intrinsik - Mencapai</strong> - Kuat (r ≈ 0.68)</li>
                    <li><strong>Eksternal - Introjected</strong> - Moderat (r ≈ 0.52)</li>
                </ul>
                <div class="alert alert-success">
                    <strong>Insight:</strong> Stimulasi tertinggi dari aktivitas fisik dan pencapaian melalui hasil praktis.
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    Tidak ada data korelasi AMS yang tersedia secara rinci.
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    Tidak ada data korelasi AMS yang tersedia secara rinci.
                </div>
            <?php endif; ?>
        </div>
    </div>

<!-- Recommendations -->
<div class="correlation-card">
    <h5><i class="fas fa-lightbulb me-2 text-warning"></i>Rekomendasi Personal</h5>
    <?php if (isset($correlation_results) && $correlation_results !== null): ?>
        <h6>Strategi yang Direkomendasikan:</h6>
        <ul>
            <?php foreach ($correlation_results['recommendations'] as $rec): ?>
                <li><?php echo htmlspecialchars($rec); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="alert alert-info">
            <strong>Segera Hadir:</strong> Strategi pembelajaran personal dan peningkatan motivasi berdasarkan skor VARK, MSLQ, dan AMS Anda akan ditampilkan di sini pada Fase 2.
        </div>
    <?php endif; ?>
</div>

<div class="correlation-card mt-4">
    <h5><i class="fas fa-info-circle me-2 text-info"></i>Detail Korelasi Tambahan</h5>
    <div class="row">
        <div class="col-md-6">
            <h6>Detail Korelasi MSLQ: <?php echo htmlspecialchars($mslq_score); ?></h6>
            <?php if (!empty($correlation_results['mslq_correlation'])): ?>
                <table class="table table-bordered table-striped mt-3">
                    <thead>
                        <tr>
                            <th>Komponen</th>
                            <th>Korelasi (r)</th>
                            <th>Penjelasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($correlation_results['mslq_correlation'] as $detail): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($detail['component']); ?></td>
                                <td><?php echo htmlspecialchars(sprintf("%.2f", $detail['correlation'])); ?></td>
                                <td><?php echo htmlspecialchars($detail['explanation']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">
                    Tidak ada data korelasi MSLQ yang tersedia secara rinci.
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <h6>Detail Korelasi AMS: <?php echo htmlspecialchars($ams_score); ?></h6>
            <?php if (!empty($correlation_results['ams_correlation'])): ?>
                <table class="table table-bordered table-striped mt-3">
                    <thead>
                        <tr>
                            <th>Komponen</th>
                            <th>Korelasi (r)</th>
                            <th>Penjelasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($correlation_results['ams_correlation'] as $detail): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($detail['component']); ?></td>
                                <td><?php echo htmlspecialchars(sprintf("%.2f", $detail['correlation'])); ?></td>
                                <td><?php echo htmlspecialchars($detail['explanation']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">
                    Tidak ada data korelasi AMS yang tersedia secara rinci.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px 0;
        border-radius: 15px;
        margin-bottom: 30px;
    }
    .correlation-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .score-display {
        font-size: 2rem;
        font-weight: bold;
        text-align: center;
    }
    .style-badge {
        background: rgba(255,255,255,0.2);
        padding: 10px 20px;
        border-radius: 25px;
        font-size: 1.1rem;
        display: inline-block;
    }
</style>