<?php
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit();
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    setErrorMessage('Token keamanan tidak valid. Silakan coba lagi.');
    header("Location: ../login.php");
    exit();
}

$username = sanitizeInput($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$role = sanitizeInput($_POST['role'] ?? '');

if (empty($username) || empty($password) || empty($role)) {
    setErrorMessage('Semua field harus diisi.');
    header("Location: ../login.php");
    exit();
}

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Get user from database
    $stmt = $pdo->prepare("SELECT id, username, password, name, email, role, last_login FROM users WHERE username = ? AND role = ?");
    $stmt->execute([$username, $role]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && verifyPassword($password, $user['password'])) {
        // Login successful
        startSession();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        
        // Update last login
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $updateStmt->execute([$user['id']]);
        
        // Log activity
        logActivity($user['id'], 'login', 'User logged in successfully', $pdo);
        
        setSuccessMessage('Login berhasil! Selamat datang, ' . $user['name']);
        header("Location: ../dashboard.php");
        exit();
    } else {
        // Login failed
        setErrorMessage('Username, password, atau role tidak valid.');
        header("Location: ../login.php");
        exit();
    }
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    setErrorMessage('Terjadi kesalahan sistem. Silakan coba lagi.');
    header("Location: ../login.php");
    exit();
}
?>
