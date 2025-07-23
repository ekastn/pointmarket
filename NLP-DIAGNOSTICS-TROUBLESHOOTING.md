# NLP Diagnostics Troubleshooting Guide

## 📋 Daftar Isi
1. [Error "body stream already read"](#error-body-stream-already-read)
2. [API tidak merespons](#api-tidak-merespons)
3. [Response tidak valid JSON](#response-tidak-valid-json)
4. [Best Practices](#best-practices)

---

## 🚨 Error "body stream already read"

### Penyebab
Error "Failed to execute 'text' on 'Response': body stream already read" terjadi karena:
- JavaScript Response body stream hanya bisa dibaca **sekali**
- Ketika kita memanggil `response.json()` atau `response.text()`, stream langsung "dikonsumsi"
- Attempt untuk membaca stream yang sama lagi akan menghasilkan error

### Kode yang Menyebabkan Error
```javascript
// ❌ SALAH - Menyebabkan error
const response = await fetch(url);
const json = await response.json();  // Membaca stream pertama kali
const text = await response.text();  // ERROR: stream sudah dibaca
```

### Solusi yang Benar
```javascript
// ✅ BENAR - Menggunakan clone()
const response = await fetch(url);
const responseClone = response.clone();  // Membuat copy
const json = await response.json();     // Membaca original
const text = await responseClone.text(); // Membaca copy

// ✅ ALTERNATIF - Restructure code
const response = await fetch(url);
const text = await response.text();
try {
    const json = JSON.parse(text);
    // Process JSON
} catch (e) {
    // Handle as text
}
```

---

## 🌐 API Tidak Merespons

### Kemungkinan Penyebab
1. **File API tidak ada**
   - `api/nlp-analysis.php` tidak ditemukan
   - Path salah atau file terhapus

2. **Syntax Error di PHP**
   - Kesalahan sintaks mencegah eksekusi
   - Fatal error menghentikan script

3. **Database Connection Issue**
   - Database tidak tersedia
   - Kredensial database salah

4. **Dependency Missing**
   - Library atau file include tidak ada
   - Konfigurasi tidak lengkap

### Solusi
```php
// Tambahkan error reporting di awal file API
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Tambahkan try-catch untuk database
try {
    $pdo = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
```

---

## 📝 Response Tidak Valid JSON

### Penyebab
- API mengembalikan HTML error page
- PHP notices/warnings tercampur dengan output
- Content-Type header salah

### Solusi
```php
// Set proper content type
header('Content-Type: application/json');

// Suppress notices (hanya untuk production)
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Always return valid JSON
try {
    $result = ['status' => 'success', 'data' => $data];
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
```

---

## 💡 Best Practices

### 1. Response Handling
```javascript
async function safeAPICall(url) {
    try {
        const response = await fetch(url);
        
        // Check status first
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        // Check content type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error(`Expected JSON, got ${contentType}: ${text}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('API call failed:', error);
        throw error;
    }
}
```

### 2. PHP API Structure
```php
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Your API logic here
    $result = [
        'status' => 'success',
        'data' => $responseData,
        'timestamp' => date('c')
    ];
    
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('c')
    ]);
}
?>
```

### 3. Error Handling
```javascript
// Comprehensive error handling
async function robustAPICall(url) {
    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 5000); // 5s timeout
        
        const response = await fetch(url, {
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        return data;
        
    } catch (error) {
        if (error.name === 'AbortError') {
            throw new Error('Request timeout');
        }
        throw error;
    }
}
```

### 4. Debugging Tips
- Gunakan browser Developer Tools Network tab
- Periksa PHP error logs
- Tambahkan logging di API endpoints
- Test API secara terpisah sebelum integrasi
- Gunakan cache busting untuk development (`?v=timestamp`)

---

## 🔧 Implementasi di nlp-diagnostics.php

File `nlp-diagnostics.php` telah diperbaiki dengan:

1. **Response Cloning**: Menggunakan `response.clone()` untuk membaca response multiple times
2. **Better Error Handling**: Menangkap berbagai jenis error dengan informasi yang lebih detail
3. **Response Time Monitoring**: Mengukur waktu response API
4. **Detailed Logging**: Menampilkan status code, headers, dan content type
5. **Progressive Enhancement**: Menggunakan `<details>` untuk menampilkan informasi tambahan

### Contoh Perbaikan
```javascript
// Sebelum (menyebabkan error)
const result = await response.json();
const text = await response.text(); // ERROR!

// Sesudah (aman)
const responseClone = response.clone();
const result = await response.json();
const text = await responseClone.text(); // OK!
```

---

## 📞 Bantuan Lebih Lanjut

Jika masih mengalami masalah:
1. Periksa console browser untuk error JavaScript
2. Periksa PHP error logs
3. Jalankan `nlp-diagnostics.php` untuk diagnosis lengkap
4. Pastikan semua file dependencies tersedia

**File yang telah diperbaiki:**
- `nlp-diagnostics.php` - Tool diagnostik utama
- API error handling telah diperbaiki
- Response stream handling telah dioptimalkan
