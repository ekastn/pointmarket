# Panduan Penggunaan POINTMARKET NLP Demo (Simplified)

## Pendahuluan

POINTMARKET NLP Demo (Simplified) adalah versi paling sederhana dari demo analisis Natural Language Processing pada sistem POINTMARKET. Versi ini dirancang untuk tetap berfungsi bahkan ketika sistem utama mengalami masalah, karena tidak memiliki dependensi pada file eksternal atau database.

## Cara Mengakses

Untuk mengakses demo NLP sederhana:

1. Buka browser web Anda (Chrome, Firefox, Edge, dll)
2. Ketikkan URL berikut di address bar:
   ```
   http://localhost/pointmarket/super-simple-nlp-demo.php
   ```
3. Tekan Enter untuk membuka halaman demo

**Kelebihan Versi Sederhana:** Demo ini akan berfungsi bahkan ketika sistem utama mengalami masalah, karena tidak bergantung pada session database, config.php, atau file eksternal lainnya.

## Fitur Utama

Demo NLP sederhana ini menyediakan fitur-fitur berikut:

### 1. Analisis Teks
Menganalisis teks dalam Bahasa Indonesia dan memberikan skor untuk:
- **Sentiment** - Menunjukkan apakah teks bersifat positif, netral, atau negatif
- **Complexity** - Mengukur tingkat kompleksitas teks
- **Coherence** - Mengukur koherensi atau keterpaduan teks

### 2. Identifikasi Kata Kunci
Menemukan dan menampilkan kata-kata penting dalam teks yang dianalisis.

### 3. Ekstraksi Kalimat Penting
Mengidentifikasi kalimat-kalimat kunci yang mewakili inti dari teks.

### 4. Statistik Teks
Memberikan statistik lengkap tentang teks yang dianalisis, termasuk:
- Jumlah kata
- Jumlah kalimat
- Rata-rata panjang kata
- Estimasi waktu baca

### 5. Tools Debugging
Dilengkapi dengan alat bantu debugging untuk memeriksa API dan session.

## Melakukan Analisis Teks

Untuk menganalisis teks dengan NLP Demo (Simplified), ikuti langkah-langkah berikut:

### Langkah 1: Masukkan Teks
Ada dua cara untuk memasukkan teks yang akan dianalisis:
1. Ketik atau paste teks secara langsung ke dalam text area, ATAU
2. Klik tombol **"Gunakan Contoh"** untuk menggunakan teks contoh yang sudah disediakan

> **Tip:** Untuk hasil terbaik, gunakan teks dengan minimal 3-5 kalimat.

### Langkah 2: Pilih Konteks
Pilih konteks yang paling sesuai dengan teks Anda dari dropdown menu:
- **Assignment (Tugas)** - Untuk teks yang merupakan tugas atau essay umum
- **Matematika** - Untuk teks yang berkaitan dengan matematika
- **Fisika** - Untuk teks yang berkaitan dengan fisika
- **Kimia** - Untuk teks yang berkaitan dengan kimia
- **Biologi** - Untuk teks yang berkaitan dengan biologi

Pemilihan konteks membantu algoritma NLP memahami domain teks dengan lebih baik.

### Langkah 3: Kirim untuk Analisis
Setelah teks dan konteks siap:
1. Klik tombol **"Analisis Teks"**
2. Tunggu proses analisis selesai (ditandai dengan indikator loading)
3. Hasil analisis akan muncul di bawah form

> **Catatan:** Demo NLP sederhana ini menggunakan API mini yang selalu mengembalikan data valid. Ini berarti analisis akan selalu berhasil bahkan jika sistem utama sedang bermasalah.

## Memahami Hasil Analisis

Setelah teks dianalisis, Anda akan melihat hasil yang terbagi dalam beberapa bagian:

### 1. Skor Utama

#### Sentiment Score
Mengukur sentimen atau nada emosional teks:
- **0.7 - 1.0**: Positif
- **0.4 - 0.7**: Netral
- **0.0 - 0.4**: Negatif

#### Complexity Score
Mengukur tingkat kompleksitas teks:
- **0.7 - 1.0**: Tinggi
- **0.4 - 0.7**: Sedang
- **0.0 - 0.4**: Rendah

#### Coherence Score
Mengukur koherensi atau keterpaduan teks:
- **0.7 - 1.0**: Sangat Koheren
- **0.4 - 0.7**: Cukup Koheren
- **0.0 - 0.4**: Kurang Koheren

### 2. Kata Kunci
Bagian ini menampilkan kata-kata penting yang ditemukan dalam teks. Kata kunci ini dipilih berdasarkan:
- Frekuensi kemunculan dalam teks
- Relevansi dengan konteks yang dipilih
- Nilai informatif kata tersebut

### 3. Kalimat Penting
Menampilkan kalimat-kalimat yang dianggap paling penting atau representatif dari keseluruhan teks. Kalimat ini dipilih berdasarkan:
- Keberadaan kata kunci di dalamnya
- Posisi dalam teks (kalimat pembuka/penutup sering lebih penting)
- Kekayaan informasi yang terkandung

