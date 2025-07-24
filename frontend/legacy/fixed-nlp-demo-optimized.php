<?php
/**
 * Fixed and Improved NLP Demo Page for POINTMARKET
 * 
 * This is a comprehensive solution to fix the blank page issues
 * in the NLP demo and improve error handling.
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fungsi untuk mencatat error ke file log
function logError($message) {
    $logFile = __DIR__ . '/nlp-demo-error.log';
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, $logFile);
}

// Start session first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include required files with error handling
try {
    require_once 'includes/config.php';
} catch (Exception $e) {
    logError("Error loading config file: " . $e->getMessage());
    echo "Error loading config file. Please check error logs.";
    exit;
}

// Temporarily create a test user session for debugging
// Hapus bagian ini pada versi produksi
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'test_user';
    $_SESSION['name'] = 'Test User';
    $_SESSION['email'] = 'test@example.com';
    $_SESSION['role'] = 'siswa';
}

// Get current user with error handling
try {
    if (function_exists('getCurrentUser')) {
        $user = getCurrentUser();
    } else {
        $user = [
            'id' => $_SESSION['user_id'] ?? 1,
            'username' => $_SESSION['username'] ?? 'test_user',
            'name' => $_SESSION['name'] ?? 'Test User',
            'email' => $_SESSION['email'] ?? 'test@example.com',
            'role' => $_SESSION['role'] ?? 'siswa'
        ];
    }
} catch (Exception $e) {
    logError("Error getting current user: " . $e->getMessage());
    $user = [
        'id' => 1,
        'username' => 'test_user',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'siswa'
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo NLP Analysis - POINTMARKET</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        .demo-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .example-text {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 15px 0;
            border-radius: 0 8px 8px 0;
        }
        .nlp-results-container {
            margin-top: 20px;
        }
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Navigation -->
            <?php 
            try {
                include 'includes/navbar.php'; 
            } catch (Exception $e) {
                logError("Navbar include error: " . $e->getMessage());
                echo '<div class="alert alert-danger">Error loading navigation: ' . $e->getMessage() . '</div>';
            }
            ?>
            
            <!-- Sidebar -->
            <?php 
            try {
                include 'includes/sidebar.php'; 
            } catch (Exception $e) {
                logError("Sidebar include error: " . $e->getMessage());
                echo '<div class="alert alert-danger">Error loading sidebar: ' . $e->getMessage() . '</div>';
            }
            ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Demo Analisis NLP</h1>
                </div>
                
                <div class="demo-container">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Demo Analisis Teks dengan Natural Language Processing</h5>
                            <p class="card-text">
                                Demo ini menggunakan teknologi Natural Language Processing (NLP) untuk menganalisis teks.
                                Masukkan teks yang ingin dianalisis dalam form di bawah ini.
                            </p>
                            
                            <div class="example-text">
                                <h6>Contoh Teks:</h6>
                                <p>Saya sangat senang dengan pelajaran bahasa Indonesia hari ini. Guru menjelaskan dengan baik dan memberikan contoh yang mudah dipahami.</p>
                                <button class="btn btn-sm btn-outline-primary use-example">Gunakan Contoh</button>
                            </div>
                            
                            <form id="nlp-form" class="mt-4">
                                <div class="mb-3">
                                    <label for="text-input" class="form-label">Teks untuk Dianalisis:</label>
                                    <textarea id="text-input" class="form-control" rows="5" placeholder="Masukkan teks di sini..."></textarea>
                                </div>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i> Analisis Teks
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Results Section -->
                    <div id="nlp-results" class="nlp-results-container" style="display: none;">
                        <div class="card shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Hasil Analisis NLP</h5>
                            </div>
                            <div class="card-body">
                                <div id="loading-results" style="display: none;">
                                    <div class="d-flex justify-content-center my-3">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                    <p class="text-center text-muted">Sedang menganalisis teks...</p>
                                </div>
                                
                                <div id="results-content">
                                    <div class="row mb-4">
                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100 border-0 shadow-sm">
                                                <div class="card-body text-center">
                                                    <h3 class="sentiment-score">0</h3>
                                                    <p class="text-muted mb-0">Sentiment Score</p>
                                                    <span class="score-badge sentiment-badge mt-2">Neutral</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100 border-0 shadow-sm">
                                                <div class="card-body text-center">
                                                    <h3 class="complexity-score">0</h3>
                                                    <p class="text-muted mb-0">Complexity Score</p>
                                                    <span class="score-badge complexity-badge mt-2">Medium</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100 border-0 shadow-sm">
                                                <div class="card-body text-center">
                                                    <h3 class="coherence-score">0</h3>
                                                    <p class="text-muted mb-0">Coherence Score</p>
                                                    <span class="score-badge coherence-badge mt-2">Medium</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <h6>Analisis Kata Kunci:</h6>
                                    <div id="keywords-container" class="mb-4"></div>
                                    
                                    <h6>Kalimat Penting:</h6>
                                    <div id="key-sentences"></div>
                                    
                                    <h6 class="mt-3">Statistik Teks:</h6>
                                    <div class="row text-stats">
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body py-2 text-center">
                                                    <h4 class="word-count">0</h4>
                                                    <small class="text-muted">Kata</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body py-2 text-center">
                                                    <h4 class="sentence-count">0</h4>
                                                    <small class="text-muted">Kalimat</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body py-2 text-center">
                                                    <h4 class="avg-word-length">0</h4>
                                                    <small class="text-muted">Rata-rata Panjang Kata</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body py-2 text-center">
                                                    <h4 class="reading-time">0</h4>
                                                    <small class="text-muted">Waktu Baca (detik)</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="error-message" class="alert alert-danger mt-3" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nlpForm = document.getElementById('nlp-form');
            const textInput = document.getElementById('text-input');
            const resultsSection = document.getElementById('nlp-results');
            const loadingResults = document.getElementById('loading-results');
            const resultsContent = document.getElementById('results-content');
            const errorMessage = document.getElementById('error-message');
            const useExampleBtn = document.querySelector('.use-example');
            
            // Use example text
            useExampleBtn.addEventListener('click', function() {
                textInput.value = 'Saya sangat senang dengan pelajaran bahasa Indonesia hari ini. Guru menjelaskan dengan baik dan memberikan contoh yang mudah dipahami.';
            });
            
            // Handle form submission
            nlpForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const text = textInput.value.trim();
                if (!text) {
                    alert('Silakan masukkan teks untuk dianalisis.');
                    return;
                }
                
                // Show results section and loading state
                resultsSection.style.display = 'block';
                loadingResults.style.display = 'block';
                resultsContent.style.display = 'none';
                errorMessage.style.display = 'none';
                
                // Scroll to results
                resultsSection.scrollIntoView({ behavior: 'smooth' });
                
                // Call API
                fetch('api/nlp-analysis.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ text: text })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Hide loading, show results
                    loadingResults.style.display = 'none';
                    resultsContent.style.display = 'block';
                    
                    // Update UI with results
                    updateResults(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    loadingResults.style.display = 'none';
                    errorMessage.textContent = 'Error loading analysis results: ' + error.message;
                    errorMessage.style.display = 'block';
                    
                    // Try fallback API if main fails
                    tryFallbackAPI(text);
                });
            });
            
            // Try fallback API if main API fails
            function tryFallbackAPI(text) {
                fetch('api/nlp-backup-api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ text: text })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Fallback API failed');
                    }
                    return response.json();
                })
                .then(data => {
                    errorMessage.style.display = 'none';
                    resultsContent.style.display = 'block';
                    
                    // Update UI with results from fallback
                    updateResults(data);
                })
                .catch(error => {
                    console.error('Fallback Error:', error);
                    errorMessage.textContent = 'Both main and fallback API failed. Please try again later.';
                });
            }
            
            // Update UI with analysis results
            function updateResults(data) {
                // Update sentiment score
                document.querySelector('.sentiment-score').textContent = data.sentiment.score.toFixed(1);
                const sentimentBadge = document.querySelector('.sentiment-badge');
                sentimentBadge.textContent = data.sentiment.label;
                
                if (data.sentiment.score >= 0.7) {
                    sentimentBadge.className = 'score-badge sentiment-badge mt-2 score-high';
                } else if (data.sentiment.score >= 0.4) {
                    sentimentBadge.className = 'score-badge sentiment-badge mt-2 score-medium';
                } else {
                    sentimentBadge.className = 'score-badge sentiment-badge mt-2 score-low';
                }
                
                // Update complexity score
                document.querySelector('.complexity-score').textContent = data.complexity.score.toFixed(1);
                const complexityBadge = document.querySelector('.complexity-badge');
                complexityBadge.textContent = data.complexity.label;
                
                if (data.complexity.score >= 0.7) {
                    complexityBadge.className = 'score-badge complexity-badge mt-2 score-high';
                } else if (data.complexity.score >= 0.4) {
                    complexityBadge.className = 'score-badge complexity-badge mt-2 score-medium';
                } else {
                    complexityBadge.className = 'score-badge complexity-badge mt-2 score-low';
                }
                
                // Update coherence score
                document.querySelector('.coherence-score').textContent = data.coherence.score.toFixed(1);
                const coherenceBadge = document.querySelector('.coherence-badge');
                coherenceBadge.textContent = data.coherence.label;
                
                if (data.coherence.score >= 0.7) {
                    coherenceBadge.className = 'score-badge coherence-badge mt-2 score-high';
                } else if (data.coherence.score >= 0.4) {
                    coherenceBadge.className = 'score-badge coherence-badge mt-2 score-medium';
                } else {
                    coherenceBadge.className = 'score-badge coherence-badge mt-2 score-low';
                }
                
                // Update keywords
                const keywordsContainer = document.getElementById('keywords-container');
                keywordsContainer.innerHTML = '';
                
                if (data.keywords && data.keywords.length > 0) {
                    data.keywords.forEach(keyword => {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-primary me-2 mb-2';
                        badge.textContent = keyword;
                        keywordsContainer.appendChild(badge);
                    });
                } else {
                    keywordsContainer.innerHTML = '<p class="text-muted">Tidak ada kata kunci yang signifikan ditemukan.</p>';
                }
                
                // Update key sentences
                const keySentences = document.getElementById('key-sentences');
                keySentences.innerHTML = '';
                
                if (data.keySentences && data.keySentences.length > 0) {
                    const ul = document.createElement('ul');
                    ul.className = 'list-group';
                    
                    data.keySentences.forEach(sentence => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item';
                        li.textContent = sentence;
                        ul.appendChild(li);
                    });
                    
                    keySentences.appendChild(ul);
                } else {
                    keySentences.innerHTML = '<p class="text-muted">Tidak ada kalimat penting yang ditemukan.</p>';
                }
                
                // Update text stats
                document.querySelector('.word-count').textContent = data.stats.wordCount;
                document.querySelector('.sentence-count').textContent = data.stats.sentenceCount;
                document.querySelector('.avg-word-length').textContent = data.stats.avgWordLength.toFixed(1);
                document.querySelector('.reading-time').textContent = data.stats.readingTime;
            }
        });
    </script>
</body>
</html>
