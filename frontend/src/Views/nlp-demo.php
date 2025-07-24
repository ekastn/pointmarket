<?php
// Data for this view will be passed from the NLPDemoController
$user = $user ?? ['name' => 'Guest'];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-brain me-2"></i>
        Demo NLP Analysis
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="loadExample('good')">
                <i class="fas fa-thumbs-up me-1"></i>Contoh Baik
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="loadExample('bad')">
                <i class="fas fa-thumbs-down me-1"></i>Contoh Buruk
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAll()">
                <i class="fas fa-eraser me-1"></i>Clear
            </button>
            <button type="button" class="btn btn-sm btn-outline-info" onclick="testAPI()">
                <i class="fas fa-stethoscope me-1"></i>Test API
            </button>
        </div>
    </div>
</div>

<!-- Info Alert -->
<div class="alert alert-info mb-4">
    <h6><i class="fas fa-info-circle me-2"></i>Tentang NLP Analysis</h6>
    <p class="mb-2">Sistem NLP (Natural Language Processing) POINTMARKET menganalisis teks Anda berdasarkan beberapa faktor:</p>
    <div class="row">
        <div class="col-md-6">
            <ul class="mb-0">
                <li><strong>Grammar:</strong> Tata bahasa dan ejaan</li>
                <li><strong>Keywords:</strong> Kata kunci yang relevan</li>
                <li><strong>Structure:</strong> Organisasi dan alur</li>
            </ul>
        </div>
        <div class="col-md-6">
            <ul class="mb-0">
                <li><strong>Readability:</strong> Keterbacaan teks</li>
                <li><strong>Sentiment:</strong> Tone positif/negatif</li>
                <li><strong>Complexity:</strong> Tingkat kompleksitas</li>
            </ul>
        </div>
    </div>
</div>

<!-- Demo Form -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-edit me-2"></i>Coba Analisis Teks Anda</h5>
    </div>
    <div class="card-body">
        <form id="nlpDemoForm">
            <div class="mb-3">
                <label for="demo-text" class="form-label">Tulis teks yang ingin dianalisis:</label>
                <textarea 
                    id="demo-text" 
                    name="demo-text" 
                    class="form-control" 
                    rows="8" 
                    placeholder="Contoh: Teknologi dalam pendidikan sangat penting karena dapat meningkatkan kualitas pembelajaran. Dengan adanya komputer dan internet, siswa dapat mengakses berbagai sumber belajar yang tidak terbatas..."
                    data-nlp="true"
                    data-context="assignment"
                ></textarea>
                <div class="form-text">
                    <i class="fas fa-info-circle me-1"></i>
                    Minimal 10 karakter untuk analisis. Analisis otomatis akan berjalan 3 detik setelah berhenti mengetik.
                </div>
            </div>
            
            <div class="mb-3">
                <label for="context-select" class="form-label">Konteks:</label>
                <select id="context-select" class="form-select" onchange="updateContext()">
                    <option value="assignment">Assignment (Tugas)</option>
                    <option value="matematik">Matematika</option>
                    <option value="fisika">Fisika</option>
                    <option value="kimia">Kimia</option>
                    <option value="biologi">Biologi</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Examples -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6><i class="fas fa-star me-2"></i>Contoh Teks Berkualitas Tinggi</h6>
            </div>
            <div class="card-body">
                <div class="example-text">
                    <p><strong>Topik:</strong> Teknologi dalam Pendidikan</p>
                    <p>"Teknologi dalam pendidikan memainkan peran yang sangat penting dalam meningkatkan kualitas pembelajaran. Pertama, teknologi menyediakan akses ke sumber belajar yang tidak terbatas melalui internet. Kedua, aplikasi pembelajaran interaktif memungkinkan siswa untuk belajar dengan cara yang lebih menarik dan efektif. Ketiga, platform digital memfasilitasi komunikasi antara guru dan siswa di luar jam sekolah. Dengan demikian, integrasi teknologi dalam pendidikan bukan hanya trend, tetapi kebutuhan fundamental untuk menciptakan sistem pembelajaran yang adaptif dan berkelanjutan."</p>
                </div>
                <div class="mt-2">
                    <span class="score-badge score-high">Prediksi Score: 85-92</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Contoh Teks Perlu Perbaikan</h6>
            </div>
            <div class="card-body">
                <div class="example-text">
                    <p><strong>Topik:</strong> Teknologi dalam Pendidikan</p>
                    <p>"teknologi bagus untuk sekolah karena bisa belajar dengan komputer dan internet juga bisa cari materi di google terus bisa ngerjain tugas lebih gampang pokoknya teknologi sangat membantu"</p>
                </div>
                <div class="mt-2">
                    <span class="score-badge score-low">Prediksi Score: 35-45</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="card mt-4">
    <div class="card-header">
        <h6><i class="fas fa-chart-bar me-2"></i>Statistik Analisis Anda</h6>
    </div>
    <div class="card-body">
        <div id="user-stats">
            <div class="text-center text-muted">
                <i class="fas fa-chart-line fa-2x mb-2"></i>
                <p>Lakukan analisis pertama untuk melihat statistik</p>
            </div>
        </div>
    </div>
</div>

<!-- Navigation -->
<div class="text-center mt-4">
    <a href="/assignments" class="btn btn-primary me-2">
        <i class="fas fa-tasks me-1"></i>Coba di Assignment
    </a>
    <a href="/ai-explanation" class="btn btn-secondary me-2">
        <i class="fas fa-book me-1"></i>Pelajari Lebih Lanjut
    </a>
    <a href="/dashboard" class="btn btn-outline-secondary">
        <i class="fas fa-home me-1"></i>Kembali ke Dashboard
    </a>
</div>