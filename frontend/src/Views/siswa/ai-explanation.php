<?php
// Data for the view will be passed from the AIExplanationController
// For now, we can assume no dynamic data is needed for this page
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-brain me-2"></i>
        Cara Kerja AI dalam POINTMARKET
    </h1>
</div>

<!-- Introduction -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle me-2"></i>Penjelasan Sederhana</h5>
            <p class="mb-0">POINTMARKET menggunakan 3 teknologi AI utama untuk membantu pembelajaran Anda menjadi lebih efektif. Mari kita pahami bagaimana masing-masing bekerja dengan contoh nyata!</p>
        </div>
    </div>
</div>

<!-- NLP Section -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card ai-card nlp-card">
            <div class="card-header bg-info text-white">
                <h4><i class="fas fa-language me-2"></i>1. Natural Language Processing (NLP)</h4>
                <p class="mb-0">AI yang memahami dan menganalisis teks bahasa manusia</p>
            </div>
            <div class="card-body">
                <h5>🤔 Apa yang dilakukan NLP?</h5>
                <p>NLP menganalisis jawaban essay Anda dan memberikan feedback otomatis berdasarkan kualitas tulisan.</p>
                
                <div class="score-demo">
                    <h6><i class="fas fa-pen me-2"></i>Contoh Analisis Essay</h6>
                    
                    <div class="example-text">
                        <strong>Soal:</strong> "Jelaskan pentingnya teknologi dalam pendidikan!"
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="workflow-step">
                                <h6 class="text-danger">❌ Jawaban Kurang Baik</h6>
                                <div class="example-text">
                                    "teknologi bagus untuk sekolah karena bisa belajar dengan komputer"
                                </div>
                                <strong>NLP Score: 40/100</strong>
                                <ul class="mt-2">
                                    <li>Kata kunci: 2/5 (teknologi, belajar)</li>
                                    <li>Grammar: 3/10 (banyak kesalahan)</li>
                                    <li>Panjang: 1/5 (terlalu pendek)</li>
                                    <li>Struktur: 2/10 (tidak terorganisir)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="workflow-step">
                                <h6 class="text-success">✅ Jawaban Baik</h6>
                                <div class="example-text">
                                    "Teknologi dalam pendidikan sangat penting karena: 1) Meningkatkan aksesibilitas pembelajaran jarak jauh, 2) Menyediakan sumber belajar yang interaktif, 3) Memungkinkan personalisasi pembelajaran sesuai kebutuhan siswa."
                                </div>
                                <strong>NLP Score: 92/100</strong>
                                <ul class="mt-2">
                                    <li>Kata kunci: 5/5 (teknologi, pendidikan, pembelajaran, dll)</li>
                                    <li>Grammar: 9/10 (struktur kalimat baik)</li>
                                    <li>Panjang: 5/5 (sesuai ekspektasi)</li>
                                    <li>Struktur: 8/10 (ada numbering, jelas)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-light">
                    <h6><i class="fas fa-cogs me-2"></i>Cara Kerja NLP:</h6>
                    <ol>
                        <li><strong>Tokenisasi:</strong> Memecah teks menjadi kata-kata</li>
                        <li><strong>Analisis Grammar:</strong> Memeriksa struktur kalimat</li>
                        <li><strong>Keyword Matching:</strong> Mencari kata kunci yang relevan</li>
                        <li><strong>Sentiment Analysis:</strong> Mengukur tone positif/negatif</li>
                        <li><strong>Scoring:</strong> Memberikan nilai berdasarkan semua faktor</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- RL Section -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card ai-card rl-card">
            <div class="card-header bg-primary text-white">
                <h4><i class="fas fa-robot me-2"></i>2. Reinforcement Learning (RL)</h4>
                <p class="mb-0">AI yang belajar dari kesalahan untuk memberikan rekomendasi terbaik</p>
            </div>
            <div class="card-body">
                <h5>🎯 Apa yang dilakukan RL?</h5>
                <p>RL mengamati pola belajar Anda dan merekomendasikan urutan materi yang paling efektif.</p>
                
                <div class="score-demo">
                    <h6><i class="fas fa-route me-2"></i>Contoh Learning Path Optimization</h6>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="workflow-step">
                                <h6><i class="fas fa-user me-2"></i>Profil Siswa A</h6>
                                <ul>
                                    <li>Matematika: 85 poin</li>
                                    <li>Fisika: 60 poin</li>
                                    <li>Kimia: 70 poin</li>
                                    <li>Waktu belajar: Pagi (07-09)</li>
                                </ul>
                                <div class="alert alert-warning">
                                    <strong>RL Decision:</strong><br>
                                    Prioritas: Fisika → Kimia → Matematika<br>
                                    <small>Confidence: 87%</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="workflow-step">
                                <h6><i class="fas fa-brain me-2"></i>Proses Pengambilan Keputusan RL</h6>
                                
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Action</th>
                                                <th>Current Score</th>
                                                <th>Predicted Improvement</th>
                                                <th>RL Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="table-success">
                                                <td><strong>Belajar Fisika</strong></td>
                                                <td>60</td>
                                                <td>+25 poin</td>
                                                <td><strong>95/100</strong></td>
                                            </tr>
                                            <tr>
                                                <td>Belajar Kimia</td>
                                                <td>70</td>
                                                <td>+15 poin</td>
                                                <td>75/100</td>
                                            </tr>
                                            <tr>
                                                <td>Belajar Matematika</td>
                                                <td>85</td>
                                                <td>+5 poin</td>
                                                <td>30/100</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mt-3">
                                    <strong>Reward Calculation:</strong><br>
                                    <code>RL Score = (Potential Improvement × 40%) + (Learning Efficiency × 30%) + (Time Optimization × 30%)</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-light">
                    <h6><i class="fas fa-cogs me-2"></i>Cara Kerja RL:</h6>
                    <ol>
                        <li><strong>Observe:</strong> Mengamati performance siswa saat ini</li>
                        <li><strong>Predict:</strong> Memprediksi hasil dari berbagai pilihan belajar</li>
                        <li><strong>Decide:</strong> Memilih action dengan reward tertinggi</li>
                        <li><strong>Learn:</strong> Memperbaiki prediksi berdasarkan hasil aktual</li>
                        <li><strong>Improve:</strong> Memberikan rekomendasi yang semakin akurat</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CBF Section -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card ai-card cbf-card">
            <div class="card-header bg-success text-white">
                <h4><i class="fas fa-filter me-2"></i>3. Collaborative & Content-Based Filtering (CBF)</h4>
                <p class="mb-0">AI yang merekomendasikan materi berdasarkan kesamaan dengan siswa lain</p>
            </div>
            <div class="card-body">
                <h5>🤝 Apa yang dilakukan CBF?</h5>
                <p>CBF mencari siswa dengan profil serupa dan merekomendasikan materi yang berhasil untuk mereka.</p>
                
                <div class="score-demo">
                    <h6><i class="fas fa-users me-2"></i>Contoh Collaborative Filtering</h6>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="workflow-step">
                                <h6><i class="fas fa-user-circle me-2"></i>Anda (User ID: 123)</h6>
                                <ul>
                                    <li>MSLQ Score: 75</li>
                                    <li>AMS Score: 82</li>
                                    <li>Suka: Video Tutorial</li>
                                    <li>Lemah: Matematika Abstrak</li>
                                    <li>Strong: Bahasa Indonesia</li>
                                </ul>
                            </div>
                            
                            <div class="workflow-step">
                                <h6><i class="fas fa-search me-2"></i>Mencari Siswa Serupa</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <tr>
                                            <td>User 456</td>
                                            <td><span class="badge bg-success">92% Match</span></td>
                                        </tr>
                                        <tr>
                                            <td>User 789</td>
                                            <td><span class="badge bg-success">89% Match</span></td>
                                        </tr>
                                        <tr>
                                            <td>User 321</td>
                                            <td><span class="badge bg-warning">76% Match</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="workflow-step">
                                <h6><i class="fas fa-star me-2"></i>Rekomendasi Untuk Anda</h6>
                                
                                <div class="alert alert-success">
                                    <strong>📹 Video: "Aljabar Linear Mudah"</strong><br>
                                    <small>User serupa rating: 4.8/5</small><br>
                                    <strong>CBF Score: 94/100</strong>
                                </div>
                                
                                <div class="alert alert-info">
                                    <strong>📖 E-book: "Trigonometri Visual"</strong><br>
                                    <small>User serupa rating: 4.5/5</small><br>
                                    <strong>CBF Score: 87/100</strong>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <strong>🎵 Audio: "Kalkulus Podcast"</strong><br>
                                    <small>User serupa rating: 3.2/5</small><br>
                                    <strong>CBF Score: 65/100</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="score-demo">
                    <h6><i class="fas fa-tags me-2"></i>Contoh Content-Based Filtering</h6>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="workflow-step">
                                <h6>Analisis Konten yang Anda Sukai</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Materi yang Anda Rating Tinggi:</strong>
                                        <ul>
                                            <li>Video Praktikum Fisika ⭐⭐⭐⭐⭐</li>
                                            <li>Infografis Kimia ⭐⭐⭐⭐⭐</li>
                                            <li>Tutorial Coding ⭐⭐⭐⭐</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Pattern yang Ditemukan:</strong>
                                        <ul>
                                            <li>Format: Visual (90%)</li>
                                            <li>Durasi: 10-15 menit (85%)</li>
                                            <li>Level: Intermediate (80%)</li>
                                            <li>Bahasa: Indonesia (95%)</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Rekomendasi Baru:</strong>
                                        <ul>
                                            <li>Video Matematika Visual <span class="badge bg-success">96%</span></li>
                                            <li>Animasi Biologi <span class="badge bg-success">94%</span></li>
                                            <li>Diagram Sejarah <span class="badge bg-info">88%</span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-light">
                    <h6><i class="fas fa-cogs me-2"></i>Cara Kerja CBF:</h6>
                    <ol>
                        <li><strong>User Profiling:</strong> Menganalisis preferensi dan performa Anda</li>
                        <li><strong>Similarity Calculation:</strong> Mencari siswa dengan profil serupa</li>
                        <li><strong>Content Analysis:</strong> Menganalisis karakteristik materi</li>
                        <li><strong>Hybrid Recommendation:</strong> Menggabungkan collaborative + content-based</li>
                        <li><strong>Feedback Loop:</strong> Belajar dari rating dan interaksi Anda</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Integration Example -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h4><i class="fas fa-puzzle-piece me-2"></i>Bagaimana Ketiganya Bekerja Bersama?</h4>
            </div>
            <div class="card-body">
                <div class="score-demo">
                    <h6><i class="fas fa-scenario me-2"></i>Skenario: Anda Mengerjakan Assignment Fisika</h6>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="workflow-step border-info">
                                <h6 class="text-info">📝 Step 1: NLP Analysis</h6>
                                <p>Anda submit essay tentang "Hukum Newton"</p>
                                <ul>
                                    <li>NLP Score: 78/100</li>
                                    <li>Deteksi: Lemah di konsep gaya</li>
                                    <li>Saran: Perlu materi tambahan</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="workflow-step border-success">
                                <h6 class="text-success">🤝 Step 2: CBF Recommendation</h6>
                                <p>Berdasarkan siswa serupa yang juga lemah di gaya:</p>
                                <ul>
                                    <li>Video "Gaya dan Gerak" (Rating 4.9)</li>
                                    <li>Simulasi Interaktif Newton</li>
                                    <li>Quiz Ringan: Identifikasi Gaya</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="workflow-step border-primary">
                                <h6 class="text-primary">🎯 Step 3: RL Optimization</h6>
                                <p>Urutan belajar optimal untuk Anda:</p>
                                <ul>
                                    <li>1. Tonton video (15 menit)</li>
                                    <li>2. Coba simulasi (10 menit)</li>
                                    <li>3. Kerjakan quiz (5 menit)</li>
                                    <li>Waktu terbaik: Besok pagi 08:00</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-success mt-3">
                        <h6><i class="fas fa-trophy me-2"></i>Hasil Akhir:</h6>
                        <p class="mb-0">Setelah mengikuti rekomendasi AI, essay Anda yang berikutnya mendapat NLP Score: 92/100! 
                        AI terus belajar dari kemajuan Anda untuk memberikan rekomendasi yang lebih baik.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- VARK + NLP Data Fusion (Interactive) -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h4><i class="fas fa-balance-scale me-2"></i>VARK + NLP Data Fusion</h4>
                <p class="mb-0">Simulasikan bagaimana skor VARK (kuisioner) dan NLP (perilaku) digabung dengan bobot adaptif.</p>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="mb-2">VARK (Self-Report) — 1 sampai 10</h6>
                        <div class="mb-2">
                            <label class="form-label">Visual</label>
                            <input id="vark-visual" type="number" class="form-control" value="6" min="1" max="10" step="0.1">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Auditory</label>
                            <input id="vark-auditory" type="number" class="form-control" value="5" min="1" max="10" step="0.1">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Reading</label>
                            <input id="vark-reading" type="number" class="form-control" value="7" min="1" max="10" step="0.1">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Kinesthetic</label>
                            <input id="vark-kinesthetic" type="number" class="form-control" value="4" min="1" max="10" step="0.1">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="mb-2">NLP (Behavior) — 1 sampai 10</h6>
                        <div class="mb-2">
                            <label class="form-label">Visual</label>
                            <input id="nlp-visual" type="number" class="form-control" value="5" min="1" max="10" step="0.1">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Auditory</label>
                            <input id="nlp-auditory" type="number" class="form-control" value="5.5" min="1" max="10" step="0.1">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Reading</label>
                            <input id="nlp-reading" type="number" class="form-control" value="5" min="1" max="10" step="0.1">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Kinesthetic</label>
                            <input id="nlp-kinesthetic" type="number" class="form-control" value="7.5" min="1" max="10" step="0.1">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="mb-2">Kualitas Data NLP</h6>
                        <label for="word-count" class="form-label">Jumlah kata (essay terbaru)</label>
                        <input id="word-count" type="range" class="form-range" min="0" max="500" value="150">
                        <div class="d-flex justify-content-between">
                            <small>0</small>
                            <small><span id="word-count-value">150</span> kata</small>
                            <small>500</small>
                        </div>
                        <hr>
                        <div>
                            <div class="d-flex justify-content-between"><small>W_NLP</small><strong><span id="wnlp">0.50</span></strong></div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div id="wnlp-bar" class="progress-bar bg-info" style="width: 50%"></div>
                            </div>
                            <div class="d-flex justify-content-between"><small>W_VARK</small><strong><span id="wvark">0.50</span></strong></div>
                            <div class="progress" style="height: 6px;">
                                <div id="wvark-bar" class="progress-bar bg-secondary" style="width: 50%"></div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">Threshold θ (multimodal):</small>
                            <strong><span id="theta-value"><?php echo isset($theta) ? htmlspecialchars((string)$theta) : '0.15'; ?></span></strong>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <h6>Hasil Skor Fusi (Fused Scores)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Dimensi</th>
                                        <th>Fused</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>Visual</td><td><strong id="fused-visual">—</strong></td></tr>
                                    <tr><td>Auditory</td><td><strong id="fused-auditory">—</strong></td></tr>
                                    <tr><td>Reading</td><td><strong id="fused-reading">—</strong></td></tr>
                                    <tr><td>Kinesthetic</td><td><strong id="fused-kinesthetic">—</strong></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Keputusan Preferensi</h6>
                        <div class="alert" id="decision-box">
                            <div><small>Top VARK vs NLP:</small> <strong id="mismatch-label">—</strong></div>
                            <div class="mt-1"><small>Label:</small> <strong id="decision-label">—</strong></div>
                            <div class="mt-1 text-muted"><small>Catatan:</small> Jika selisih dua skor tertinggi < θ, sistem memberi label multimodal.</small></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script>
        (function(){
            const ids = [
                'vark-visual','vark-auditory','vark-reading','vark-kinesthetic',
                'nlp-visual','nlp-auditory','nlp-reading','nlp-kinesthetic','word-count'
            ];
            const byId = id => document.getElementById(id);
            const theta = parseFloat(byId('theta-value').textContent) || 0.15;

            function clamp1to10(x){
                x = Number(x);
                if (isNaN(x)) return 1;
                return Math.max(1, Math.min(10, x));
            }

            function calcWeights(wordCount){
                const wc = Number(wordCount) || 0;
                let wnlp = 0.5;
                if (wc < 100) wnlp = 0.3;
                else if (wc >= 300) wnlp = 0.7;
                else wnlp = 0.5;
                return { wnlp, wvark: 1 - wnlp };
            }

            function round2(x){ return Math.round(x * 100) / 100; }

            function topTwo(obj){
                const entries = Object.entries(obj).sort((a,b)=>b[1]-a[1]);
                return { top1: entries[0], top2: entries[1] };
            }

            function update(){
                const vark = {
                    Visual: clamp1to10(byId('vark-visual').value),
                    Auditory: clamp1to10(byId('vark-auditory').value),
                    Reading: clamp1to10(byId('vark-reading').value),
                    Kinesthetic: clamp1to10(byId('vark-kinesthetic').value),
                };
                const nlp = {
                    Visual: clamp1to10(byId('nlp-visual').value),
                    Auditory: clamp1to10(byId('nlp-auditory').value),
                    Reading: clamp1to10(byId('nlp-reading').value),
                    Kinesthetic: clamp1to10(byId('nlp-kinesthetic').value),
                };
                const wc = Number(byId('word-count').value) || 0;
                byId('word-count-value').textContent = wc;

                const { wnlp, wvark } = calcWeights(wc);
                byId('wnlp').textContent = round2(wnlp).toFixed(2);
                byId('wvark').textContent = round2(wvark).toFixed(2);
                byId('wnlp-bar').style.width = (wnlp*100).toFixed(0)+'%';
                byId('wvark-bar').style.width = (wvark*100).toFixed(0)+'%';

                const fused = {
                    Visual: round2(wvark * vark.Visual + wnlp * nlp.Visual),
                    Auditory: round2(wvark * vark.Auditory + wnlp * nlp.Auditory),
                    Reading: round2(wvark * vark.Reading + wnlp * nlp.Reading),
                    Kinesthetic: round2(wvark * vark.Kinesthetic + wnlp * nlp.Kinesthetic),
                };

                byId('fused-visual').textContent = fused.Visual.toFixed(2);
                byId('fused-auditory').textContent = fused.Auditory.toFixed(2);
                byId('fused-reading').textContent = fused.Reading.toFixed(2);
                byId('fused-kinesthetic').textContent = fused.Kinesthetic.toFixed(2);

                const tv = topTwo(vark); const tn = topTwo(nlp); const tf = topTwo(fused);
                const mismatch = tv.top1[0] === tn.top1[0] ? 'Konsisten' : (tv.top1[0]+' vs '+tn.top1[0]);
                byId('mismatch-label').textContent = mismatch;

                const diff = Math.abs(tf.top1[1] - tf.top2[1]);
                const isMulti = diff < theta;
                const label = isMulti ? ('Multimodal ('+tf.top1[0]+'/'+tf.top2[0]+')') : ('Dominan ('+tf.top1[0]+')');
                const box = byId('decision-box');
                box.classList.remove('alert-success','alert-info');
                box.classList.add(isMulti ? 'alert-info' : 'alert-success');
                byId('decision-label').textContent = label + ' — Δ=' + round2(diff).toFixed(2) + ' vs θ=' + theta.toFixed(2);
            }

            ids.forEach(id => {
                const el = byId(id);
                if (!el) return;
                el.addEventListener('input', update);
                el.addEventListener('change', update);
            });
            update();
        })();
    </script>
