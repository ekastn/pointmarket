<?php
// Data for this view will be passed from the VarkAssessmentController
$user = $user ?? ['name' => 'Guest'];
$varkQuestions = $varkQuestions ?? [];
$existingResult = $existingResult ?? null;
$messages = $_SESSION['messages'] ?? [];
unset($_SESSION['messages']);
$show_result = $show_result ?? false;
$result_data = $result_data ?? null;

// Helper function (ideally in a separate utility file or passed from controller)
if (!function_exists('getVARKLearningTips')) {
    function getVARKLearningTips($dominantStyle) {
        $tips = [
            'Visual' => [
                'study_tips' => [
                    'Gunakan diagram, chart, dan mind maps',
                    'Highlight dengan warna-warna berbeda',
                    'Buat flashcards dengan gambar',
                    'Tonton video pembelajaran'
                ],
                'description' => 'Anda lebih mudah belajar melalui elemen visual seperti gambar, diagram, dan grafik.',
                'icon' => 'fas fa-eye'
            ],
            'Auditory' => [
                'study_tips' => [
                    'Diskusikan materi dengan teman',
                    'Rekam dan dengar kembali catatan',
                    'Gunakan musik atau rhythm untuk mengingat',
                    'Baca materi dengan suara keras'
                ],
                'description' => 'Anda lebih mudah belajar melalui mendengar dan berbicara.',
                'icon' => 'fas fa-volume-up'
            ],
            'Reading' => [
                'study_tips' => [
                    'Buat catatan lengkap saat belajar',
                    'Gunakan daftar dan bullet points',
                    'Baca buku teks dan artikel',
                    'Tulis ringkasan dengan kata-kata sendiri'
                ],
                'description' => 'Anda lebih mudah belajar melalui membaca dan menulis.',
                'icon' => 'fas fa-book-open'
            ],
            'Kinesthetic' => [
                'study_tips' => [
                    'Praktikkan langsung apa yang dipelajari',
                    'Gunakan model atau objek fisik',
                    'Bergerak sambil belajar (walking study)',
                    'Buat eksperimen dan simulasi'
                ],
                'description' => 'Anda lebih mudah belajar melalui pengalaman langsung dan praktik.',
                'icon' => 'fas fa-hand-rock'
            ],
            // Multi-modal styles (simplified descriptions)
            'Visual/Auditory' => [
                'study_tips' => ['Gunakan kombinasi visual dan audio.'],
                'description' => 'Anda belajar terbaik dengan melihat dan mendengar.',
                'icon' => 'fas fa-eye fas fa-volume-up'
            ],
            'Visual/Reading' => [
                'study_tips' => ['Kombinasikan visual dengan membaca/menulis.'],
                'description' => 'Anda belajar terbaik dengan melihat dan membaca/menulis.',
                'icon' => 'fas fa-eye fas fa-book-open'
            ],
            'Visual/Kinesthetic' => [
                'study_tips' => ['Gunakan visual dan praktik langsung.'],
                'description' => 'Anda belajar terbaik dengan melihat dan melakukan.',
                'icon' => 'fas fa-eye fas fa-hand-rock'
            ],
            'Auditory/Reading' => [
                'study_tips' => ['Kombinasikan mendengar dengan membaca/menulis.'],
                'description' => 'Anda belajar terbaik dengan mendengar dan membaca/menulis.',
                'icon' => 'fas fa-volume-up fas fa-book-open'
            ],
            'Auditory/Kinesthetic' => [
                'study_tips' => ['Kombinasikan mendengar dengan praktik langsung.'],
                'description' => 'Anda belajar terbaik dengan mendengar dan melakukan.',
                'icon' => 'fas fa-volume-up fas fa-hand-rock'
            ],
            'Reading/Kinesthetic' => [
                'study_tips' => ['Kombinasikan membaca/menulis dengan praktik langsung.'],
                'description' => 'Anda belajar terbaik dengan membaca/menulis dan melakukan.',
                'icon' => 'fas fa-book-open fas fa-hand-rock'
            ],
            'Visual/Auditory/Reading' => [
                'study_tips' => ['Gunakan visual, audio, dan membaca/menulis.'],
                'description' => 'Anda adalah pembelajar multimodal yang kuat dalam melihat, mendengar, dan membaca/menulis.',
                'icon' => 'fas fa-eye fas fa-volume-up fas fa-book-open'
            ],
            'Visual/Auditory/Kinesthetic' => [
                'study_tips' => ['Gunakan visual, audio, dan praktik langsung.'],
                'description' => 'Anda adalah pembelajar multimodal yang kuat dalam melihat, mendengar, dan melakukan.',
                'icon' => 'fas fa-eye fas fa-volume-up fas fa-hand-rock'
            ],
            'Visual/Reading/Kinesthetic' => [
                'study_tips' => ['Gunakan visual, membaca/menulis, dan praktik langsung.'],
                'description' => 'Anda adalah pembelajar multimodal yang kuat dalam melihat, membaca/menulis, dan melakukan.',
                'icon' => 'fas fa-eye fas fa-book-open fas fa-hand-rock'
            ],
            'Auditory/Reading/Kinesthetic' => [
                'study_tips' => ['Gunakan audio, membaca/menulis, dan praktik langsung.'],
                'description' => 'Anda adalah pembelajar multimodal yang kuat dalam mendengar, membaca/menulis, dan melakukan.',
                'icon' => 'fas fa-volume-up fas fa-book-open fas fa-hand-rock'
            ],
            'Visual/Auditory/Reading/Kinesthetic' => [
                'study_tips' => ['Gunakan semua modalitas pembelajaran.'],
                'description' => 'Anda adalah pembelajar multimodal yang efektif dengan semua gaya belajar.',
                'icon' => 'fas fa-brain'
            ],
        ];
        return $tips[$dominantStyle] ?? ['study_tips' => ['Tidak ada tips spesifik.'], 'description' => 'Gaya belajar tidak teridentifikasi.', 'icon' => 'fas fa-question-circle'];
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-brain me-2 text-primary"></i>
        Asesmen Gaya Belajar VARK
    </h1>
</div>

<?php if (!empty($messages)): ?>
    <?php foreach ($messages as $type => $message): ?>
        <div class="alert alert-<?php echo $type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($show_result) && $show_result): ?>
    <!-- VARK Result Display -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Profil Gaya Belajar VARK Anda
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Skor Gaya Belajar:</h6>
                            <div class="mb-3">
                                <?php 
                                $maxScore = 0;
                                if (!empty($result_data['scores'])) {
                                    $maxScore = max($result_data['scores']);
                                }
                                $totalQuestions = 16; // Assuming 16 questions for VARK

                                foreach ($result_data['scores'] as $style => $score): 
                                    $percentage = ($totalQuestions > 0) ? ($score / $totalQuestions) * 100 : 0;
                                    $isHighest = ($score == $maxScore && $maxScore > 0); // Also check if maxScore is not 0
                                ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="<?php echo $isHighest ? 'fw-bold text-primary' : ''; ?>">
                                            <?php echo htmlspecialchars($style); ?>:
                                        </span>
                                        <span class="<?php echo $isHighest ? 'fw-bold text-primary' : ''; ?>">
                                            <?php echo htmlspecialchars($score); ?>/<?php echo $totalQuestions; ?> (<?php echo round($percentage); ?>%)
                                        </span>
                                    </div>
                                    <div class="progress mb-2" style="height: 8px;">
                                        <div class="progress-bar <?php echo $isHighest ? 'bg-primary' : 'bg-secondary'; ?>" 
                                             style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success">Preferensi Belajar Anda:</h6>
                            <div class="p-3 bg-light rounded">
                                <h5 class="text-success mb-2">
                                    <i class="<?php echo getVARKLearningTips($result_data['dominant_style'])['icon']; ?> me-2"></i>
                                    <?php echo htmlspecialchars($result_data['learning_preference']); ?>
                                </h5>
                                <p class="mb-0">
                                    <?php echo htmlspecialchars(getVARKLearningTips($result_data['dominant_style'])['description']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <a href="/vark-assessment?retake=true" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-sync-alt me-1"></i>
                            Ulangi Asesmen
                        </a>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Introduction -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-primary">
                        <i class="fas fa-info-circle me-2"></i>
                        Tentang Asesmen Gaya Belajar VARK
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p>VARK adalah akronim dari <strong>V</strong>isual, <strong>A</strong>uditori, <strong>M</strong>embaca/Menulis, dan <strong>K</strong>inestetik. Asesmen ini membantu mengidentifikasi mode belajar pilihan Anda.</p>
                            
                            <h6 class="mt-3">Empat Gaya Belajar:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-eye text-info me-2"></i><strong>Visual:</strong> Belajar melalui grafik, diagram, dan peta</li>
                                <li><i class="fas fa-volume-up text-warning me-2"></i><strong>Auditori:</strong> Belajar melalui mendengarkan dan diskusi</li>
                                <li><i class="fas fa-book-open text-success me-2"></i><strong>Membaca/Menulis:</strong> Belajar melalui teks dan catatan</li>
                                <li><i class="fas fa-hand-rock text-danger me-2"></i><strong>Kinestetik:</strong> Belajar melalui praktik langsung</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded">
                                <h6 class="text-primary">Detail Asesmen:</h6>
                                <ul class="list-unstyled mb-0">
                                    <li><i class="fas fa-clock me-2"></i><strong>Waktu:</strong> ~10-15 menit</li>
                                    <li><i class="fas fa-list-ol me-2"></i><strong>Pertanyaan:</strong> 16 skenario</li>
                                    <li><i class="fas fa-tasks me-2"></i><strong>Format:</strong> Pilihan ganda</li>
                                    <li><i class="fas fa-chart-line me-2"></i><strong>Hasil:</strong> Profil gaya belajar</li>
                                </ul>
                            </div>
                            
                            <?php if ($existingResult): ?>
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Hasil Sebelumnya:</strong> <?php echo htmlspecialchars($existingResult['learning_preference']); ?>
                                    <br><small>Selesai: <?php echo date('d M Y', strtotime($existingResult['completed_at'])); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- VARK Questionnaire -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Kuesioner Gaya Belajar VARK
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="varkForm">
                        <div id="questionsContainer">
                            <?php if (!empty($varkQuestions)): ?>
                                <?php foreach ($varkQuestions as $index => $question): ?>
                                    <div class="question-card mb-4 p-3 border rounded">
                                        <h6 class="text-primary mb-3">
                                            Pertanyaan <?php echo htmlspecialchars($question['question_number']); ?> dari 16
                                        </h6>
                                        <p class="fw-bold mb-3">
                                            <?php echo htmlspecialchars($question['question_text']); ?>
                                        </p>
                                        
                                        <div class="options">
                                            <?php if (!empty($question['options'])): ?>
                                                <?php foreach ($question['options'] as $option): ?>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" 
                                                               type="radio" 
                                                               name="answers[<?php echo htmlspecialchars($question['id']); ?>]" 
                                                               id="q<?php echo htmlspecialchars($question['id']); ?>_<?php echo htmlspecialchars($option['option_letter']); ?>"
                                                               value="<?php echo htmlspecialchars($option['option_letter']); ?>"
                                                               required>
                                                        <label class="form-check-label" 
                                                               for="q<?php echo htmlspecialchars($question['id']); ?>_<?php echo htmlspecialchars($option['option_letter']); ?>">
                                                            <strong><?php echo htmlspecialchars(strtoupper($option['option_letter'])); ?>.</strong> 
                                                            <?php echo htmlspecialchars($option['option_text']); ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p class="text-muted">Tidak ada opsi tersedia untuk pertanyaan ini.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Tidak ada pertanyaan VARK tersedia.</h5>
                                    <p class="text-muted">Silakan coba lagi nanti atau hubungi dukungan.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($varkQuestions)): ?>
                            <div class="text-center mt-4">
                                <button type="submit" name="submit_vark" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check me-2"></i>
                                    Kirim Asesmen VARK
                                </button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- VARK Assessment Proof of Concept Notice -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-primary" role="alert">
            <h6 class="alert-heading">
                <i class="fas fa-info-circle me-2"></i>
                Asesmen Gaya Belajar VARK - Bukti Konsep
            </h6>
            <p class="mb-2">
                <strong>Status:</strong> Sistem pembelajaran ini menggunakan algoritma VARK yang tervalidasi untuk mengidentifikasi gaya belajar Anda.
            </p>
            <div class="row">
                <div class="col-md-6">
                    <strong>Gaya Belajar VARK:</strong>
                    <ul class="small mb-0 mt-1">
                        <li><strong>Visual:</strong> Belajar melalui grafik, diagram, dan peta</li>
                        <li><strong>Auditori:</strong> Belajar melalui mendengarkan dan diskusi</li>
                        <li><strong>Membaca/Menulis:</strong> Belajar melalui teks dan catatan</li>
                        <li><strong>Kinestetik:</strong> Belajar melalui praktik langsung</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <strong>Integrasi dengan AI POINTMARKET:</strong>
                    <ul class="small mb-0 mt-1">
                        <li>📊 Hasil akan memengaruhi rekomendasi materi</li>
                        <li>🎯 AI akan menyesuaikan metode pembelajaran</li>
                        <li>📚 Konten akan disesuaikan dengan preferensi belajar Anda</li>
                        <li>🔄 Sistem akan terus belajar dari interaksi Anda</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>