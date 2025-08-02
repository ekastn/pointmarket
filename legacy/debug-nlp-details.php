<?php
// Debug version of nlp-demo.php with step by step outputs
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html>
<head>
    <title>NLP Demo Debug</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body>
<div class='container mt-4'>";

echo "<h1>NLP Demo Debugging</h1>";
echo "<hr>";

echo "<h3>Step 1: Include Config</h3>";
try {
    require_once 'includes/config.php';
    echo "<div class='alert alert-success'>✓ Config file loaded successfully</div>";
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>✗ Error loading config: " . $e->getMessage() . "</div>";
    die("</div></body></html>");
}

echo "<h3>Step 2: Check Login Status</h3>";
if (!isLoggedIn()) {
    echo "<div class='alert alert-warning'>✗ User is not logged in - would normally redirect to login page</div>";
    // For debugging, let's continue without redirection
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'debug_user';
    $_SESSION['name'] = 'Debug User';
    $_SESSION['role'] = 'siswa';
    echo "<div class='alert alert-info'>Created debug session for testing</div>";
} else {
    echo "<div class='alert alert-success'>✓ User is logged in</div>";
}

$user = getCurrentUser();
echo "<pre>User data: " . print_r($user, true) . "</pre>";

echo "<h3>Step 3: Check Navigation Files</h3>";
echo "<p>Testing navbar.php...</p>";
try {
    ob_start();
    include 'includes/navbar.php';
    $navbar = ob_get_clean();
    echo "<div class='alert alert-success'>✓ Navbar included successfully (" . strlen($navbar) . " chars)</div>";
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>✗ Error including navbar: " . $e->getMessage() . "</div>";
}

echo "<p>Testing sidebar.php...</p>";
try {
    ob_start();
    include 'includes/sidebar.php';
    $sidebar = ob_get_clean();
    echo "<div class='alert alert-success'>✓ Sidebar included successfully (" . strlen($sidebar) . " chars)</div>";
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>✗ Error including sidebar: " . $e->getMessage() . "</div>";
}

echo "<h3>Step 4: Check JavaScript Files</h3>";
echo "<p>Checking nlp-analyzer.js...</p>";
if (file_exists('assets/js/nlp-analyzer.js')) {
    echo "<div class='alert alert-success'>✓ nlp-analyzer.js file exists</div>";
    
    // Check file size and modification time
    $filesize = filesize('assets/js/nlp-analyzer.js');
    $modified = date("Y-m-d H:i:s", filemtime('assets/js/nlp-analyzer.js'));
    echo "<p>File size: $filesize bytes, Last modified: $modified</p>";
} else {
    echo "<div class='alert alert-danger'>✗ nlp-analyzer.js file is missing!</div>";
}

echo "<h3>Step 5: Check API Endpoint</h3>";
echo "<p>Checking api/nlp-analysis.php...</p>";
if (file_exists('api/nlp-analysis.php')) {
    echo "<div class='alert alert-success'>✓ nlp-analysis.php file exists</div>";
    
    // Check file size and modification time
    $filesize = filesize('api/nlp-analysis.php');
    $modified = date("Y-m-d H:i:s", filemtime('api/nlp-analysis.php'));
    echo "<p>File size: $filesize bytes, Last modified: $modified</p>";
    
    // Test API response (without making an actual HTTP request)
    echo "<p>Testing API response format...</p>";
    try {
        // Simulate calling the API directly
        ob_start();
        include 'api/nlp-analysis.php';
        $result = ob_get_clean();
        
        echo "<div class='alert alert-info'>API Response:</div>";
        echo "<pre>" . htmlspecialchars($result) . "</pre>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>✗ Error accessing API: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='alert alert-danger'>✗ nlp-analysis.php file is missing!</div>";
}

echo "<h3>Step 6: Memory and Resource Usage</h3>";
echo "<p>Memory usage: " . memory_get_usage() / 1024 / 1024 . " MB</p>";
echo "<p>Peak memory usage: " . memory_get_peak_usage() / 1024 / 1024 . " MB</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";

echo "<h3>Conclusion</h3>";
echo "<p>Check the results above to identify why nlp-demo.php isn't displaying properly.</p>";
echo "<p><a href='nlp-demo.php' class='btn btn-primary'>Try Original NLP Demo Page</a></p>";

echo "</div></body></html>";
?>
