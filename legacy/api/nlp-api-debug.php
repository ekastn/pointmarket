<?php
/**
 * API Debugging Helper untuk NLP Analysis API
 * File ini membantu melakukan debug pada API NLP tanpa perlu login
 */

// Aktifkan error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Mulai session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set session testing untuk bypass login check
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'test_user';
$_SESSION['name'] = 'Test User';
$_SESSION['email'] = 'test@example.com';
$_SESSION['role'] = 'siswa';

// Buat fungsi untuk melakukan test API internal
function testNlpApi($text) {
    // Simpan input text
    $input = json_encode(['text' => $text]);
    
    // Persiapkan output buffer untuk menangkap output API
    ob_start();
    
    // Set variabel $_POST seperti yang akan diterima API
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = json_decode($input, true);
    
    // Include API file secara langsung (bypass HTTP request)
    include_once __DIR__ . '/nlp-analysis.php';
    
    // Ambil output dan bersihkan buffer
    $output = ob_get_clean();
    
    return $output;
}

// Cek jika dipanggil langsung melalui browser
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $testText = "Ini adalah contoh teks untuk pengujian API NLP. Semoga hasilnya baik dan informatif.";
    $result = testNlpApi($testText);
    
    header('Content-Type: application/json');
    echo json_encode([
        'testText' => $testText,
        'apiResult' => json_decode($result, true),
        'rawResult' => $result
    ], JSON_PRETTY_PRINT);
}
?>
