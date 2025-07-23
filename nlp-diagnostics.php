<?php
/**
 * NLP Demo Diagnostics Tool
 * 
 * File ini untuk mendiagnosis masalah pada halaman NLP demo
 * dan menampilkan informasi penting terkait dengan masalah tersebut.
 */

// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Output diagnostik HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NLP Demo Diagnostics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            padding: 20px; 
            background-color: #f8f9fa;
        }
        .card { 
            margin-bottom: 20px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            font-weight: bold;
        }
        .success { 
            color: #28a745; 
            font-weight: 500;
        }
        .failure { 
            color: #dc3545; 
            font-weight: 500;
        }
        .warning { 
            color: #ffc107; 
            font-weight: 500;
        }
        pre {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            max-height: 300px;
            overflow-y: auto;
            font-size: 0.9em;
        }
        details {
            margin-top: 10px;
        }
        details summary {
            cursor: pointer;
            padding: 5px;
            background-color: #e9ecef;
            border-radius: 4px;
            font-weight: 500;
        }
        details summary:hover {
            background-color: #dee2e6;
        }
        .alert {
            border-left: 4px solid;
        }
        .alert-info {
            border-left-color: #17a2b8;
        }
        .alert-warning {
            border-left-color: #ffc107;
        }
        .alert-success {
            border-left-color: #28a745;
        }
        #api-test-results {
            min-height: 100px;
            padding: 10px;
            background-color: #ffffff;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        h1 {
            color: #007bff;
            margin-bottom: 20px;
        }
        .lead {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>NLP Demo Diagnostics Tool</h1>
        <p class="lead">Alat ini memeriksa komponen-komponen penting yang diperlukan untuk halaman NLP demo.</p>
        
        <div class="card">
            <div class="card-header">1. PHP Information</div>
            <div class="card-body">
                <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                <p><strong>Memory Limit:</strong> <?php echo ini_get('memory_limit'); ?></p>
                <p><strong>Max Execution Time:</strong> <?php echo ini_get('max_execution_time'); ?> seconds</p>
                <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
                <p><strong>Current File:</strong> <?php echo __FILE__; ?></p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">2. Session Test</div>
            <div class="card-body">
                <?php
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                    echo "<p class='success'>Session started: " . session_id() . "</p>";
                } else {
                    echo "<p class='success'>Session already active: " . session_id() . "</p>";
                }
                
                echo "<p><strong>Session Data:</strong></p>";
                echo "<pre>" . print_r($_SESSION, true) . "</pre>";
                
                // Test setting session data
                $_SESSION['test_value'] = 'This is a test value at ' . date('Y-m-d H:i:s');
                echo "<p class='success'>Session test value set.</p>";
                ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">3. Required Files Check</div>
            <div class="card-body">
                <?php
                $requiredFiles = [
                    'includes/config.php',
                    'includes/nlp-model.php',
                    'includes/basic-nlp-model.php',
                    'includes/navbar.php',
                    'includes/sidebar.php',
                    'api/nlp-analysis.php',
                    'api/nlp-backup-api.php',
                    'assets/js/nlp-analyzer.js'
                ];
                
                foreach ($requiredFiles as $file) {
                    if (file_exists($file)) {
                        echo "<p class='success'>✓ File found: $file</p>";
                    } else {
                        echo "<p class='failure'>✗ File missing: $file</p>";
                    }
                }
                ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">4. Configuration File Test</div>
            <div class="card-body">
                <?php
                try {
                    require_once 'includes/config.php';
                    echo "<p class='success'>✓ Configuration file loaded successfully</p>";
                    
                    // Check for required functions
                    $requiredFunctions = ['startSession', 'isLoggedIn', 'requireLogin', 'getCurrentUser'];
                    foreach ($requiredFunctions as $function) {
                        if (function_exists($function)) {
                            echo "<p class='success'>✓ Function exists: $function</p>";
                        } else {
                            echo "<p class='failure'>✗ Function missing: $function</p>";
                        }
                    }
                    
                    // Test Database connection
                    if (class_exists('Database')) {
                        echo "<p class='success'>✓ Database class exists</p>";
                        try {
                            $database = new Database();
                            $pdo = $database->getConnection();
                            if ($pdo instanceof PDO) {
                                echo "<p class='success'>✓ Database connection successful</p>";
                            } else {
                                echo "<p class='failure'>✗ Database connection failed: Not a PDO instance</p>";
                            }
                        } catch (Exception $e) {
                            echo "<p class='failure'>✗ Database connection failed: " . $e->getMessage() . "</p>";
                        }
                    } else {
                        echo "<p class='failure'>✗ Database class missing</p>";
                    }
                    
                } catch (Exception $e) {
                    echo "<p class='failure'>✗ Error loading configuration file: " . $e->getMessage() . "</p>";
                }
                ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">5. NLP Model Test</div>
            <div class="card-body">
                <?php
                try {
                    // Check if NLP model exists
                    if (file_exists('includes/nlp-model.php')) {
                        require_once 'includes/nlp-model.php';
                        echo "<p class='success'>✓ NLP model file loaded successfully</p>";
                        
                        if (class_exists('NLPModel')) {
                            echo "<p class='success'>✓ NLPModel class exists</p>";
                            
                            // Try to create an instance
                            try {
                                if (isset($pdo) && $pdo instanceof PDO) {
                                    $nlpModel = new NLPModel($pdo);
                                    echo "<p class='success'>✓ NLPModel instance created successfully</p>";
                                    
                                    // Test analyze method
                                    if (method_exists($nlpModel, 'analyzeText')) {
                                        echo "<p class='success'>✓ analyzeText method exists</p>";
                                    } else {
                                        echo "<p class='failure'>✗ analyzeText method missing</p>";
                                    }
                                } else {
                                    echo "<p class='warning'>⚠ Cannot create NLPModel instance: No valid PDO connection</p>";
                                }
                            } catch (Exception $e) {
                                echo "<p class='failure'>✗ Error creating NLPModel instance: " . $e->getMessage() . "</p>";
                            }
                        } else {
                            echo "<p class='failure'>✗ NLPModel class missing</p>";
                        }
                    } else {
                        echo "<p class='warning'>⚠ NLP model file missing, checking for basic model...</p>";
                        
                        // Check for basic model as fallback
                        if (file_exists('includes/basic-nlp-model.php')) {
                            require_once 'includes/basic-nlp-model.php';
                            echo "<p class='success'>✓ Basic NLP model file loaded successfully</p>";
                            
                            if (class_exists('NLPModel')) {
                                echo "<p class='success'>✓ NLPModel class exists in basic model</p>";
                            } else {
                                echo "<p class='failure'>✗ NLPModel class missing in basic model</p>";
                            }
                        } else {
                            echo "<p class='failure'>✗ Both NLP model and basic model files missing</p>";
                        }
                    }
                } catch (Exception $e) {
                    echo "<p class='failure'>✗ Error testing NLP model: " . $e->getMessage() . "</p>";
                }
                ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">6. User Authentication Test</div>
            <div class="card-body">
                <?php
                // Check if user is logged in
                if (function_exists('isLoggedIn') && function_exists('getCurrentUser')) {
                    if (isLoggedIn()) {
                        $user = getCurrentUser();
                        echo "<p class='success'>✓ User is logged in</p>";
                        echo "<pre>" . print_r($user, true) . "</pre>";
                    } else {
                        echo "<p class='warning'>⚠ User is not logged in</p>";
                        
                        // Create test session
                        echo "<p>Creating test session for debugging...</p>";
                        $_SESSION['user_id'] = 1;
                        $_SESSION['username'] = 'test_user';
                        $_SESSION['name'] = 'Test User';
                        $_SESSION['email'] = 'test@example.com';
                        $_SESSION['role'] = 'siswa';
                        
                        if (isLoggedIn()) {
                            echo "<p class='success'>✓ Test session created successfully</p>";
                        } else {
                            echo "<p class='failure'>✗ Failed to create test session</p>";
                        }
                    }
                } else {
                    echo "<p class='failure'>✗ Authentication functions missing</p>";
                }
                ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">7. API Test</div>
            <div class="card-body">
                <p>Testing API endpoints:</p>
                <div class="alert alert-info">
                    <strong>Note:</strong> API tests use Response.clone() to handle body stream reading safely. 
                    This prevents the "body stream already read" error that occurs when trying to read the same response multiple times.
                </div>
                <div id="api-test-results">Loading API test results...</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">8. Troubleshooting Guide</div>
            <div class="card-body">
                <h5>Common Issues and Solutions:</h5>
                
                <div class="alert alert-warning">
                    <strong>⚠️ "Failed to execute 'text' on 'Response': body stream already read"</strong>
                    <p><strong>Cause:</strong> JavaScript Response body stream can only be read once. When you call <code>response.json()</code> or <code>response.text()</code>, the stream is consumed and cannot be read again.</p>
                    <p><strong>Solution:</strong> Use <code>response.clone()</code> to create a copy of the response before reading it, or restructure your code to avoid multiple reads.</p>
                    <pre>// ❌ Wrong - causes error
