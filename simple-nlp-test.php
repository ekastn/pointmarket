<?php
// Minimal NLP Test Script
// This script tests just the essential NLP functionality without UI complexity

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Create a test session for debugging purposes
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'test_user';
$_SESSION['name'] = 'Test User';
$_SESSION['email'] = 'test@example.com';
$_SESSION['role'] = 'siswa';

// Helper function to display error information
function displayError($title, $message, $details = null) {
    echo "<div style='margin: 20px; padding: 10px; border: 1px solid #dc3545; background-color: #f8d7da; color: #721c24; border-radius: 5px;'>";
    echo "<h3>$title</h3>";
    echo "<p>$message</p>";
    if ($details) {
        echo "<pre>$details</pre>";
    }
    echo "</div>";
}

// Helper function to display success information
function displaySuccess($title, $message, $details = null) {
    echo "<div style='margin: 20px; padding: 10px; border: 1px solid #28a745; background-color: #d4edda; color: #155724; border-radius: 5px;'>";
    echo "<h3>$title</h3>";
    echo "<p>$message</p>";
    if ($details) {
        echo "<pre>$details</pre>";
    }
    echo "</div>";
}

// Helper function to test include file
function testInclude($file) {
    echo "<h4>Testing include: $file</h4>";
    if (file_exists($file)) {
        echo "<p>✅ File exists</p>";
        try {
            include_once $file;
            echo "<p>✅ File included successfully</p>";
            return true;
        } catch (Exception $e) {
            displayError("Include Error", "Error including file: $file", $e->getMessage());
            return false;
        }
    } else {
        displayError("File Not Found", "The file does not exist: $file");
        return false;
    }
}

// Helper function to check if a function exists
function checkFunction($function) {
    echo "<h4>Checking function: $function</h4>";
    if (function_exists($function)) {
        echo "<p>✅ Function exists</p>";
        return true;
    } else {
        displayError("Function Not Found", "The function does not exist: $function");
        return false;
    }
}

// Helper function to check class
function checkClass($class) {
    echo "<h4>Checking class: $class</h4>";
    if (class_exists($class)) {
        echo "<p>✅ Class exists</p>";
        return true;
    } else {
        displayError("Class Not Found", "The class does not exist: $class");
        return false;
    }
}

// Test making an API request
function testApiRequest($url, $method = 'GET', $data = null) {
    echo "<h3>Testing API request to: $url</h3>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    echo "<p>HTTP Status Code: $httpCode</p>";
    echo "<p>Content Type: $contentType</p>";
    
    if ($error) {
        displayError("cURL Error", "Error making request", $error);
        return false;
    }
    
    echo "<h4>Raw Response:</h4>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    // Try to decode JSON
    $decoded = json_decode($response, true);
    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        displayError("JSON Error", "Failed to decode JSON response", json_last_error_msg());
        
        // Check for HTML content
        if (strpos($response, '<!DOCTYPE html>') !== false || strpos($response, '<html') !== false) {
            displayError("HTML Response", "The API returned HTML instead of JSON", "This usually indicates a PHP error or authorization issue");
        }
        
        return false;
    }
    
    displaySuccess("JSON Response", "Successfully decoded JSON response", print_r($decoded, true));
    return $decoded;
}

// Main test script
echo "<!DOCTYPE html>
<html>
<head>
    <title>NLP API Test</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        h1, h2 { color: #333; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
        .section { margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
    </style>
</head>
<body>
    <h1>NLP System Test</h1>
    <p>Testing core NLP functionality with minimal dependencies</p>";

echo "<div class='section'>";
echo "<h2>1. Testing Session</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>2. Testing Core Includes</h2>";
$configLoaded = testInclude('includes/config.php');
if ($configLoaded) {
    checkFunction('requireLogin');
    checkFunction('isLoggedIn');
    checkFunction('getCurrentUser');
}
testInclude('includes/nlp-model.php');
if (class_exists('NLPModel')) {
    echo "<p>✅ NLPModel class exists</p>";
} else {
    displayError("Missing Class", "NLPModel class is not defined");
}
echo "</div>";

echo "<div class='section'>";
echo "<h2>3. Testing API Endpoints</h2>";
// Test API with GET request
testApiRequest('http://localhost/pointmarket/api/nlp-analysis.php?test=1&v=' . time());

// Test API with POST request (text analysis)
$testData = [
    'text' => 'This is a test text for NLP analysis.',
    'context' => 'assignment',
    'save_result' => false
];
testApiRequest('http://localhost/pointmarket/api/nlp-analysis.php', 'POST', $testData);
echo "</div>";

echo "<div class='section'>";
echo "<h2>4. Manual NLP Analysis Test</h2>";

if (class_exists('NLPModel') && class_exists('Database')) {
    try {
        echo "<h3>Creating Database Connection</h3>";
        $database = new Database();
        $pdo = $database->getConnection();
        
        if ($pdo) {
            echo "<p>✅ Database connection successful</p>";
            
            echo "<h3>Creating NLP Model</h3>";
            $nlpModel = new NLPModel($pdo);
            
            echo "<h3>Performing Text Analysis</h3>";
            $sampleText = "Teknologi dalam pendidikan memainkan peran yang sangat penting dalam meningkatkan kualitas pembelajaran.";
            $analysis = $nlpModel->analyzeText($sampleText, 'assignment', 1);
            
            if ($analysis) {
                displaySuccess("Analysis Success", "Successfully analyzed text", print_r($analysis, true));
            } else {
                displayError("Analysis Failed", "Failed to analyze text");
            }
        } else {
            displayError("Database Error", "Failed to connect to database");
        }
    } catch (Exception $e) {
        displayError("Exception", "An error occurred during manual testing", $e->getMessage());
    }
} else {
    displayError("Missing Classes", "Required classes are not available");
}
echo "</div>";

echo "<div class='section'>";
echo "<h2>5. Checking File Permissions</h2>";
$files = [
    'api/nlp-analysis.php',
    'includes/nlp-model.php',
    'includes/config.php',
    'assets/js/nlp-analyzer.js'
];

foreach ($files as $file) {
    echo "<h4>$file</h4>";
    $fullPath = __DIR__ . '/' . $file;
    
    if (file_exists($fullPath)) {
        echo "<p>✅ File exists</p>";
        echo "<p>Permissions: " . substr(sprintf('%o', fileperms($fullPath)), -4) . "</p>";
        echo "<p>Size: " . filesize($fullPath) . " bytes</p>";
        echo "<p>Last modified: " . date('Y-m-d H:i:s', filemtime($fullPath)) . "</p>";
    } else {
        displayError("File Not Found", "The file does not exist: $file");
    }
}
echo "</div>";

echo "</body></html>";
?>
