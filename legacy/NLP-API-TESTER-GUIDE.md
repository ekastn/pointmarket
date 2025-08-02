# Panduan Penggunaan NLP API Tester

## Pendahuluan

NLP API Tester adalah alat diagnostik yang dibuat untuk membantu pengembang dan administrator dalam menguji fungsionalitas API NLP pada sistem POINTMARKET. Alat ini memungkinkan Anda untuk mengisolasi masalah pada API tanpa perlu berinteraksi dengan halaman PHP, membantu mengidentifikasi apakah masalah terletak pada frontend, backend, atau keduanya.

## Cara Mengakses NLP API Tester

1. Buka browser web dan navigasikan ke URL berikut:
   ```
   http://localhost/pointmarket/nlp-api-tester.html
   ```

2. Halaman tester akan muncul dengan tiga bagian utama:
   - **Test API Endpoints**: Untuk pengujian cepat API yang tersedia
   - **Send Custom Request**: Untuk mengirim request khusus dengan parameter yang dikustomisasi
   - **API Information**: Informasi tentang endpoint yang tersedia dan tips pengujian

## Bagian 1: Test API Endpoints

Bagian ini memungkinkan Anda untuk melakukan pengujian cepat terhadap tiga API yang tersedia:

1. **Test Main API**: Menguji API utama (`nlp-analysis.php`)
   - Klik tombol "Test Main API" untuk mengirim request GET dengan parameter `test=1`
   - Hasil akan ditampilkan di bawah tombol

2. **Test Backup API**: Menguji API backup (`nlp-backup-api.php`)
   - Klik tombol "Test Backup API" untuk mengirim request GET dengan parameter `test=1`
   - Hasil akan ditampilkan di bawah tombol

3. **Test Mini API**: Menguji API mini (`nlp-mini-api.php`)
   - Klik tombol "Test Mini API" untuk mengirim request GET dengan parameter `test=1`
   - API ini selalu mengembalikan data dummy valid dan merupakan indikator baik untuk memeriksa konektivitas dasar

### Cara Menginterpretasi Hasil Test API Endpoints

- **Status 200 dengan Content-Type application/json**: API berfungsi dengan baik
- **Status non-200 atau Content-Type bukan application/json**: Ada masalah dengan API
- **Error Network atau CORS**: Masalah koneksi atau konfigurasi server

## Bagian 2: Send Custom Request

Bagian ini memungkinkan Anda untuk membuat request khusus ke API:

1. **Pilih API Endpoint**:
   - Pilih API yang ingin diuji dari dropdown "API Endpoint"
   - Tersedia 4 opsi: Main API, Backup API, Mini API, dan Debug API

2. **Pilih Request Method**:
   - GET: Untuk request statistik, tes, atau parameter lainnya
   - POST: Untuk mengirim teks yang akan dianalisis

3. **Untuk Request GET**:
   - Isi field "GET Parameters" dengan parameter yang diinginkan
   - Format: `action=statistics&v=12345`

4. **Untuk Request POST**:
   - Isi field "POST Body (JSON)" dengan data JSON yang akan dikirim
   - Format contoh: `{"text": "Ini adalah contoh teks untuk dianalisis."}`

5. **Kirim Request**:
   - Klik tombol "Send Request" untuk mengirim request
   - Hasil akan ditampilkan di bagian "Response"

### Contoh Penggunaan Custom Request

#### Contoh 1: Mengambil Statistik NLP

1. Pilih "Main API (nlp-analysis.php)" dari dropdown
2. Pilih method "GET"
3. Isi GET Parameters dengan: `action=statistics&v=12345`
4. Klik "Send Request"

#### Contoh 2: Menganalisis Teks

1. Pilih "Mini API (nlp-mini-api.php)" dari dropdown
2. Pilih method "POST"
3. Isi POST Body dengan: `{"text": "Teknologi dalam pendidikan sangat penting karena dapat meningkatkan kualitas pembelajaran."}`
4. Klik "Send Request"

## Bagian 3: API Information

Bagian ini memberikan informasi tentang endpoint API yang tersedia:

1. **Main API** (`api/nlp-analysis.php`):
   - API utama yang memerlukan login dan koneksi database
   - Fitur lengkap namun memiliki lebih banyak dependensi

2. **Backup API** (`api/nlp-backup-api.php`):
   - API cadangan yang menggunakan model NLP dasar
   - Lebih sederhana dan lebih sedikit dependensi

3. **Mini API** (`api/nlp-mini-api.php`):
   - API minimal yang selalu mengembalikan JSON valid dengan data dummy
   - Tidak memerlukan login atau koneksi database
   - Ideal untuk debugging frontend

4. **Debug API** (`api/nlp-api-debug.php`):
   - Alat debugging untuk API utama yang menguji fungsi internal

## Strategi Debugging dengan NLP API Tester

Berikut adalah strategi step-by-step untuk mendiagnosis masalah pada sistem NLP:

### Langkah 1: Verifikasi Konektivitas Dasar

1. Klik "Test Mini API"
2. Jika berhasil, konektivitas dasar berfungsi
3. Jika gagal, kemungkinan ada masalah dengan server atau konfigurasi web

### Langkah 2: Periksa API Utama

1. Klik "Test Main API"
2. Bandingkan dengan hasil Mini API
3. Jika Main API gagal tapi Mini API berhasil, masalahnya ada pada API utama

### Langkah 3: Coba Request POST Sederhana

1. Gunakan bagian "Send Custom Request"
2. Pilih Mini API dengan method POST
3. Kirim teks sederhana untuk dianalisis
4. Verifikasi bahwa response berformat JSON yang valid

### Langkah 4: Debug API Utama dengan Detail

1. Ulangi langkah 3 tapi gunakan Main API
2. Periksa error yang muncul
3. Gunakan informasi error untuk mengidentifikasi masalah spesifik

## Troubleshooting Umum

### 1. API Mengembalikan Error 401 Unauthorized

**Kemungkinan Penyebab**:
- Session login tidak ada atau tidak valid
- API memeriksa status login sebelum memproses request

**Solusi**:
1. Gunakan file `get-session-info.php` untuk memeriksa status session
2. Gunakan file `create-test-session.php` untuk membuat session pengujian
3. Edit file API untuk menonaktifkan sementara pengecekan login untuk debugging

### 2. API Mengembalikan HTML Alih-alih JSON

**Kemungkinan Penyebab**:
- Error PHP yang muncul sebelum header JSON ditetapkan
- Include file yang gagal atau error syntax

**Solusi**:
1. Periksa response HTML untuk mengidentifikasi error PHP
2. Tambahkan error handling di awal file API
3. Gunakan Mini API sebagai alternatif sementara

### 3. Network Error atau CORS Issue

**Kemungkinan Penyebab**:
- Konfigurasi server yang tidak benar
- Headers CORS tidak ditetapkan dengan benar

**Solusi**:
1. Periksa console browser (F12) untuk detail error
2. Pastikan headers CORS ditetapkan dengan benar di file API
3. Pastikan server web (Apache/XAMPP) berjalan dengan benar

## Kesimpulan

NLP API Tester adalah alat yang ampuh untuk mendiagnosis masalah pada sistem NLP POINTMARKET. Dengan mengikuti panduan ini, Anda dapat secara metodis mengidentifikasi dan mengisolasi masalah, memungkinkan perbaikan yang lebih efisien dan efektif.

Jika Anda menemukan masalah yang tidak dapat diselesaikan dengan alat ini, pertimbangkan untuk menggunakan alat diagnostik tambahan seperti `nlp-diagnostics.php` yang memeriksa komponen sistem secara lebih menyeluruh.
