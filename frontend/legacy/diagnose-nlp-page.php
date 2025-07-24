<?php
// Diagnosa untuk fixed-nlp-demo-final.php
// File ini bertujuan untuk menemukan masalah mengapa halaman tidak tampil

// Aktifkan pelaporan error
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Output diagnosa dasar
echo "<h1>Diagnosa NLP Demo</h1>";
echo "<p>File ini berjalan pada: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Cek apakah sesi sudah dimulai
echo "<h2>Cek Status Sesi</h2>";
if (session_status() == PHP_SESSION_NONE) {
    echo "<p>Sesi belum dimulai. Mencoba memulai sesi...</p>";
    session_start();
    echo "<p>Sesi dimulai dengan ID: " . session_id() . "</p>";
} else {
    echo "<p>Sesi sudah dimulai dengan ID: " . session_id() . "</p>";
}
echo "<p>Data Sesi: </p><pre>" . print_r($_SESSION, true) . "</pre>";

// Cek keberadaan file config.php
echo "<h2>Cek File Konfigurasi</h2>";
$configFile = 'includes/config.php';
if (file_exists($configFile)) {
    echo "<p>✓ File config.php ditemukan</p>";
    echo "<p>Mencoba menginclude config.php...</p>";
    
    try {
        require_once $configFile;
        echo "<p>✓ File config.php berhasil di-include</p>";
        
        // Cek apakah fungsi-fungsi penting tersedia
        echo "<h3>Cek Fungsi-fungsi Penting</h3>";
        $functions = ['startSession', 'isLoggedIn', 'requireLogin', 'getCurrentUser'];
        foreach ($functions as $function) {
            if (function_exists($function)) {
                echo "<p>✓ Fungsi {$function} tersedia</p>";
            } else {
                echo "<p>✗ Fungsi {$function} TIDAK tersedia</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p>✗ Error saat menginclude config.php: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>✗ File config.php TIDAK ditemukan</p>";
}

// Cek keberadaan file-file include penting
echo "<h2>Cek File-file Include</h2>";
$includeFiles = ['includes/navbar.php', 'includes/sidebar.php', 'assets/js/nlp-analyzer.js'];
foreach ($includeFiles as $file) {
    if (file_exists($file)) {
        echo "<p>✓ File {$file} ditemukan</p>";
    } else {
        echo "<p>✗ File {$file} TIDAK ditemukan</p>";
    }
}

// Cek API
echo "<h2>Cek Akses API</h2>";
$apiFile = 'api/nlp-analysis.php';
if (file_exists($apiFile)) {
    echo "<p>✓ File API ditemukan</p>";
} else {
    echo "<p>✗ File API TIDAK ditemukan</p>";
}

// Cek akses ke database
echo "<h2>Cek Koneksi Database</h2>";
if (class_exists('Database')) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        if ($conn) {
            echo "<p>✓ Koneksi database berhasil</p>";
        } else {
            echo "<p>✗ Koneksi database gagal</p>";
        }
    } catch (Exception $e) {
        echo "<p>✗ Error saat koneksi ke database: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>✗ Class Database TIDAK tersedia</p>";
}

// Cek variabel user
echo "<h2>Cek Status User</h2>";
if (function_exists('isLoggedIn')) {
    echo "<p>Status login: " . (isLoggedIn() ? "Logged In" : "Not Logged In") . "</p>";
    
    if (function_exists('getCurrentUser')) {
        $user = getCurrentUser();
        if ($user) {
            echo "<p>Data user tersedia:</p>";
            echo "<pre>" . print_r($user, true) . "</pre>";
        } else {
            echo "<p>Data user TIDAK tersedia</p>";
            
            // Buat data user sementara untuk testing
            echo "<p>Membuat data user sementara untuk testing...</p>";
            $_SESSION['user_id'] = 1;
            $_SESSION['username'] = 'test_user';
            $_SESSION['name'] = 'Test User';
            $_SESSION['email'] = 'test@example.com';
            $_SESSION['role'] = 'siswa';
            
            echo "<p>Data sesi setelah dibuat:</p>";
            echo "<pre>" . print_r($_SESSION, true) . "</pre>";
        }
    } else {
        echo "<p>Fungsi getCurrentUser TIDAK tersedia</p>";
    }
} else {
    echo "<p>Fungsi isLoggedIn TIDAK tersedia</p>";
}

echo "<h2>Rekomendasi</h2>";
echo "<p>Setelah melihat hasil diagnosa di atas, coba ikuti langkah berikut:</p>";
echo "<ol>";
echo "<li>Periksa apakah semua file yang dibutuhkan ada dan dapat diakses</li>";
echo "<li>Pastikan fungsi-fungsi penting tersedia di config.php</li>";
echo "<li>Periksa apakah Anda sudah login, atau gunakan komentar kode untuk bypass login</li>";
echo "<li>Cek log error PHP di server untuk informasi lebih detail</li>";
echo "</ol>";

echo "<p><a href='fixed-nlp-demo-final.php'>Coba Buka fixed-nlp-demo-final.php Lagi</a></p>";
?>
