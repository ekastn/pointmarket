<?php
// Minimal NLP Demo Page
// This is a simplified version with only the essential components

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// For testing purposes only - create a test session
// Comment this out in production
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'test_user';
$_SESSION['name'] = 'Test User';
$_SESSION['email'] = 'test@example.com';
$_SESSION['role'] = 'siswa';

// Include config
require_once 'includes/config.php';

// Get current user
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minimal NLP Demo - POINTMARKET</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { padding-top: 20px; }
        .container { max-width: 800px; }
        .result-area { margin-top: 20px; display: none; }
        .loading { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4><i class="fas fa-brain me-2"></i>Minimal NLP Demo</h4>
            </div>
            <div class="card-body">
                <!-- Debug Information -->
                <div class="alert alert-info mb-4">
                    <h5>Debug Information</h5>
                    <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
                    <p><strong>User:</strong> <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</p>
                    <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                </div>
                
                <!-- Simple Input Form -->
                <form id="nlpForm">
                    <div class="mb-3">
                        <label for="text" class="form-label">Enter text to analyze:</label>
                        <textarea id="text" class="form-control" rows="5" placeholder="Enter at least 10 characters to analyze..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="context" class="form-label">Context:</label>
                        <select id="context" class="form-select">
                            <option value="assignment">Assignment</option>
                            <option value="matematik">Mathematics</option>
                            <option value="fisika">Physics</option>
                        </select>
                    </div>
                    <button type="button" id="analyzeBtn" class="btn btn-primary">
                        <i class="fas fa-brain me-1"></i>Analyze Text
                    </button>
                    <button type="button" id="testApiBtn" class="btn btn-info ms-2">
                        <i class="fas fa-stethoscope me-1"></i>Test API
                    </button>
                </form>
                
                <!-- Loading Indicator -->
                <div class="loading mt-4 text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Analyzing text...</p>
                </div>
                
                <!-- Results Area -->
                <div class="result-area mt-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5><i class="fas fa-chart-bar me-2"></i>Analysis Results</h5>
                        </div>
                        <div class="card-body" id="resultContent">
                            <!-- Results will be displayed here -->
                        </div>
                    </div>
                </div>
                
                <!-- Error Area -->
                <div id="errorArea" class="mt-4" style="display:none;">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i>Error</h5>
                        </div>
                        <div class="card-body" id="errorContent">
                            <!-- Error messages will be displayed here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // NLP Demo JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            const analyzeBtn = document.getElementById('analyzeBtn');
            const testApiBtn = document.getElementById('testApiBtn');
            const textArea = document.getElementById('text');
            const contextSelect = document.getElementById('context');
            const resultArea = document.querySelector('.result-area');
            const resultContent = document.getElementById('resultContent');
            const loading = document.querySelector('.loading');
            const errorArea = document.getElementById('errorArea');
            const errorContent = document.getElementById('errorContent');
            
            // Analyze Text
            analyzeBtn.addEventListener('click', async function() {
                const text = textArea.value.trim();
                
                // Validation
                if (text.length < 10) {
                    showError('Please enter at least 10 characters to analyze.');
                    return;
                }
                
                try {
                    // Show loading
                    loading.style.display = 'block';
                    resultArea.style.display = 'none';
                    errorArea.style.display = 'none';
                    
                    // Call API
                    const response = await fetch('api/nlp-analysis.php?v=' + Date.now(), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            text: text,
                            context: contextSelect.value,
                            save_result: false
                        })
                    });
                    
                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        const responseText = await response.text();
                        throw new Error('Response is not JSON. Received: ' + responseText.substring(0, 100) + '...');
                    }
                    
                    const result = await response.json();
                    
                    // Hide loading
                    loading.style.display = 'none';
                    
                    if (result.success) {
                        // Show results
                        resultArea.style.display = 'block';
                        displayResults(result.data);
                    } else {
                        showError(result.error || 'Unknown error occurred');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    loading.style.display = 'none';
                    showError(error.message);
                }
            });
            
            // Test API
            testApiBtn.addEventListener('click', async function() {
                try {
                    // Show loading
                    loading.style.display = 'block';
                    resultArea.style.display = 'none';
                    errorArea.style.display = 'none';
                    
                    const response = await fetch('api/nlp-analysis.php?test=1&v=' + Date.now());
                    
                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        const responseText = await response.text();
                        throw new Error('Response is not JSON. Received: ' + responseText.substring(0, 100) + '...');
                    }
                    
                    const result = await response.json();
                    
                    // Hide loading
                    loading.style.display = 'none';
                    
                    if (result.success) {
                        // Show results
                        resultArea.style.display = 'block';
                        resultContent.innerHTML = `
                            <div class="alert alert-success">
                                <h4><i class="fas fa-check-circle me-2"></i>API Test Successful</h4>
                                <p>The API is working correctly.</p>
                                <pre>${JSON.stringify(result, null, 2)}</pre>
                            </div>
                        `;
                    } else {
                        showError(result.error || 'Unknown error occurred');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    loading.style.display = 'none';
                    showError(error.message);
                }
            });
            
            // Display results
            function displayResults(data) {
                resultContent.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Overall Score</h5>
                            <div class="display-4 text-${getScoreColor(data.total_score)}">${data.total_score}</div>
                            <p class="text-muted">Total Score</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Detailed Scores</h5>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Grammar
                                    <span class="badge bg-${getScoreColor(data.grammar_score)}">${data.grammar_score}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Keywords
                                    <span class="badge bg-${getScoreColor(data.keyword_score)}">${data.keyword_score}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Structure
                                    <span class="badge bg-${getScoreColor(data.structure_score)}">${data.structure_score}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h5>Feedback</h5>
                        <div class="card">
                            <div class="card-body">
                                ${data.feedback || 'No feedback available'}
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h5>Text Statistics</h5>
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="h4">${data.word_count || 0}</div>
                                <small>Words</small>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="h4">${data.sentence_count || 0}</div>
                                <small>Sentences</small>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="h4">${data.readability_score || 0}</div>
                                <small>Readability</small>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // Show error message
            function showError(message) {
                errorArea.style.display = 'block';
                errorContent.innerHTML = `
                    <p>${message}</p>
                    <div class="mt-3">
                        <strong>Troubleshooting:</strong>
                        <ul>
                            <li>Check that the API endpoint is accessible</li>
                            <li>Verify that you are logged in</li>
                            <li>Check server error logs for more details</li>
                        </ul>
                    </div>
                `;
            }
            
            // Get color based on score
            function getScoreColor(score) {
                if (score >= 80) return 'success';
                if (score >= 60) return 'primary';
                if (score >= 40) return 'warning';
                return 'danger';
            }
        });
    </script>
</body>
</html>
