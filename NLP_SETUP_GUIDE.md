# NLP System Setup Guide

## Quick Fix untuk Error JSON

Jika Anda mengalami error `SyntaxError: Unexpected token '<'` pada `nlp-demo.php`, ikuti langkah-langkah berikut:

### 1. Automatic Setup (Recommended)

1. Buka browser dan akses: `http://localhost/pointmarket/setup-nlp.php`
2. Jika berhasil, Anda akan melihat response JSON: `{"success":true,"message":"NLP database tables created successfully"}`
3. Refresh halaman `nlp-demo.php`

### 2. Manual Setup

Jika automatic setup tidak berhasil, jalankan script SQL berikut di phpMyAdmin:

```sql
-- Jalankan di database 'pointmarket'
CREATE TABLE IF NOT EXISTS `nlp_analysis_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `assignment_id` int(11) DEFAULT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `original_text` text NOT NULL,
  `clean_text` text DEFAULT NULL,
  `word_count` int(11) DEFAULT 0,
  `sentence_count` int(11) DEFAULT 0,
  `total_score` decimal(5,2) DEFAULT 0.00,
  `grammar_score` decimal(5,2) DEFAULT 0.00,
  `keyword_score` decimal(5,2) DEFAULT 0.00,
  `structure_score` decimal(5,2) DEFAULT 0.00,
  `readability_score` decimal(5,2) DEFAULT 0.00,
  `sentiment_score` decimal(5,2) DEFAULT 0.00,
  `complexity_score` decimal(5,2) DEFAULT 0.00,
  `feedback` json DEFAULT NULL,
  `personalized_feedback` json DEFAULT NULL,
  `context_type` varchar(50) DEFAULT 'assignment',
  `analysis_version` varchar(10) DEFAULT '1.0',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `assignment_id` (`assignment_id`),
  KEY `quiz_id` (`quiz_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `nlp_keywords` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `context` varchar(100) NOT NULL,
  `keyword` varchar(255) NOT NULL,
  `weight` decimal(3,2) DEFAULT 1.00,
  `category` varchar(50) DEFAULT 'general',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `context_keyword` (`context`, `keyword`),
  KEY `context` (`context`),
  KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `nlp_feedback_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `score_range_min` decimal(5,2) NOT NULL,
  `score_range_max` decimal(5,2) NOT NULL,
  `category` varchar(50) NOT NULL,
  `feedback_text` text NOT NULL,
  `suggestions` json DEFAULT NULL,
  `vark_type` varchar(20) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `score_range` (`score_range_min`, `score_range_max`),
  KEY `category` (`category`),
  KEY `vark_type` (`vark_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3. Testing

Setelah setup selesai, test sistem dengan:

1. Buka `nlp-demo.php`
2. Klik tombol "Test API" - seharusnya menampilkan "✅ API Test Passed!"
3. Coba tulis teks di textarea dan lihat analisis real-time

### 4. Troubleshooting

#### Error: "Response is not valid JSON"
- Pastikan Apache dan MySQL/MariaDB running
- Clear browser cache (Ctrl+F5)
- Periksa file `includes/config.php` ada dan dapat diakses

#### Error: "Table doesn't exist"
- Jalankan `setup-nlp.php` atau SQL manual di atas
- Periksa koneksi database di `includes/config.php`

#### Error: "Permission denied"
- Pastikan folder `api/` memiliki permission yang tepat
- Restart Apache service

### 5. Fitur NLP yang Tersedia

- **Grammar Analysis**: Deteksi kesalahan tata bahasa
- **Keyword Analysis**: Identifikasi kata kunci relevan
- **Structure Analysis**: Evaluasi organisasi teks
- **Readability Score**: Tingkat keterbacaan
- **Sentiment Analysis**: Analisis tone positif/negatif
- **Complexity Score**: Tingkat kompleksitas teks

### 6. Integration

Sistem NLP dapat diintegrasikan dengan:
- Assignment submission (tugas)
- Quiz responses (kuis)
- Discussion forums
- Essay evaluation

### 7. Next Steps

Setelah NLP berfungsi:
1. Import data VARK dan MSLQ untuk personalisasi
2. Tambahkan lebih banyak keyword untuk domain spesifik
3. Kustomisasi feedback template
4. Integrasikan dengan sistem penilaian

---

**Status**: NLP system dengan fallback handler telah aktif
**Update**: July 2025 - Auto-setup dan error handling improved
