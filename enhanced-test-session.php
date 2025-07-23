<?php
/**
 * Enhanced Test Session untuk mengatasi authentication error
 */

// Start session
session_start();

// Create test session data
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'test_user';
$_SESSION['name'] = 'Test User';
$_SESSION['email'] = 'test@example.com';
$_SESSION['role'] = 'siswa';
$_SESSION['class'] = 'XII IPA 1';
$_SESSION['school'] = 'SMA Test';
$_SESSION['logged_in'] = true;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Enhanced Test Session</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 20px 0; }
        .success { color: green; }
        .info { color: blue; }
        .warning { color: orange; }
        .error { color: red; }
        .test-btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; text-decoration: none; display: inline-block; }
        .test-btn:hover { background: #0056b3; }
        .test-btn.success { background: #28a745; }
        .test-btn.warning { background: #ffc107; color: black; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        #result { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Enhanced Test Session</h1>
        
        <div class="card success">
            <h2>✅ Session Status</h2>
            <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
            <p><strong>Status:</strong> Session started successfully with test user data.</p>
            <p><strong>User ID:</strong> <?php echo $_SESSION['user_id']; ?></p>
            <p><strong>Username:</strong> <?php echo $_SESSION['username']; ?></p>
            <p><strong>Role:</strong> <?php echo $_SESSION['role']; ?></p>
        </div>
        
        <div class="card info">
            <h2>📋 Complete Session Data</h2>
            <pre><?php print_r($_SESSION); ?></pre>
        </div>
        
        <div class="card info">
            <h2>🔗 Test Links</h2>
            <p>Sekarang Anda dapat menguji API dengan session yang valid:</p>
            <a href="api/nlp-analysis.php?test=1" class="test-btn" target="_blank">Test Main API</a>
            <a href="nlp-diagnostics.php" class="test-btn" target="_blank">Run Diagnostics</a>
            <a href="debug-api-response.php" class="test-btn warning" target="_blank">Debug API Response</a>
            <a href="simple-api-test.php" class="test-btn success" target="_blank">Simple API Test</a>
        </div>
        
        <div class="card">
            <h2>🧪 Live API Test</h2>
            <p>Test API langsung dari sini:</p>
            <button onclick="testMainAPI()" class="test-btn">Test Main API</button>
            <button onclick="testWithPOST()" class="test-btn success">Test with POST</button>
            <button onclick="clearResults()" class="test-btn warning">Clear Results</button>
            <div id="result"></div>
        </div>
        
        <div class="card">
            <h2>🔍 Authentication Test</h2>
            <p>Test fungsi authentication:</p>
            <button onclick="testAuth()" class="test-btn">Test isLoggedIn()</button>
            <div id="auth-result"></div>
        </div>
    </div>
    
    <script>
        async function testMainAPI() {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<p>🔄 Testing Main API...</p>';
            
            try {
                const response = await fetch('api/nlp-analysis.php?test=1&v=' + Date.now());
                const text = await response.text();
                
                let statusColor = 'green';
                if (response.status !== 200) statusColor = 'red';
                
                resultDiv.innerHTML = `
                    <h3>Main API Response:</h3>
                    <p><strong>Status:</strong> <span style="color: ${statusColor}">${response.status} ${response.statusText}</span></p>
                    <p><strong>Content-Type:</strong> ${response.headers.get('content-type')}</p>
                    <p><strong>Response Length:</strong> ${text.length} characters</p>
                    <p><strong>Response:</strong></p>
                    <pre>${text}</pre>
                `;
                
                // Try to parse JSON
                try {
                    const json = JSON.parse(text);
                    resultDiv.innerHTML += '<p style="color: green;">✅ Valid JSON response</p>';
                    resultDiv.innerHTML += '<pre>' + JSON.stringify(json, null, 2) + '</pre>';
                } catch (e) {
                    resultDiv.innerHTML += '<p style="color: red;">❌ Invalid JSON: ' + e.message + '</p>';
                    resultDiv.innerHTML += '<p>This might be the cause of the "Main API returned invalid JSON" error.</p>';
                }
                
            } catch (error) {
                resultDiv.innerHTML = '<p style="color: red;">❌ Network Error: ' + error.message + '</p>';
            }
        }
        
        async function testWithPOST() {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<p>🔄 Testing API with POST...</p>';
            
            try {
                const response = await fetch('api/nlp-analysis.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        text: 'This is a test text for NLP analysis.',
                        context: 'assignment'
                    })
                });
                
                const text = await response.text();
                
                let statusColor = 'green';
                if (response.status !== 200) statusColor = 'red';
                
                resultDiv.innerHTML = `
                    <h3>POST API Response:</h3>
                    <p><strong>Status:</strong> <span style="color: ${statusColor}">${response.status} ${response.statusText}</span></p>
                    <p><strong>Content-Type:</strong> ${response.headers.get('content-type')}</p>
                    <p><strong>Response:</strong></p>
                    <pre>${text}</pre>
                `;
                
                // Try to parse JSON
                try {
                    const json = JSON.parse(text);
                    resultDiv.innerHTML += '<p style="color: green;">✅ Valid JSON response</p>';
                } catch (e) {
                    resultDiv.innerHTML += '<p style="color: red;">❌ Invalid JSON: ' + e.message + '</p>';
                }
                
            } catch (error) {
                resultDiv.innerHTML = '<p style="color: red;">❌ Error: ' + error.message + '</p>';
            }
        }
        
        async function testAuth() {
            const resultDiv = document.getElementById('auth-result');
            resultDiv.innerHTML = '<p>🔄 Testing authentication...</p>';
            
            try {
                const response = await fetch('get-session-info.php');
                const text = await response.text();
                
                resultDiv.innerHTML = `
                    <h3>Authentication Test:</h3>
                    <p><strong>Status:</strong> ${response.status} ${response.statusText}</p>
                    <p><strong>Response:</strong></p>
                    <pre>${text}</pre>
                `;
                
            } catch (error) {
                resultDiv.innerHTML = '<p style="color: red;">❌ Error: ' + error.message + '</p>';
            }
        }
        
        function clearResults() {
            document.getElementById('result').innerHTML = '';
            document.getElementById('auth-result').innerHTML = '';
        }
    </script>
    
    <div class="card warning">
        <h2>⚠️ Important Notes</h2>
        <ul>
            <li>File ini membuat session test yang valid untuk mengatasi error authentication pada API</li>
            <li>Setelah menggunakan session ini, API seharusnya tidak lagi mengembalikan error 401 Unauthorized</li>
            <li>Jika masih ada error "invalid JSON", kemungkinan masalah terletak pada:</li>
            <ul>
                <li>Database connection</li>
                <li>NLP model initialization</li>
                <li>PHP errors/warnings yang tercampur dengan JSON output</li>
                <li>Missing database tables</li>
            </ul>
            <li>Gunakan tombol "Test Main API" untuk melihat response yang sebenarnya</li>
        </ul>
    </div>
</body>
</html>
