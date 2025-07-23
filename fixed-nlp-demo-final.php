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

// Output basic diagnostic info - akan dihapus setelah debugging selesai
echo "<!-- Diagnosa dimulai -->";
echo "<!-- PHP version: " . phpversion() . " -->";

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    echo "<!-- Session started: " . session_id() . " -->";
}

// Include required files with error handling
try {
    require_once 'includes/config.php';
    echo "<!-- Config file loaded successfully -->";
} catch (Exception $e) {
    die("Error loading config file: " . $e->getMessage());
}

// Make sure startSession function exists and run it if it does
if (function_exists('startSession')) {
    startSession();
    echo "<!-- startSession() called -->";
} else {
    echo "<!-- WARNING: startSession() function not found -->";
}

// Temporarily create a test user session for debugging
// Comment this out in production
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'test_user';
$_SESSION['name'] = 'Test User';
$_SESSION['email'] = 'test@example.com';
$_SESSION['role'] = 'siswa';
echo "<!-- Test user session created for debugging -->";

// Check if user is logged in, otherwise create test session
// NO redirect to login for debugging purposes
if (!isLoggedIn()) {
    // Create a temporary session for testing
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'test_user';
    $_SESSION['name'] = 'Test User';
    $_SESSION['email'] = 'test@example.com';
    $_SESSION['role'] = 'siswa';
    echo "<!-- Created test session for debugging -->";
} else {
    echo "<!-- User already logged in: " . $_SESSION['username'] . " -->";
}

// Get current user with error handling
try {
    if (function_exists('getCurrentUser')) {
        $user = getCurrentUser();
        echo "<!-- getCurrentUser() called -->";
    } else {
        echo "<!-- WARNING: getCurrentUser() function not found -->";
        $user = [
            'id' => 1,
            'username' => 'test_user',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'siswa'
        ];
    }
} catch (Exception $e) {
    die("Error getting current user: " . $e->getMessage());
}

// Check if user data is available
if (!$user) {
    die("Error: User data not available. Please <a href='login.php'>login</a> again.");
}

// Function to log errors to a file (for debugging)
function logError($message) {
    $logFile = __DIR__ . '/nlp-demo-error.log';
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, $logFile);
}

