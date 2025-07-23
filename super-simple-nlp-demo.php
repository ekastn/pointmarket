<?php
/**
 * Super Simple NLP Demo
 * 
 * This is the most stripped-down version of the NLP demo page
 * with no dependencies on external files or authentication.
 */

// Start session (untuk kompatibilitas jika diperlukan)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Simulasi session user (tanpa memerlukan autentikasi database)
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'test_user';
$_SESSION['name'] = 'Test User';
$_SESSION['email'] = 'test@example.com';
$_SESSION['role'] = 'siswa';

// Buat variabel user yang diperlukan oleh navbar dan sidebar
$user = [
    'id' => $_SESSION['user_id'],
    'username' => $_SESSION['username'],
    'name' => $_SESSION['name'],
    'email' => $_SESSION['email'],
    'role' => $_SESSION['role']
];

// Fungsi untuk mencatat error ke file log
function logError($message) {
    $logFile = __DIR__ . '/nlp-demo-error.log';
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, $logFile);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo NLP Analysis (Simple) - POINTMARKET</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { padding-top: 20px; }
        .container { max-width: 900px; }
        .example-text {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 15px 0;
            border-radius: 0 8px 8px 0;
        }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; }
        .score-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .score-high { background-color: #d1edff; color: #0c63e4; }
        .score-medium { background-color: #fff3cd; color: #664d03; }
        .score-low { background-color: #f8d7da; color: #721c24; }
        
        /* Enhanced dropdown styling */
        .dropdown-menu {
            max-height: 400px;
            overflow-y: auto;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .dropdown-header {
            color: #007bff;
            font-weight: 600;
            font-size: 0.875rem;
            background-color: #f8f9fa;
        }
        
        .dropdown-item {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            transition: all 0.15s ease-in-out;
        }
        
        .dropdown-item:hover {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        
        /* Textarea enhancement */
        #text-input {
            transition: background-color 0.3s ease;
        }
        
        #text-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        /* Button enhancements */
        .btn-outline-secondary:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,123,255,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-brain me-2"></i>POINTMARKET NLP Demo (Simplified)</h4>
            </div>
            <div class="card-body">
                <p class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Simplified Demo:</strong> Ini adalah versi paling sederhana dari demo NLP yang tidak memerlukan file eksternal atau database.
                    Halaman ini menggunakan API mini untuk memastikan selalu dapat menampilkan respons valid.
                </p>
                
                <form id="nlp-form" class="mb-4">
                    <div class="mb-3">
                        <label for="text-input" class="form-label">Teks untuk Dianalisis:</label>
                        <textarea id="text-input" class="form-control" rows="5" placeholder="Masukkan teks di sini..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="context-select" class="form-label">Konteks:</label>
                        <select id="context-select" class="form-select">
                            <option value="assignment">Assignment (Tugas)</option>
                            <option value="matematik">Matematika</option>
                            <option value="fisika">Fisika</option>
                            <option value="kimia">Kimia</option>
                            <option value="biologi">Biologi</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="dropdown d-inline-block me-2">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="exampleDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-lightbulb me-1"></i>Gunakan Contoh
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="exampleDropdown">
                                    <li><h6 class="dropdown-header">Assignment (Tugas)</h6></li>
                                    <li><a class="dropdown-item" href="#" data-example="assignment1">Pentingnya Teknologi Pendidikan</a></li>
                                    <li><a class="dropdown-item" href="#" data-example="assignment2">Dampak Media Sosial</a></li>
                                    <li><a class="dropdown-item" href="#" data-example="assignment3">Lingkungan Hidup</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Matematika</h6></li>
                                    <li><a class="dropdown-item" href="#" data-example="matematik1">Konsep Fungsi Linear</a></li>
                                    <li><a class="dropdown-item" href="#" data-example="matematik2">Geometri Ruang</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Fisika</h6></li>
                                    <li><a class="dropdown-item" href="#" data-example="fisika1">Hukum Newton</a></li>
                                    <li><a class="dropdown-item" href="#" data-example="fisika2">Energi dan Momentum</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Kimia</h6></li>
                                    <li><a class="dropdown-item" href="#" data-example="kimia1">Reaksi Kimia</a></li>
                                    <li><a class="dropdown-item" href="#" data-example="kimia2">Struktur Atom</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Biologi</h6></li>
                                    <li><a class="dropdown-item" href="#" data-example="biologi1">Sistem Peredaran Darah</a></li>
                                    <li><a class="dropdown-item" href="#" data-example="biologi2">Fotosintesis</a></li>
                                </ul>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="clear-text">
                                <i class="fas fa-eraser me-1"></i>Clear
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Analisis Teks
                        </button>
                    </div>
                </form>
                
                <div id="analysis-results" style="display: none;">
                    <h5 class="border-bottom pb-2 mb-3">Hasil Analisis</h5>
                    
                    <div id="loading-indicator" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Menganalisis teks...</p>
                    </div>
                    
                    <div id="results-content" style="display: none;">
                        <!-- Results will be populated here by JavaScript -->
                    </div>
                    
                    <div id="api-error" class="alert alert-danger" style="display: none;"></div>
                </div>
                
                <div class="mt-4">
                    <h5 class="border-bottom pb-2 mb-3">Debugging Tools</h5>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <button id="test-api" class="btn btn-outline-primary btn-sm w-100">
                                <i class="fas fa-vial me-1"></i>Test API
                            </button>
                        </div>
                        <div class="col-md-4 mb-2">
                            <button id="view-session" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fas fa-key me-1"></i>View Session
                            </button>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="nlp-diagnostics.php" class="btn btn-outline-info btn-sm w-100">
                                <i class="fas fa-microscope me-1"></i>Run Diagnostics
                            </a>
                        </div>
                    </div>
                    
                    <div id="debug-output" class="mt-3" style="display: none;">
                        <pre id="debug-content"></pre>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mb-4">
            <a href="index.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-home me-1"></i>Back to Home
            </a>
            <a href="panduan-nlp-demo-simplified.html" class="btn btn-sm btn-outline-info ms-2">
                <i class="fas fa-book me-1"></i>Lihat Panduan Penggunaan
            </a>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cache DOM elements
            const nlpForm = document.getElementById('nlp-form');
            const textInput = document.getElementById('text-input');
            const contextSelect = document.getElementById('context-select');
            const clearTextBtn = document.getElementById('clear-text');
            const analysisResults = document.getElementById('analysis-results');
            const loadingIndicator = document.getElementById('loading-indicator');
            const resultsContent = document.getElementById('results-content');
            const apiError = document.getElementById('api-error');
            const testApiBtn = document.getElementById('test-api');
            const viewSessionBtn = document.getElementById('view-session');
            const debugOutput = document.getElementById('debug-output');
            const debugContent = document.getElementById('debug-content');
            
            // Example texts for different contexts
            const exampleTexts = {
                assignment1: {
                    text: "Teknologi dalam pendidikan memainkan peran yang sangat penting dalam meningkatkan kualitas pembelajaran di era digital. Pertama, teknologi menyediakan akses ke sumber belajar yang tidak terbatas melalui internet, memungkinkan siswa untuk mengeksplorasi materi pembelajaran dari berbagai perspektif dan sumber yang kredibel. Kedua, aplikasi pembelajaran interaktif seperti simulasi, game edukasi, dan platform e-learning memungkinkan siswa untuk belajar dengan cara yang lebih menarik, efektif, dan sesuai dengan gaya belajar masing-masing. Ketiga, platform digital memfasilitasi komunikasi dan kolaborasi antara guru dan siswa di luar jam sekolah, menciptakan lingkungan belajar yang lebih fleksibel dan responsif. Keempat, teknologi memungkinkan personalisasi pembelajaran melalui sistem adaptif yang dapat menyesuaikan konten dan kecepatan belajar dengan kemampuan individual siswa. Dengan demikian, integrasi teknologi dalam pendidikan bukan hanya sebuah tren, tetapi kebutuhan fundamental untuk mempersiapkan generasi masa depan yang kompeten dan siap menghadapi tantangan global.",
                    context: "assignment"
                },
                assignment2: {
                    text: "Media sosial telah mengubah cara manusia berinteraksi dan berkomunikasi secara fundamental dalam beberapa dekade terakhir. Di satu sisi, media sosial memberikan dampak positif yang signifikan seperti memudahkan komunikasi jarak jauh, memperluas jaringan sosial, dan memberikan platform untuk ekspresi diri dan kreativitas. Platform seperti Facebook, Instagram, dan Twitter memungkinkan orang untuk tetap terhubung dengan keluarga dan teman, berbagi pengalaman, dan membangun komunitas berdasarkan minat yang sama. Di sisi lain, media sosial juga membawa dampak negatif yang perlu diwaspadai, seperti penyebaran informasi palsu atau hoaks, cyberbullying, kecanduan digital, dan masalah privasi data. Fenomena FOMO (Fear of Missing Out) dan tekanan untuk tampil sempurna di media sosial dapat menyebabkan masalah kesehatan mental, terutama pada remaja. Oleh karena itu, penggunaan media sosial yang bijak dan bertanggung jawab menjadi kunci untuk memaksimalkan manfaatnya sambil meminimalkan risiko yang ada.",
                    context: "assignment"
                },
                assignment3: {
                    text: "Pelestarian lingkungan hidup merupakan tanggung jawab bersama yang memerlukan komitmen dari semua pihak untuk memastikan keberlanjutan planet ini bagi generasi mendatang. Krisis lingkungan yang kita hadapi saat ini, seperti perubahan iklim, pencemaran udara dan air, deforestasi, dan kepunahan spesies, memerlukan tindakan nyata dan segera. Setiap individu dapat berperan dalam pelestarian lingkungan melalui tindakan sederhana namun berdampak, seperti mengurangi penggunaan plastik sekali pakai, menghemat energi listrik, menggunakan transportasi ramah lingkungan, dan menerapkan prinsip 3R (Reduce, Reuse, Recycle) dalam kehidupan sehari-hari. Pemerintah dan perusahaan juga memiliki peran krusial dalam menciptakan kebijakan dan praktik bisnis yang berkelanjutan. Pendidikan lingkungan sejak dini sangat penting untuk membentuk kesadaran dan perilaku yang peduli terhadap alam. Dengan kerja sama dan komitmen bersama, kita dapat menjaga kelestarian lingkungan dan menciptakan masa depan yang lebih hijau dan berkelanjutan.",
                    context: "assignment"
                },
                matematik1: {
                    text: "Fungsi linear merupakan salah satu konsep fundamental dalam matematika yang memiliki aplikasi luas dalam kehidupan sehari-hari dan berbagai bidang ilmu. Secara matematis, fungsi linear adalah fungsi yang dapat dinyatakan dalam bentuk f(x) = ax + b, dimana a dan b adalah konstanta dan a ≠ 0. Karakteristik utama fungsi linear adalah grafik yang berupa garis lurus dengan kemiringan konstan. Koefisien a menentukan kemiringan (slope) garis, sedangkan konstanta b menentukan titik potong dengan sumbu y (y-intercept). Fungsi linear banyak digunakan dalam pemodelan hubungan proporsional, seperti menghitung biaya produksi, konversi suhu, dan analisis ekonomi sederhana. Pemahaman yang baik tentang fungsi linear menjadi dasar untuk mempelajari konsep matematika yang lebih kompleks seperti sistem persamaan linear, program linear, dan kalkulus.",
                    context: "matematik"
                },
                matematik2: {
                    text: "Geometri ruang atau geometri tiga dimensi mempelajari sifat-sifat dan hubungan antara titik, garis, bidang, dan bangun ruang dalam ruang tiga dimensi. Konsep dasar geometri ruang meliputi koordinat tiga dimensi (x, y, z), jarak antara dua titik, persamaan bidang, dan persamaan garis dalam ruang. Bangun ruang seperti kubus, balok, prisma, limas, tabung, kerucut, dan bola memiliki rumus volume dan luas permukaan yang spesifik. Aplikasi geometri ruang sangat luas dalam kehidupan nyata, mulai dari arsitektur dan konstruksi bangunan, desain produk industri, animasi komputer, hingga navigasi GPS. Pemahaman geometri ruang juga penting dalam bidang fisika untuk memahami konsep vektor, momentum sudut, dan medan elektromagnetik. Kemampuan visualisasi spasial yang dikembangkan melalui pembelajaran geometri ruang sangat bermanfaat dalam pemecahan masalah teknis dan kreatif.",
                    context: "matematik"
                },
                fisika1: {
                    text: "Hukum Newton tentang gerak merupakan fondasi mekanika klasik yang menjelaskan hubungan antara gaya dan gerak benda. Hukum pertama Newton atau hukum inersia menyatakan bahwa benda yang diam akan tetap diam dan benda yang bergerak akan tetap bergerak dengan kecepatan konstan dalam garis lurus, kecuali ada gaya eksternal yang bekerja padanya. Hukum kedua Newton menyatakan bahwa percepatan suatu benda berbanding lurus dengan gaya total yang bekerja padanya dan berbanding terbalik dengan massanya, yang dinyatakan dalam rumus F = ma. Hukum ketiga Newton menyatakan bahwa untuk setiap aksi terdapat reaksi yang sama besar tetapi berlawanan arah. Ketiga hukum ini dapat diamati dalam kehidupan sehari-hari, seperti saat kita berjalan, mengendarai kendaraan, atau melempar bola. Pemahaman hukum Newton sangat penting dalam rekayasa, astronautika, dan teknologi transportasi modern.",
                    context: "fisika"
                },
                fisika2: {
                    text: "Konsep energi dan momentum merupakan dua besaran fundamental dalam fisika yang berperan penting dalam analisis gerak dan tumbukan. Energi adalah kemampuan untuk melakukan usaha dan dapat berubah bentuk dari satu jenis ke jenis lainnya, seperti energi kinetik, energi potensial, energi panas, dan energi listrik. Hukum kekekalan energi menyatakan bahwa energi tidak dapat diciptakan atau dimusnahkan, tetapi hanya dapat diubah dari satu bentuk ke bentuk lainnya. Momentum adalah besaran vektor yang didefinisikan sebagai perkalian antara massa dan kecepatan benda (p = mv). Hukum kekekalan momentum menyatakan bahwa momentum total sistem tertutup akan tetap konstan jika tidak ada gaya eksternal yang bekerja. Aplikasi konsep energi dan momentum dapat ditemukan dalam analisis tumbukan kendaraan, desain roller coaster, peluncuran roket, dan pembangkit listrik. Pemahaman kedua konsep ini sangat penting dalam pengembangan teknologi dan keselamatan transportasi.",
                    context: "fisika"
                },
                kimia1: {
                    text: "Reaksi kimia adalah proses dimana satu atau lebih zat (reaktan) berubah menjadi zat lain (produk) dengan susunan atom yang berbeda. Dalam reaksi kimia, ikatan kimia antara atom-atom dalam reaktan putus dan terbentuk ikatan baru dalam produk, namun jenis dan jumlah atom tetap sama sesuai dengan hukum kekekalan massa. Reaksi kimia dapat diklasifikasikan menjadi beberapa jenis, seperti reaksi sintesis (penggabungan), reaksi dekomposisi (penguraian), reaksi substitusi (penggantian), dan reaksi pertukaran ganda. Faktor-faktor yang mempengaruhi laju reaksi kimia meliputi konsentrasi reaktan, suhu, luas permukaan, dan keberadaan katalis. Reaksi kimia terjadi di mana-mana dalam kehidupan sehari-hari, mulai dari proses metabolisme dalam tubuh, pembakaran bahan bakar, fotosintesis pada tumbuhan, hingga proses industri pembuatan berbagai produk kimia. Pemahaman reaksi kimia sangat penting dalam pengembangan obat-obatan, material baru, dan teknologi ramah lingkungan.",
                    context: "kimia"
                },
                kimia2: {
                    text: "Struktur atom merupakan konsep fundamental dalam kimia yang menjelaskan susunan partikel-partikel subatomik dalam atom. Atom terdiri dari inti atom (nukleus) yang bermuatan positif dan dikelilingi oleh elektron yang bermuatan negatif. Inti atom mengandung proton yang bermuatan positif dan neutron yang tidak bermuatan. Model atom modern menggambarkan elektron sebagai awan probabilitas yang mengelilingi inti dalam orbital-orbital dengan tingkat energi tertentu. Konfigurasi elektron dalam orbital menentukan sifat kimia unsur, seperti kemampuan membentuk ikatan kimia dan reaktivitas. Tabel periodik unsur disusun berdasarkan nomor atom (jumlah proton) dan menunjukkan pola periodik sifat-sifat unsur. Pemahaman struktur atom sangat penting untuk memahami ikatan kimia, sifat material, spektroskopi, dan teknologi nuklir. Konsep ini juga menjadi dasar pengembangan teknologi modern seperti laser, transistor, dan panel surya.",
                    context: "kimia"
                },
                biologi1: {
                    text: "Sistem peredaran darah manusia merupakan sistem transportasi vital yang berfungsi mengangkut oksigen, nutrisi, hormon, dan zat-zat penting lainnya ke seluruh tubuh, serta mengangkut limbah metabolisme untuk dibuang. Sistem ini terdiri dari jantung sebagai pompa utama, pembuluh darah sebagai saluran transportasi, dan darah sebagai medium pengangkut. Jantung memiliki empat ruang yaitu dua atrium (serambi) dan dua ventrikel (bilik) yang bekerja secara terkoordinasi dalam siklus jantung. Pembuluh darah terbagi menjadi arteri yang mengangkut darah dari jantung, vena yang mengangkut darah kembali ke jantung, dan kapiler yang memfasilitasi pertukaran zat antara darah dan jaringan. Darah mengandung sel darah merah (eritrosit) yang membawa oksigen, sel darah putih (leukosit) yang berperan dalam sistem imun, keping darah (trombosit) untuk pembekuan darah, dan plasma sebagai medium cair. Gangguan pada sistem peredaran darah seperti hipertensi, aterosklerosis, dan penyakit jantung koroner dapat berdampak serius pada kesehatan.",
                    context: "biologi"
                },
                biologi2: {
                    text: "Fotosintesis adalah proses biokimia fundamental yang dilakukan oleh tumbuhan, alga, dan beberapa jenis bakteri untuk mengubah energi cahaya matahari menjadi energi kimia dalam bentuk glukosa. Proses ini terjadi di kloroplas, khususnya di bagian tilakoid yang mengandung pigmen klorofil. Fotosintesis terdiri dari dua tahap utama yaitu reaksi terang (foto-reaksi) dan reaksi gelap (siklus Calvin). Dalam reaksi terang, energi cahaya diserap oleh klorofil untuk memecah molekul air (H2O) menjadi hidrogen dan oksigen, sambil menghasilkan ATP dan NADPH. Dalam reaksi gelap, CO2 dari atmosfer difiksasi menjadi glukosa menggunakan energi dari ATP dan NADPH yang dihasilkan pada reaksi terang. Fotosintesis sangat penting bagi kehidupan di Bumi karena menghasilkan oksigen yang diperlukan untuk respirasi dan menjadi dasar rantai makanan. Proses ini juga berperan dalam mengurangi kadar CO2 di atmosfer, sehingga membantu mengatasi efek rumah kaca dan perubahan iklim.",
                    context: "biologi"
                }
            };
            
            // Load example text based on selection
            document.querySelectorAll('[data-example]').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const exampleKey = this.getAttribute('data-example');
                    const example = exampleTexts[exampleKey];
                    
                    if (example) {
                        textInput.value = example.text;
                        contextSelect.value = example.context;
                        
                        // Add a small animation to show the text was loaded
                        textInput.style.backgroundColor = '#e3f2fd';
                        setTimeout(() => {
                            textInput.style.backgroundColor = '';
                        }, 500);
                    }
                });
            });
            
            // Clear text
            clearTextBtn.addEventListener('click', function() {
                textInput.value = '';
                analysisResults.style.display = 'none';
            });
            
            // Handle form submission
            nlpForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const text = textInput.value.trim();
                const context = contextSelect.value;
                
                if (!text) {
                    alert('Please enter some text to analyze.');
                    return;
                }
                
                // Show results area and loading indicator
                analysisResults.style.display = 'block';
                loadingIndicator.style.display = 'block';
                resultsContent.style.display = 'none';
                apiError.style.display = 'none';
                
                // Scroll to results
                analysisResults.scrollIntoView({ behavior: 'smooth' });
                
                // Call the mini API (which always returns valid JSON)
                fetch('api/nlp-mini-api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        text: text,
                        context: context
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('API response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    // Hide loading, show results
                    loadingIndicator.style.display = 'none';
                    resultsContent.style.display = 'block';
                    
                    // Render the results
                    renderResults(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    loadingIndicator.style.display = 'none';
                    apiError.style.display = 'block';
                    apiError.textContent = 'Error analyzing text: ' + error.message;
                });
            });
            
            // Test API button
            testApiBtn.addEventListener('click', function() {
                debugOutput.style.display = 'block';
                debugContent.textContent = 'Testing API...';
                
                fetch('api/nlp-mini-api.php?test=1&v=' + Date.now())
                    .then(response => response.json())
                    .then(data => {
                        debugContent.textContent = JSON.stringify(data, null, 2);
                    })
                    .catch(error => {
                        debugContent.textContent = 'Error testing API: ' + error.message;
                    });
            });
            
            // View session button
            viewSessionBtn.addEventListener('click', function() {
                debugOutput.style.display = 'block';
                debugContent.textContent = 'Loading session data...';
                
                fetch('get-session-info.php')
                    .then(response => response.json())
                    .then(data => {
                        debugContent.textContent = JSON.stringify(data, null, 2);
                    })
                    .catch(error => {
                        debugContent.textContent = 'Error getting session data: ' + error.message;
                    });
            });
            
            // Render analysis results
            function renderResults(data) {
                let html = `
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <h3>${data.sentiment.score.toFixed(1)}</h3>
                                    <p class="text-muted mb-0">Sentiment Score</p>
                                    <span class="score-badge ${getScoreClass(data.sentiment.score)}">
                                        ${data.sentiment.label}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <h3>${data.complexity.score.toFixed(1)}</h3>
                                    <p class="text-muted mb-0">Complexity Score</p>
                                    <span class="score-badge ${getScoreClass(data.complexity.score)}">
                                        ${data.complexity.label}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <h3>${data.coherence.score.toFixed(1)}</h3>
                                    <p class="text-muted mb-0">Coherence Score</p>
                                    <span class="score-badge ${getScoreClass(data.coherence.score)}">
                                        ${data.coherence.label}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h6>Kata Kunci:</h6>
                    <div class="mb-4">
                `;
                
                // Add keywords
                if (data.keywords && data.keywords.length > 0) {
                    data.keywords.forEach(keyword => {
                        html += `<span class="badge bg-primary me-2 mb-2">${keyword}</span>`;
                    });
                } else {
                    html += `<p class="text-muted">Tidak ada kata kunci yang signifikan ditemukan.</p>`;
                }
                
                html += `
                    </div>
                    
                    <h6>Kalimat Penting:</h6>
                    <div class="mb-4">
                `;
                
                // Add key sentences
                if (data.keySentences && data.keySentences.length > 0) {
                    html += `<ul class="list-group">`;
                    data.keySentences.forEach(sentence => {
                        html += `<li class="list-group-item">${sentence}</li>`;
                    });
                    html += `</ul>`;
                } else {
                    html += `<p class="text-muted">Tidak ada kalimat penting yang ditemukan.</p>`;
                }
                
                html += `
                    </div>
                    
                    <h6>Statistik Teks:</h6>
                    <div class="row text-stats">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body py-2 text-center">
                                    <h4>${data.stats.wordCount}</h4>
                                    <small class="text-muted">Kata</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body py-2 text-center">
                                    <h4>${data.stats.sentenceCount}</h4>
                                    <small class="text-muted">Kalimat</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body py-2 text-center">
                                    <h4>${data.stats.avgWordLength.toFixed(1)}</h4>
                                    <small class="text-muted">Rata-rata Panjang Kata</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body py-2 text-center">
                                    <h4>${data.stats.readingTime}</h4>
                                    <small class="text-muted">Waktu Baca (detik)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                resultsContent.innerHTML = html;
            }
            
            // Get score class based on value
            function getScoreClass(score) {
                if (score >= 0.7) return 'score-high';
                if (score >= 0.4) return 'score-medium';
                return 'score-low';
            }
        });
    </script>
    
    <!-- Bootstrap JS for dropdown functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
