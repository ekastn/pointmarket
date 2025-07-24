<?php
// Debug version of nlp-demo.php
// Turn on error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Debug NLP Demo</h1>";
echo "<p>Starting execution...</p>";

try {
    echo "<p>Loading config...</p>";
    require_once 'includes/config.php';
    echo "<p>Config loaded successfully</p>";
    
    echo "<p>Checking login status...</p>";
    
    // Check login without redirecting
    if (!isLoggedIn()) {
        echo "<p style='color:red;'>Not logged in! Would redirect to login.php in the real file.</p>";
        echo "<p>You need to login first at <a href='login.php'>login.php</a></p>";
    } else {
        echo "<p style='color:green;'>User is logged in</p>";
        $user = getCurrentUser();
        echo "<p>Current user: " . htmlspecialchars($user['name']) . " (Role: " . htmlspecialchars($user['role']) . ")</p>";
        
        echo "<h2>Loading includes check:</h2>";
        
        echo "<p>Checking navbar.php...</p>";
        if (file_exists('includes/navbar.php')) {
            echo "<p style='color:green;'>navbar.php exists</p>";
        } else {
            echo "<p style='color:red;'>navbar.php not found!</p>";
        }
        
        echo "<p>Checking sidebar.php...</p>";
        if (file_exists('includes/sidebar.php')) {
            echo "<p style='color:green;'>sidebar.php exists</p>";
        } else {
            echo "<p style='color:red;'>sidebar.php not found!</p>";
        }
        
        echo "<p>Checking nlp-model.php...</p>";
        if (file_exists('includes/nlp-model.php')) {
            echo "<p style='color:green;'>nlp-model.php exists</p>";
        } else {
            echo "<p style='color:red;'>nlp-model.php not found!</p>";
        }
        
        echo "<p>Checking API endpoint...</p>";
        if (file_exists('api/nlp-analysis.php')) {
            echo "<p style='color:green;'>api/nlp-analysis.php exists</p>";
        } else {
            echo "<p style='color:red;'>api/nlp-analysis.php not found!</p>";
        }
        
        echo "<h2>Checking nlp-analyzer.js:</h2>";
        if (file_exists('assets/js/nlp-analyzer.js')) {
            echo "<p style='color:green;'>nlp-analyzer.js exists</p>";
        } else {
            echo "<p style='color:red;'>nlp-analyzer.js not found!</p>";
        }
        
        echo "<h2>HTML rendering test:</h2>";
        echo "<div class='container'>";
        echo "<div class='alert alert-info'>This is a simple HTML rendering test</div>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color:red;'>Error:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>File: " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}

echo "<p>End of debug output</p>";
?>
