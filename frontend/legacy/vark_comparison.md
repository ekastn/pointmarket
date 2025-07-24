# Perbandingan VARK pada questionnaire.php vs vark-assessment.php

## questionnaire.php
- **Fungsi**: Menampilkan daftar semua kuesioner yang tersedia termasuk VARK
- **Tampilan VARK**: 
  - Jika sudah selesai: Menampilkan hasil VARK (skor dan learning style)
  - Jika belum selesai: Menampilkan tombol untuk mulai assessment
  - Mengarahkan ke vark-assessment.php untuk melakukan assessment
- **Konten**: 
  - Resume hasil VARK
  - Informasi singkat tentang VARK
  - Tombol untuk start/retake assessment
  - Tidak ada pertanyaan VARK di halaman ini

## vark-assessment.php  
- **Fungsi**: Halaman khusus untuk melakukan VARK assessment
- **Tampilan VARK**:
  - Menampilkan 16 pertanyaan VARK lengkap
  - Form untuk menjawab pertanyaan
  - Proses perhitungan dan penyimpanan hasil
  - Menampilkan hasil setelah selesai
- **Konten**:
  - Penjelasan detail tentang VARK
  - 16 pertanyaan dengan pilihan a, b, c, d
  - Form submission untuk assessment
  - Hasil assessment dengan grafik skor

## Perbedaan Utama:
1. **questionnaire.php**: Halaman overview/dashboard untuk semua kuesioner
2. **vark-assessment.php**: Halaman khusus untuk melakukan assessment VARK
3. **questionnaire.php**: Tidak ada pertanyaan, hanya informasi dan link
4. **vark-assessment.php**: Berisi 16 pertanyaan VARK yang harus dijawab

## Status Saat Ini:
- Database VARK sudah terisi dengan 16 pertanyaan dan 64 opsi jawaban
- questionnaire.php menampilkan informasi VARK dengan benar
- vark-assessment.php seharusnya menampilkan 16 pertanyaan VARK untuk dijawab
