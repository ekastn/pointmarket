# 🔍 Analisis "Main API returned invalid JSON" 

## 📋 Kemungkinan Penyebab Error

Berdasarkan analisis kode `api/nlp-analysis.php`, berikut adalah kemungkinan penyebab mengapa API mengembalikan invalid JSON:

### 1. 🚫 **Authentication Error (401 Unauthorized)**
**Penyebab paling umum** - API memeriksa login pada baris 28-33:
```php
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized. Please login first.'
    ]);
    exit;
}
```

**Indikasi**: 
- User belum login atau session expired
- Function `isLoggedIn()` mengembalikan false
- API mengembalikan JSON 401 yang valid, tapi mungkin tidak ter-handle dengan benar di frontend

### 2. 🗄️ **Database Connection Error**
**Lokasi**: Baris 35-37
```php
$database = new Database();
$pdo = $database->getConnection();
$nlpModel = new NLPModel($pdo);
```

**Kemungkinan masalah**:
- Database "pointmarket" tidak ada
- MySQL server tidak running
- Kredensial database salah
- Connection timeout

### 3. 📁 **Missing NLP Model File**
**Lokasi**: Baris 10
```php
require_once dirname(__DIR__) . '/includes/nlp-model.php';
```

**Kemungkinan masalah**:
- File `includes/nlp-model.php` tidak ada
- File ada tapi memiliki syntax error
- Class `NLPModel` tidak terdefinisi dengan benar

### 4. ⚠️ **PHP Errors/Warnings Tercampur dengan JSON**
**Penyebab**:
- PHP Notice/Warning muncul sebelum header JSON
- Error dari file include yang corrupt
- Output buffer tidak bersih

**Contoh output yang merusak JSON**:
```
Notice: Undefined variable: test in /path/to/file.php on line 15
{"success": true, "message": "OK"}
```

### 5. 🔧 **NLP Model Initialization Error**
**Lokasi**: Baris 37
```php
$nlpModel = new NLPModel($pdo);
```

**Kemungkinan masalah**:
- Class `NLPModel` constructor error
- Database tables untuk NLP tidak ada
- Dependencies yang diperlukan NLP model tidak tersedia

### 6. 📊 **Database Table Missing**
**Lokasi**: Function `getNLPStatistics()` pada baris 161-175

API mencoba check table `nlp_analysis_results`:
```php
$stmt = $pdo->query("SHOW TABLES LIKE 'nlp_analysis_results'");
if ($stmt->rowCount() == 0) {
    // Try to auto-create tables
    require_once dirname(__DIR__) . '/setup-nlp.php';
    ...
}
```

**Masalah**:
- Table tidak ada dan auto-create gagal
- File `setup-nlp.php` tidak ada atau error
- Permissions database tidak cukup

## 🔧 **Langkah Diagnosis**

### Step 1: Test File Debug
1. Buka: `http://localhost/pointmarket/debug-api-response.php`
2. Lihat output untuk mengidentifikasi error spesifik

### Step 2: Check Login Status
```php
// Test di browser console atau buat file test
session_start();
var_dump($_SESSION);
```

### Step 3: Test Database
```php
// Test koneksi database
$database = new Database();
$pdo = $database->getConnection();
var_dump($pdo);
```

### Step 4: Check Files
- ✅ `includes/config.php` - Ada
- ✅ `includes/nlp-model.php` - Ada  
- ❓ `setup-nlp.php` - Perlu dicek
- ❓ Database tables - Perlu dicek

## 🛠️ **Solusi Berdasarkan Diagnosis**

### Jika Error Authentication:
```php
// Buat test session di file terpisah
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'test_user';
$_SESSION['role'] = 'siswa';
```

### Jika Error Database:
```sql
-- Check database
SHOW DATABASES;
USE pointmarket;
SHOW TABLES;
```

### Jika Error NLP Model:
```php
// Gunakan backup model
copy includes/basic-nlp-model.php includes/nlp-model.php
```

### Jika Error PHP:
```php
// Tambahkan error suppression sementara
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);
```

## 📋 **Quick Fix Checklist**

1. **✅ Login Check**
   - Pastikan user sudah login
   - Atau buat test session

2. **✅ Database Check**  
   - Pastikan MySQL running
   - Database "pointmarket" ada
   - Tables NLP ada

3. **✅ File Check**
   - `includes/nlp-model.php` ada dan valid
   - `setup-nlp.php` ada (jika diperlukan)

4. **✅ Error Check**
   - Tidak ada PHP errors/warnings
   - Headers JSON benar
   - Output buffer bersih

5. **✅ Test API**
   - Test dengan parameter ?test=1
   - Verify JSON response valid
   - Check response headers

## 🎯 **Rekomendasi Langkah Selanjutnya**

1. **Jalankan diagnosis file**: `debug-api-response.php`
2. **Periksa hasil dan identifikasi error spesifik**
3. **Terapkan fix yang sesuai**
4. **Test ulang dengan `nlp-diagnostics.php`**

---

**File yang telah dibuat untuk debugging:**
- `debug-api-response.php` - Diagnosis lengkap API
- `simple-api-test.php` - Test API sederhana
- File ini - Analisis masalah
