<?php
// Direct API Test Script
// This script directly tests the NLP API components to isolate issues

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Create a debug log function
function debug_log($message, $data = null) {
    echo "<div style='margin: 5px 0; padding: 5px; border: 1px solid #ccc;'>";
    echo "<strong>$message</strong>";
    if ($data !== null) {
        echo "<pre>" . print_r($data, true) . "</pre>";
    }
    echo "</div>";
}

// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

debug_log("Starting API Test");
debug_log("Session ID", session_id());

// Set up a test session
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'test_user';
$_SESSION['name'] = 'Test User';
$_SESSION['email'] = 'test@example.com';
$_SESSION['role'] = 'siswa';

debug_log("Session data", $_SESSION);

// Try to include the required files
try {
    debug_log("Including config.php");
    require_once 'includes/config.php';
    debug_log("Config included successfully");
    
    // Check for key functions
    if (function_exists('requireLogin')) {
        debug_log("requireLogin function exists");
    } else {
        debug_log("ERROR: requireLogin function is missing");
    }
    
    if (function_exists('isLoggedIn')) {
        debug_log("isLoggedIn function exists");
        debug_log("isLoggedIn() result", isLoggedIn());
    } else {
        debug_log("ERROR: isLoggedIn function is missing");
    }
    
    // Try to include NLP model
    debug_log("Including nlp-model.php");
    $nlpModelPath = 'includes/nlp-model.php';
    
    if (file_exists($nlpModelPath)) {
        require_once $nlpModelPath;
        debug_log("NLP model included successfully");
        
        if (class_exists('NLPModel')) {
            debug_log("NLPModel class exists");
        } else {
            debug_log("ERROR: NLPModel class is missing");
        }
    } else {
        debug_log("ERROR: nlp-model.php file not found");
    }
} catch (Exception $e) {
    debug_log("ERROR: Exception while including files", $e->getMessage());
}

// Test database connection
try {
    debug_log("Testing database connection");
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        debug_log("Database connection successful");
        
        // Check if tables exist
        $tables = [];
        $query = $conn->query("SHOW TABLES");
        while ($row = $query->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        debug_log("Database tables", $tables);
    } else {
        debug_log("ERROR: Database connection failed");
    }
} catch (Exception $e) {
    debug_log("ERROR: Database exception", $e->getMessage());
}

// Direct test of NLP analysis
try {
    debug_log("Testing NLP analysis directly");
    
    if (class_exists('NLPModel') && isset($conn)) {
        $nlpModel = new NLPModel($conn);
        $sampleText = "This is a sample text for analysis. It should be processed by the NLP model to test functionality.";
        
        debug_log("Analyzing text", $sampleText);
        $analysis = $nlpModel->analyzeText($sampleText, 'assignment', 1);
        
        if ($analysis) {
            debug_log("Analysis successful", $analysis);
        } else {
            debug_log("ERROR: Analysis failed");
        }
    } else {
        debug_log("ERROR: Cannot test NLP analysis because required components are missing");
    }
} catch (Exception $e) {
    debug_log("ERROR: Exception during NLP analysis", $e->getMessage());
}

// Test the API endpoint directly
try {
    debug_log("Testing API endpoint directly");
    
    $apiUrl = 'http://localhost/pointmarket/api/nlp-analysis.php?test=1&v=' . time();
    debug_log("API URL", $apiUrl);
    
    // Use cURL to make the request
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    // Set cookies from current session
    $cookieHeader = 'PHPSESSID=' . session_id();
    curl_setopt($ch, CURLOPT_COOKIE, $cookieHeader);
    
    debug_log("Sending request with session cookie", $cookieHeader);
    $response = curl_exec($ch);
    
    if ($response === false) {
        debug_log("ERROR: cURL error", curl_error($ch));
    } else {
        // Split header and body
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
        debug_log("Response headers", $header);
        
        // Check if response is JSON
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        debug_log("Content type", $contentType);
        
        if (strpos($contentType, 'application/json') !== false) {
            debug_log("Response is JSON");
            $jsonResponse = json_decode($body, true);
            
            if ($jsonResponse === null) {
                debug_log("ERROR: Invalid JSON", json_last_error_msg());
                debug_log("Raw response body", $body);
            } else {
                debug_log("Decoded JSON response", $jsonResponse);
            }
        } else {
            debug_log("ERROR: Response is not JSON");
            debug_log("Raw response body", $body);
        }
    }
    
    curl_close($ch);
} catch (Exception $e) {
    debug_log("ERROR: Exception testing API endpoint", $e->getMessage());
}

// Create a fix recommendation
echo "<h2>Fix Recommendations</h2>";
echo "<ol>";

if (!file_exists('includes/nlp-model.php')) {
    echo "<li>Create the missing <code>includes/nlp-model.php</code> file.</li>";
} 

echo "<li>Ensure the NLPModel class is properly defined in <code>includes/nlp-model.php</code>.</li>";
echo "<li>Check that the API endpoint <code>api/nlp-analysis.php</code> is returning proper JSON responses.</li>";
echo "<li>Verify that user authentication is working correctly.</li>";
echo "<li>Check database tables needed for NLP functionality.</li>";
echo "</ol>";

// Output a conclusion
debug_log("Test completed");
?>