const response = await fetch(url);
const json = await response.json(); // First read
const text = await response.text(); // Error: stream already read

// ✅ Correct - use clone
const response = await fetch(url);
const responseClone = response.clone();
const json = await response.json(); // First read
const text = await responseClone.text(); // Works fine</pre>
                </div>
                
                <div class="alert alert-info">
                    <strong>ℹ️ API Not Responding</strong>
                    <p><strong>Possible causes:</strong></p>
                    <ul>
                        <li>API file doesn't exist or has syntax errors</li>
                        <li>PHP errors preventing proper execution</li>
                        <li>Database connection issues</li>
                        <li>Missing dependencies or configuration</li>
                    </ul>
                    <p><strong>Solution:</strong> Check the PHP error logs and ensure all required files are present.</p>
                </div>
                
                <div class="alert alert-success">
                    <strong>✅ Best Practices</strong>
                    <ul>
                        <li>Always check response headers before parsing</li>
                        <li>Use try-catch blocks for API calls</li>
                        <li>Clone responses when you need to read them multiple times</li>
                        <li>Add proper error handling for network failures</li>
                        <li>Use cache busting parameters for development (e.g., <code>?v=timestamp</code>)</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">9. Actions</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <a href="fixed-nlp-demo-optimized.php" class="btn btn-primary mb-2 w-100">Open Optimized Demo</a>
                    </div>
                    <div class="col-md-4">
                        <a href="fixed-nlp-demo-final.php" class="btn btn-secondary mb-2 w-100">Open Fixed Demo</a>
                    </div>
                    <div class="col-md-4">
                        <a href="nlp-demo.php" class="btn btn-outline-primary mb-2 w-100">Open Original Demo</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Test the API
        async function testAPI() {
            const resultDiv = document.getElementById('api-test-results');
            resultDiv.innerHTML = '<p>🔄 Initializing API tests...</p>';
            
            // Test main API
            resultDiv.innerHTML += '<p>🔍 Testing main API (api/nlp-analysis.php)...</p>';
            try {
                const startTime = Date.now();
                const response = await fetch('api/nlp-analysis.php?test=1&v=' + Date.now());
                const endTime = Date.now();
                const responseTime = endTime - startTime;
                
                const contentType = response.headers.get('content-type');
                const status = response.status;
                const statusText = response.statusText;
                
                resultDiv.innerHTML += `
                    <p><strong>Response Info:</strong> Status ${status} ${statusText}, Time: ${responseTime}ms</p>
                    <p><strong>Content-Type:</strong> ${contentType || 'Not specified'}</p>
                `;
                
                // Clone response to read it multiple times if needed
                const responseClone = response.clone();
                
                if (status === 200 && contentType && contentType.includes('application/json')) {
                    try {
                        const result = await response.json();
                        resultDiv.innerHTML += `
                            <p class="success">✓ Main API responded with valid JSON</p>
                            <details>
                                <summary>View Response Data</summary>
                                <pre>${JSON.stringify(result, null, 2)}</pre>
                            </details>
                        `;
                    } catch (e) {
                        // Use the cloned response to read as text
                        const text = await responseClone.text();
                        resultDiv.innerHTML += `
                            <p class="failure">✗ Main API returned invalid JSON</p>
                            <p><strong>JSON Parse Error:</strong> ${e.message}</p>
                            <details>
                                <summary>View Raw Response</summary>
                                <pre>${text}</pre>
                            </details>
                        `;
                    }
                } else if (status === 200) {
                    const text = await response.text();
                    resultDiv.innerHTML += `
                        <p class="warning">⚠ Main API did not return JSON (got ${contentType})</p>
                        <details>
                            <summary>View Response Content</summary>
                            <pre>${text}</pre>
                        </details>
                    `;
                } else {
                    const text = await response.text();
                    resultDiv.innerHTML += `
                        <p class="failure">✗ Main API returned HTTP ${status} ${statusText}</p>
                        <details>
                            <summary>View Error Response</summary>
                            <pre>${text}</pre>
                        </details>
                    `;
                }
            } catch (e) {
                resultDiv.innerHTML += `
                    <p class="failure">✗ Network error testing main API: ${e.message}</p>
                `;
            }
            
            // Test backup API
            resultDiv.innerHTML += '<p>🔍 Testing backup API (api/nlp-backup-api.php)...</p>';
            try {
                const startTime = Date.now();
                const response = await fetch('api/nlp-backup-api.php?test=1&v=' + Date.now());
                const endTime = Date.now();
                const responseTime = endTime - startTime;
                
                const contentType = response.headers.get('content-type');
                const status = response.status;
                const statusText = response.statusText;
                
                resultDiv.innerHTML += `
                    <p><strong>Response Info:</strong> Status ${status} ${statusText}, Time: ${responseTime}ms</p>
                    <p><strong>Content-Type:</strong> ${contentType || 'Not specified'}</p>
                `;
                
                // Clone response to read it multiple times if needed
                const responseClone = response.clone();
                
                if (status === 200 && contentType && contentType.includes('application/json')) {
                    try {
                        const result = await response.json();
                        resultDiv.innerHTML += `
                            <p class="success">✓ Backup API responded with valid JSON</p>
                            <details>
                                <summary>View Response Data</summary>
                                <pre>${JSON.stringify(result, null, 2)}</pre>
                            </details>
                        `;
                    } catch (e) {
                        // Use the cloned response to read as text
                        const text = await responseClone.text();
                        resultDiv.innerHTML += `
                            <p class="failure">✗ Backup API returned invalid JSON</p>
                            <p><strong>JSON Parse Error:</strong> ${e.message}</p>
                            <details>
                                <summary>View Raw Response</summary>
                                <pre>${text}</pre>
                            </details>
                        `;
                    }
                } else if (status === 200) {
                    const text = await response.text();
                    resultDiv.innerHTML += `
                        <p class="warning">⚠ Backup API did not return JSON (got ${contentType})</p>
                        <details>
                            <summary>View Response Content</summary>
                            <pre>${text}</pre>
                        </details>
                    `;
                } else {
                    const text = await response.text();
                    resultDiv.innerHTML += `
                        <p class="failure">✗ Backup API returned HTTP ${status} ${statusText}</p>
                        <details>
                            <summary>View Error Response</summary>
                            <pre>${text}</pre>
                        </details>
                    `;
                }
            } catch (e) {
                resultDiv.innerHTML += `
                    <p class="failure">✗ Network error testing backup API: ${e.message}</p>
                `;
            }
            
            resultDiv.innerHTML += '<p>✅ API testing completed!</p>';
        }
        
        // Run API tests when page loads
        document.addEventListener('DOMContentLoaded', testAPI);
    </script>
</body>
</html>