</div>

<!-- Explanation of Calculation -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle me-2"></i>Penjelasan Perhitungan Skor</h5>
            </div>
            <div class="card-body">
                <p class="mb-2">Bagian ini menjelaskan bagaimana sistem menggabungkan skor VARK (self-report) dan skor NLP (perilaku) untuk menentukan preferensi belajar.</p>
                <ul>
                    <li><strong>Input:</strong> VARK (1–10), NLP (1–10), jumlah kata (proxy kualitas data), dan ambang <code>θ</code> (multimodal) = <strong><?php echo isset($theta) ? htmlspecialchars((string)$theta) : '0.15'; ?></strong>.</li>
                    <li><strong>Bobot:</strong> <code>W_NLP</code> bergantung pada jumlah kata:
                        <div class="mt-1 small">
                            <code>W_NLP = 0.30</code> jika <code>kata &lt; 100</code> ·
                            <code>W_NLP = 0.50</code> jika <code>100 ≤ kata &lt; 300</code> ·
                            <code>W_NLP = 0.70</code> jika <code>kata ≥ 300</code>
                        </div>
                        <div class="small">Kemudian <code>W_VARK = 1 − W_NLP</code>.</div>
                    </li>
                    <li><strong>Fusi Skor (per dimensi):</strong>
                        <div class="mt-1"><code>Fused(d) = round2( W_VARK × VARK(d) + W_NLP × NLP(d) )</code>, untuk d ∈ {Visual, Auditory, Reading, Kinesthetic}.</div>
                    </li>
                    <li><strong>Keputusan Label:</strong>
                        <div class="mt-1 small">Urutkan skor <em>Fused</em> menurun → ambil dua teratas s1 ≥ s2 → hitung Δ = |s1 − s2|.</div>
                        <div class="small">Jika <code>Δ &lt; θ</code> → <strong>Multimodal</strong> (nama dua teratas). Jika tidak → <strong>Dominan</strong> (nama tertinggi).</div>
                    </li>
                    <li><strong>Indikator Mismatch:</strong> Bandingkan dimensi tertinggi VARK vs NLP. Jika berbeda → ditampilkan sebagai <em>mismatch</em> untuk transparansi.</li>
                </ul>
                <p class="text-muted small mb-0">Catatan: Di backend, nilai NLP (0–1) dan hasil kuisioner (mis. 0–16) dinormalisasi ke skala 1–10 sebelum difusi.</p>
            </div>
        </div>
    </div>
    
</div>
