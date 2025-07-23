<?php
/**
 * NLP API Test Script
 * 
 * This script tests the NLP API endpoint and provides detailed
 * diagnostic information about any issues.
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Set up fake session data for testing
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'test_user';
$_SESSION['name'] = 'Test User';
$_SESSION['email'] = 'test@example.com';
$_SESSION['role'] = 'siswa';

// Function to test API endpoint
function testApiEndpoint($url, $method = 'GET', $data = null) {
    echo "<h3>Testing API Endpoint: $url</h3>";
    
    $options = [
        'http' => [
            'method' => $method,
            'header' => 'Content-Type: application/json',
        ]
    ];
    
    if ($data !== null && $method === 'POST') {
        $options['http']['content'] = json_encode($data);
    }
    
    $context = stream_context_create($options);
    
    try {
        echo "<p>Sending $method request...</p>";
        
        // Set timeout to prevent hanging
        $prev_timeout = ini_get('default_socket_timeout');
        ini_set('default_socket_timeout', 5);
        
        $response = @file_get_contents($url, false, $context);
        ini_set('default_socket_timeout', $prev_timeout);
        
        if ($response === false) {
            echo "<p style='color: red;'>Error: Unable to get response</p>";
            echo "<p>HTTP Error: " . error_get_last()['message'] . "</p>";
            return false;
        }
        
        echo "<p>Raw Response:</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        
        // Try to decode as JSON
        $json = json_decode($response, true);
        if ($json === null) {
            echo "<p style='color: red;'>Error: Invalid JSON response</p>";
            echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
            
            // Attempt to identify HTML or PHP errors in response
            if (strpos($response, '<') !== false) {
                echo "<p>The response appears to contain HTML or PHP error output.</p>";
            }
            
            return false;
        }
        
        echo "<p style='color: green;'>Success: Valid JSON response</p>";
        echo "<p>Decoded JSON:</p>";
        echo "<pre>" . print_r($json, true) . "</pre>";
        
        return $json;
    } catch (Exception $e) {
        echo "<p style='color: red;'>Exception: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Function to check file exists
function checkFileExists($path) {
    echo "<h3>Checking if file exists: $path</h3>";
    
    if (file_exists($path)) {
        echo "<p style='color: green;'>✓ File exists</p>";
        
        // Check if file is readable
        if (is_readable($path)) {
            echo "<p style='color: green;'>✓ File is readable</p>";
        } else {
            echo "<p style='color: red;'>✗ File is not readable</p>";
        }
        
        // Get file size
        $size = filesize($path);
        echo "<p>File size: " . number_format($size) . " bytes</p>";
        
        // Get file modification time
        $modTime = filemtime($path);
        echo "<p>Last modified: " . date('Y-m-d H:i:s', $modTime) . "</p>";
        
        return true;
    } else {
        echo "<p style='color: red;'>✗ File does not exist</p>";
        return false;
    }
}

// Check database connection
function checkDatabase() {
    echo "<h3>Testing Database Connection</h3>";
    
    try {
        $host = "localhost";
        $db_name = "pointmarket";
        $username = "root";
        $password = "";
        
        $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<p style='color: green;'>✓ Database connection successful</p>";
        
        // Test a simple query
        $stmt = $conn->query("SELECT COUNT(*) as user_count FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>User count in database: " . $result['user_count'] . "</p>";
        
        return true;
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Check PHP extensions
function checkPHPExtensions() {
    echo "<h3>Checking Required PHP Extensions</h3>";
    
    $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'session', 'mbstring'];
    $allPresent = true;
    
    foreach ($requiredExtensions as $ext) {
        if (extension_loaded($ext)) {
            echo "<p style='color: green;'>✓ Extension '$ext' is loaded</p>";
        } else {
            echo "<p style='color: red;'>✗ Extension '$ext' is NOT loaded</p>";
            $allPresent = false;
        }
    }
    
    return $allPresent;
}

// Main diagnostic function
function runDiagnostics() {
    echo "<h1>NLP API Diagnostics</h1>";
    
    echo "<h2>Session Information</h2>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
    
    echo "<h2>PHP Information</h2>";
    echo "<p>PHP Version: " . phpversion() . "</p>";
    
    // Check PHP extensions
    checkPHPExtensions();
    
    // Check database connection
    checkDatabase();
    
    // Check if API files exist
    checkFileExists(__DIR__ . '/api/nlp-analysis.php');
    checkFileExists(__DIR__ . '/includes/nlp-model.php');
    checkFileExists(__DIR__ . '/assets/js/nlp-analyzer.js');
    
    // Test API endpoints
    testApiEndpoint('http://localhost/pointmarket/api/nlp-analysis.php?test=1&v=' . time());
    
    // Test the statistics endpoint
    testApiEndpoint('http://localhost/pointmarket/api/nlp-analysis.php?action=statistics&v=' . time());
    
    // Test a basic text analysis with POST request
    testApiEndpoint(
        'http://localhost/pointmarket/api/nlp-analysis.php?v=' . time(),
        'POST',
        ['text' => 'This is a test text for NLP analysis.', 'context' => 'assignment']
    );
}

// Run all diagnostics
runDiagnostics();
?>
