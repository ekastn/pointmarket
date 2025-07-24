# Debugging Guide untuk NLP Demo POINTMARKET

Dokumen ini berisi panduan untuk memperbaiki dan mendiagnosis masalah pada demo NLP di POINTMARKET.

## Permasalahan yang Telah Diidentifikasi

1. **Halaman NLP Demo Blank / Tidak Tampil**
   - Penyebab: Kemungkinan besar masalah session dan redirect ke login.php
   - Pengecekan session login pada API dan halaman utama menyebabkan blank page

2. **Error Loading Stats (Invalid JSON)**
   - Penyebab: API mengembalikan HTML/error PHP daripada JSON
   - Solusi: Pastikan API mengembalikan JSON valid dan gunakan fallback

3. **Alert Demo di Dashboard Hilang Terlalu Cepat**
   - Penyebab: Auto-dismiss alert di dashboard.js
   - Solusi: Tambahkan class demo-alert dan pengecualian di JS

## File-file Perbaikan yang Telah Dibuat

1. **`fixed-nlp-demo-optimized.php`**
   - Versi yang telah dioptimalkan dengan lebih sedikit debugging
   - Menggunakan session testing untuk bypass login check
   - Error handling yang lebih baik

2. **`nlp-api-debug.php`**
   - Tool untuk debug API NLP tanpa perlu HTTP request
   - Membantu mendiagnosis masalah pada API

3. **`nlp-debug-tools.html`**
   - Dashboard debug untuk menguji semua komponen
   - Link ke semua versi halaman dan API testing

4. **`get-session-info.php`**, **`create-test-session.php`**, **`destroy-session.php`**
   - Alat bantu mengelola session untuk debugging

## Langkah-langkah Troubleshooting

1. **Pastikan Session Berjalan**
   - Buka `nlp-debug-tools.html` dan periksa status session
   - Jika tidak ada session, klik "Create Test Session"

2. **Cek API Berfungsi**
   - Gunakan "Test API dengan Javascript" di `nlp-debug-tools.html`
   - Jika API utama gagal, gunakan "Try Fallback API"

3. **Buka Halaman Demo yang Telah Diperbaiki**
   - `fixed-nlp-demo-optimized.php` seharusnya berjalan dengan baik
   - Jika masih blank, periksa error di browser console (F12)

4. **Periksa Logs**
   - File `nlp-demo-error.log` berisi error yang terjadi
   - Xampp error logs di `C:\xampp\php\logs\php_error_log`

## Rekomendasi Perbaikan Permanen

1. **Perbaiki Pengelolaan Session**
   - Gunakan try-catch pada semua pemeriksaan login
   - Tambahkan fallback yang baik saat session tidak ada

2. **Perbaiki Error Handling di API**
   - Pastikan API selalu mengembalikan JSON valid
   - Gunakan HTTP status code yang benar (200, 401, 500, dll)

3. **Perbaiki Frontend JavaScript**
   - Tambahkan error handling yang lebih baik
   - Gunakan fallback API saat API utama gagal

## Cara Menggunakan Alat Debug

1. Buka `http://localhost/pointmarket/nlp-debug-tools.html`
2. Periksa status session dan buat session test jika perlu
3. Uji API untuk memastikan berfungsi
4. Buka halaman demo yang diperbaiki di `fixed-nlp-demo-optimized.php`

Dengan langkah-langkah di atas, masalah NLP Demo seharusnya dapat terselesaikan.