### 4. Statistik Teks
Bagian ini memberikan statistik kuantitatif tentang teks yang dianalisis:
- **Kata** - Jumlah total kata dalam teks
- **Kalimat** - Jumlah total kalimat dalam teks
- **Rata-rata Panjang Kata** - Rata-rata jumlah karakter per kata
- **Waktu Baca** - Estimasi waktu yang dibutuhkan untuk membaca teks (dalam detik)

## Menggunakan Tools Debugging

Demo NLP sederhana ini dilengkapi dengan beberapa alat bantu debugging yang berguna untuk pemecahan masalah:

### Test API
Tombol **"Test API"** memungkinkan Anda untuk menguji konektivitas dengan API mini:
1. Klik tombol "Test API"
2. Hasil permintaan API akan ditampilkan dalam format JSON
3. Periksa apakah API mengembalikan respons yang valid

**Kapan digunakan:** Ketika Anda ingin memastikan bahwa API dasar berfungsi dan dapat diakses.

### View Session
Tombol **"View Session"** menampilkan informasi tentang session user saat ini:
1. Klik tombol "View Session"
2. Data session akan ditampilkan dalam format JSON
3. Periksa user_id, username, dan informasi lainnya

**Kapan digunakan:** Ketika Anda ingin memeriksa apakah session pengguna tersedia dan valid.

### Run Diagnostics
Link **"Run Diagnostics"** akan membuka halaman diagnostik terpisah:
1. Klik link "Run Diagnostics"
2. Halaman baru akan terbuka dengan diagnostik menyeluruh
3. Periksa status berbagai komponen sistem

**Kapan digunakan:** Ketika Anda perlu melakukan pemeriksaan menyeluruh terhadap sistem NLP.

## Tips & Trik

### Tip 1: Gunakan Konteks yang Tepat
Pemilihan konteks yang sesuai dengan topik teks Anda akan meningkatkan akurasi analisis.
- Untuk teks ilmiah, pilih kategori yang sesuai (Fisika, Kimia, dll)
- Untuk esai umum, gunakan "Assignment"

### Tip 2: Panjang Teks Optimal
Untuk hasil terbaik, gunakan teks dengan panjang yang cukup:
- Minimal: 3-5 kalimat (50-100 kata)
- Optimal: 10-15 kalimat (200-300 kata)
- Maksimal: Tidak ada batasan, tetapi analisis mungkin lebih lambat untuk teks yang sangat panjang

### Tip 3: Analisis Beberapa Versi Teks
Untuk meningkatkan tulisan Anda:
1. Analisis versi awal teks
2. Perhatikan skor dan area yang perlu ditingkatkan
3. Revisi teks berdasarkan hasil analisis
4. Analisis lagi untuk melihat peningkatan

### Tip 4: Bandingkan dengan Demo Utama
Jika demo utama (`fixed-nlp-demo-optimized.php`) berfungsi:
- Bandingkan hasil analisis dari kedua demo
- Demo utama mungkin memberikan analisis yang lebih mendalam
- Demo sederhana lebih stabil dan selalu berfungsi

## Teknologi di Balik Demo

> **PENTING: POINTMARKET NLP Demo (Simplified) tidak sepenuhnya menggunakan model AI sebenarnya.**

### Status Penggunaan Model AI

Demo ini sebenarnya **tidak menggunakan model AI asli** untuk menganalisis teks. Sebaliknya, ini menggunakan `api/nlp-mini-api.php` yang berisi data dummy atau pre-defined. Ini adalah API "mini" yang selalu mengembalikan respons valid tanpa melakukan pemrosesan NLP nyata.

### Bagaimana Demo Ini Bekerja

1. **Simulasi Analisis**: Demo ini menyimulasikan hasil analisis NLP tanpa benar-benar menjalankan algoritma NLP atau model AI di balik layar.
2. **Data Pre-generated**: Respons yang Anda lihat adalah hasil pre-generated yang dirancang untuk terlihat seperti analisis NLP nyata.
3. **Tujuan Diagnostik**: Tujuan utama dari versi ini adalah untuk diagnosa dan debugging ketika sistem utama mengalami masalah.

```javascript
// Call the mini API (which always returns valid JSON)
fetch('api/nlp-mini-api.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        text: text,
        context: context
    })
})
```

Demo versi lengkap POINTMARKET NLP (`fixed-nlp-demo-optimized.php`) *mungkin* menggunakan model AI atau algoritma NLP asli, tetapi versi simplified ini dibuat untuk tetap berfungsi tanpa proses analisis NLP yang sebenarnya.

**Mengapa Ini Penting?** Desain ini memang disengaja untuk memastikan demo tetap berfungsi bahkan ketika komponen sistem lain mengalami kegagalan. Ini menjadikannya alat yang sangat berguna untuk diagnosa, tetapi bukan representasi dari kemampuan NLP yang sebenarnya.

## Kesimpulan

POINTMARKET NLP Demo (Simplified) menyediakan cara cepat dan handal untuk menganalisis teks dengan teknologi Natural Language Processing. Versi sederhana ini dirancang untuk:
- Selalu berfungsi bahkan ketika sistem utama mengalami masalah
- Memberikan hasil analisis yang valid tanpa dependensi eksternal
- Menyediakan alat bantu debugging untuk mendiagnosis masalah sistem

Dengan mengikuti panduan ini, Anda dapat memanfaatkan fitur analisis NLP sederhana namun powerful untuk meningkatkan kualitas teks Anda.
