<?php
// Fixed version of nlp-demo.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    require_once 'includes/config.php';
} catch (Exception $e) {
    die("Error loading config: " . $e->getMessage());
}

// Bypass login check temporarily
if (!isLoggedIn()) {
    // Just for testing - create a fake session
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'test_user';
    $_SESSION['name'] = 'Test User';
    $_SESSION['email'] = 'test@example.com';
    $_SESSION['role'] = 'siswa';
}

$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fixed NLP Demo - POINTMARKET</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
            <main class="col-12 p-4">
                <div class="demo-container">
                    <!-- Header -->
                    <div class="d-flex justify-content-between flex-wrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-brain me-2"></i>
                            Fixed NLP Analysis Demo
                        </h1>
                    </div>

                    <!-- Test Alert -->
                    <div class="alert alert-info mb-4">
                        <h5>Debug Information</h5>
                        <p>Session User: <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</p>
                        <p>PHP Version: <?php echo phpversion(); ?></p>
                    </div>

                    <!-- Demo Form -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-edit me-2"></i>Test NLP Analysis</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="mb-3">
                                    <label for="demo-text" class="form-label">Write text to analyze:</label>
                                    <textarea id="demo-text" class="form-control" rows="5" placeholder="Type something here..."></textarea>
                                </div>
                                <button type="button" class="btn btn-primary" id="analyze-btn">
                                    <i class="fas fa-brain me-1"></i>Analyze Text
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Results Area -->
                    <div id="results" class="mt-4"></div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple script to test functionality
        document.getElementById('analyze-btn').addEventListener('click', function() {
            const text = document.getElementById('demo-text').value;
            if (text.trim() === '') {
                alert('Please enter some text to analyze');
                return;
            }
            
            const resultsEl = document.getElementById('results');
            resultsEl.innerHTML = `
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5>Analysis Results</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Text Statistics:</h6>
                                <ul>
                                    <li>Words: ${text.split(/\s+/).filter(Boolean).length}</li>
                                    <li>Characters: ${text.length}</li>
                                    <li>Sentences: ${text.split(/[.!?]+/).filter(Boolean).length}</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Mock Scores:</h6>
                                <div class="progress mb-2">
                                    <div class="progress-bar" style="width: 75%">Grammar: 75%</div>
                                </div>
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-success" style="width: 60%">Keywords: 60%</div>
                                </div>
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-info" style="width: 80%">Structure: 80%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
    </script>
</body>
</html>