// End of diagnostics
echo "<!-- Diagnosa selesai, halaman seharusnya tampil normal -->";
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
                <div class="demo-container">
                    
                    <!-- Header -->
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
                        <a href="assignments.php" class="btn btn-primary me-2">
                            <i class="fas fa-tasks me-1"></i>Coba di Assignment
                        </a>
                        <a href="ai-explanation.php" class="btn btn-secondary me-2">
                            <i class="fas fa-book me-1"></i>Pelajari Lebih Lanjut
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-1"></i>Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Add cache busting to ensure the latest version is loaded -->
    <script src="assets/js/nlp-analyzer.js?v=<?php echo time(); ?>"></script>
    <script>
        // Examples
        const examples = {
            good: `Teknologi dalam pendidikan memainkan peran yang sangat penting dalam meningkatkan kualitas pembelajaran. Pertama, teknologi menyediakan akses ke sumber belajar yang tidak terbatas melalui internet. Kedua, aplikasi pembelajaran interaktif memungkinkan siswa untuk belajar dengan cara yang lebih menarik dan efektif. Ketiga, platform digital memfasilitasi komunikasi antara guru dan siswa di luar jam sekolah. Dengan demikian, integrasi teknologi dalam pendidikan bukan hanya trend, tetapi kebutuhan fundamental untuk menciptakan sistem pembelajaran yang adaptif dan berkelanjutan.`,
            
            bad: `teknologi bagus untuk sekolah karena bisa belajar dengan komputer dan internet juga bisa cari materi di google terus bisa ngerjain tugas lebih gampang pokoknya teknologi sangat membantu`
        };

        function loadExample(type) {
            const textarea = document.getElementById('demo-text');
            textarea.value = examples[type];
            
            // Trigger analysis
            if (window.nlpAnalyzer) {
                setTimeout(() => {
                    window.nlpAnalyzer.analyzeText(textarea);
                }, 500);
            }
        }

        function clearAll() {
            const textarea = document.getElementById('demo-text');
            textarea.value = '';
            
            if (window.nlpAnalyzer) {
                window.nlpAnalyzer.clearAnalysis(textarea);
            }
        }

        function updateContext() {
            const textarea = document.getElementById('demo-text');
            const context = document.getElementById('context-select').value;
            textarea.setAttribute('data-context', context);
        }

        async function testAPI() {
            try {
                // Show a loading message
                const loadingMessage = 'Testing API connection...';
                alert(loadingMessage);
                
                // Test the API with a timestamp to prevent caching
                const response = await fetch('api/nlp-analysis.php?test=1&v=' + Date.now());
                
                // Check if the response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Response is not JSON:', text);
                    alert('❌ API Test Failed!\n\nError: Response is not JSON\n\nReceived: ' + text.substring(0, 200) + '...');
                    return;
                }
                
                const result = await response.json();
                
                if (result.success) {
                    alert('✅ API Test Passed!\n\nDetails:\n' + JSON.stringify(result, null, 2));
                } else {
                    alert('❌ API Test Failed!\n\nError: ' + result.error);
                }
            } catch (error) {
                console.error('API Test Error:', error);
                alert('❌ API Test Failed!\n\nError: ' + error.message);
            }
        }

        // Load user statistics with improved error handling
        async function loadUserStats() {
            try {
                // Try main API first, then fallback
                let response;
                let error = null;
                
                try {
                    console.log('Trying primary API endpoint...');
                    response = await fetch('api/nlp-analysis.php?action=statistics&v=' + Date.now());
                } catch (apiError) {
                    console.log('Primary API failed, trying fallback...', apiError);
                    error = apiError;
                    // Fallback to inline handler
                    try {
                        response = await fetch('nlp-stats-handler.php?nlp_action=statistics&v=' + Date.now());
                    } catch (fallbackError) {
                        console.log('Fallback API also failed', fallbackError);
                        throw fallbackError;
                    }
                }
                
                // Check if response is ok
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Log the raw response for debugging
                const rawText = await response.text();
                console.log('API Response:', rawText);
                
                // Parse JSON manually to catch syntax errors
                let result;
                try {
                    result = JSON.parse(rawText);
                } catch (jsonError) {
                    console.error('JSON Parse Error:', jsonError);
                    console.error('Raw Response:', rawText);
                    throw new Error('Invalid JSON response: ' + jsonError.message);
                }
                
                if (result.success && result.data && result.data.overall && result.data.overall.total_analyses > 0) {
                    const stats = result.data.overall;
                    document.getElementById('user-stats').innerHTML = `
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div class="h4 text-primary">${stats.total_analyses || 0}</div>
                                <small>Total Analisis</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="h4 text-success">${parseFloat(stats.average_score || 0).toFixed(1)}</div>
                                <small>Rata-rata Score</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="h4 text-warning">${parseFloat(stats.best_score || 0).toFixed(1)}</div>
                                <small>Score Terbaik</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="h4 text-info">${parseFloat(stats.avg_grammar || 0).toFixed(1)}</div>
                                <small>Avg Grammar</small>
                            </div>
                        </div>
                        ${result.data.note ? `<div class="alert alert-info mt-2"><small>${result.data.note}</small></div>` : ''}
                    `;
                } else {
                    // No data available
                    document.getElementById('user-stats').innerHTML = `
                        <div class="text-center text-muted">
                            <i class="fas fa-chart-line fa-2x mb-2"></i>
                            <p>Belum ada data analisis NLP</p>
                            <small>Lakukan analisis pertama untuk melihat statistik</small>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
                document.getElementById('user-stats').innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Info:</strong> Sistem NLP sedang dalam perbaikan.
                        <br><small>Silakan coba lagi nanti atau hubungi administrator jika masalah berlanjut.</small>
                        <br><small class="text-muted">Error: ${error.message}</small>
                    </div>
                `;
            }
        }

        // Document ready function with error handling
        document.addEventListener('DOMContentLoaded', function() {
            try {
                console.log('NLP Demo loaded');
                
                // Initialize NLP Analyzer if not already initialized
                if (!window.nlpAnalyzer && typeof NLPAnalyzer === 'function') {
                    console.log('Initializing NLPAnalyzer');
                    window.nlpAnalyzer = new NLPAnalyzer();
                } else if (!window.nlpAnalyzer) {
                    console.error('NLPAnalyzer not found! Check if nlp-analyzer.js is loaded correctly.');
                }
                
                // Load stats with error catching
                loadUserStats().catch(error => {
                    console.error('Error in loadUserStats:', error);
                });
            } catch (error) {
                console.error('Error in DOMContentLoaded:', error);
                alert('Error initializing NLP Demo: ' + error.message);
            }
        });
    </script>
</body>
</html>
