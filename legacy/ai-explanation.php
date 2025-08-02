<?php
require_once 'includes/config.php';
requireLogin();

$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Explanation - POINTMARKET</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .ai-card {
            border-left: 5px solid;
            transition: transform 0.3s ease;
        }
        .ai-card:hover {
            transform: translateY(-5px);
        }
        .nlp-card { border-left-color: #17a2b8; }
        .rl-card { border-left-color: #007bff; }
        .cbf-card { border-left-color: #28a745; }
        
        .score-demo {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0;
        }
        
        .workflow-step {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .example-text {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 10px;
            margin: 10px 0;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
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

                <!-- Demo Button -->
                <div class="row">
                    <div class="col-12 text-center">
                        <a href="dashboard.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>
                            Kembali ke Dashboard
                        </a>
                        <button class="btn btn-success btn-lg ms-3" onclick="demoAI()">
                            <i class="fas fa-play me-2"></i>
                            Demo AI Simulation
                        </button>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function demoAI() {
            alert('Demo AI akan menunjukkan simulasi real-time dari ketiga teknologi AI ini. Fitur akan segera tersedia!');
        }
        
        // Smooth scrolling untuk links internal
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
