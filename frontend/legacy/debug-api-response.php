<?php
/**
 * Debug API Response - untuk mengetahui mengapa API mengembalikan invalid JSON
 */

// Aktifkan error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Capture output
ob_start();

echo "<h2>API Response Debug</h2>";
echo "<hr>";

// Test Main API with test parameter
echo "<h3>1. Testing Main API with ?test=1</h3>";
echo "<p>URL: <code>api/nlp-analysis.php?test=1</code></p>";

$url = 'http://localhost/pointmarket/api/nlp-analysis.php?test=1&v=' . time();
$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]
]);

try {
    $response = file_get_contents($url, false, $context);
    $responseHeaders = $http_response_header;
    
    echo "<h4>Response Headers:</h4>";
    echo "<pre>" . implode("\n", $responseHeaders) . "</pre>";
    
    echo "<h4>Response Body:</h4>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    echo "<h4>JSON Validation:</h4>";
    $json = json_decode($response, true);
    $jsonError = json_last_error();
    
    if ($jsonError === JSON_ERROR_NONE) {
        echo "<p style='color: green'>✓ Valid JSON</p>";
        echo "<pre>" . json_encode($json, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p style='color: red'>✗ Invalid JSON</p>";
        echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
        echo "<p>Error Code: " . $jsonError . "</p>";
        
        // Try to find the issue
        echo "<h4>Response Analysis:</h4>";
        echo "<p>Response Length: " . strlen($response) . " characters</p>";
        echo "<p>First 200 characters:</p>";
        echo "<pre>" . htmlspecialchars(substr($response, 0, 200)) . "</pre>";
        
        if (strlen($response) > 200) {
            echo "<p>Last 200 characters:</p>";
            echo "<pre>" . htmlspecialchars(substr($response, -200)) . "</pre>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Test with direct file execution
echo "<h3>2. Testing with Direct File Execution</h3>";
echo "<p>Executing the API file directly to see raw output</p>";

// Capture direct execution
ob_start();
$_GET['test'] = '1';
$_GET['v'] = time();

try {
    // Simulate the environment
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['REQUEST_URI'] = '/pointmarket/api/nlp-analysis.php?test=1';
    
    include 'api/nlp-analysis.php';
    
    $directOutput = ob_get_contents();
    ob_end_clean();
    
    echo "<h4>Direct Output:</h4>";
    echo "<pre>" . htmlspecialchars($directOutput) . "</pre>";
    
    echo "<h4>JSON Validation (Direct):</h4>";
    $json = json_decode($directOutput, true);
    $jsonError = json_last_error();
    
    if ($jsonError === JSON_ERROR_NONE) {
        echo "<p style='color: green'>✓ Valid JSON</p>";
        echo "<pre>" . json_encode($json, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p style='color: red'>✗ Invalid JSON</p>";
        echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
        echo "<p>Error Code: " . $jsonError . "</p>";
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "<p style='color: red'>Error during direct execution: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";

// Test components individually
echo "<h3>3. Testing Individual Components</h3>";

echo "<h4>Config File Test:</h4>";
try {
    require_once 'includes/config.php';
    echo "<p style='color: green'>✓ Config file loaded successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red'>✗ Config file error: " . $e->getMessage() . "</p>";
}

echo "<h4>NLP Model Test:</h4>";
try {
    require_once 'includes/nlp-model.php';
    echo "<p style='color: green'>✓ NLP Model file loaded successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red'>✗ NLP Model error: " . $e->getMessage() . "</p>";
}

echo "<h4>Database Connection Test:</h4>";
try {
    $database = new Database();
    $pdo = $database->getConnection();
    if ($pdo) {
        echo "<p style='color: green'>✓ Database connection successful</p>";
    } else {
        echo "<p style='color: red'>✗ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red'>✗ Database error: " . $e->getMessage() . "</p>";
}

echo "<h4>NLP Model Instance Test:</h4>";
try {
    if (isset($pdo) && $pdo) {
        $nlpModel = new NLPModel($pdo);
        echo "<p style='color: green'>✓ NLP Model instance created successfully</p>";
    } else {
        echo "<p style='color: red'>✗ Cannot create NLP Model: No database connection</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red'>✗ NLP Model instance error: " . $e->getMessage() . "</p>";
}

echo "<h4>Session Test:</h4>";
try {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    echo "<p style='color: green'>✓ Session started</p>";
    
    if (function_exists('isLoggedIn')) {
        if (isLoggedIn()) {
            echo "<p style='color: green'>✓ User is logged in</p>";
        } else {
            echo "<p style='color: orange'>⚠ User is not logged in</p>";
            echo "<p>This might cause the API to return 401 Unauthorized</p>";
        }
    } else {
        echo "<p style='color: red'>✗ isLoggedIn function not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red'>✗ Session error: " . $e->getMessage() . "</p>";
}

$output = ob_get_contents();
ob_end_clean();

?>
<!DOCTYPE html>
<html>
<head>
    <title>API Debug Results</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; }
        h2 { color: #333; }
        h3 { color: #666; }
        h4 { color: #999; }
        hr { margin: 20px 0; }
    </style>
</head>
<body>
    <?php echo $output; ?>
    
    <hr>
    <h3>4. Recommendations</h3>
    <ul>
        <li>If the API returns HTML instead of JSON, check for PHP errors or warnings</li>
        <li>If the user is not logged in, the API will return 401 Unauthorized</li>
        <li>Check if all required database tables exist</li>
        <li>Verify that the NLP model class is working properly</li>
        <li>Look for any output before the JSON response (whitespace, echo, etc.)</li>
    </ul>
</body>
</html>
