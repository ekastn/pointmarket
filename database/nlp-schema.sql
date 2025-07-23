-- SQL Schema untuk NLP Analysis Results
-- Tambahkan ke database pointmarket

-- Tabel untuk menyimpan hasil analisis NLP
CREATE TABLE `nlp_analysis_results` (
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
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assignment_id`) REFERENCES `assignments`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`quiz_id`) REFERENCES `quiz`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel untuk menyimpan keywords berdasarkan konteks
CREATE TABLE `nlp_keywords` (
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

-- Insert default keywords
INSERT INTO `nlp_keywords` (`context`, `keyword`, `weight`, `category`) VALUES
-- Matematika
('matematik', 'rumus', 1.00, 'core'),
('matematik', 'perhitungan', 1.00, 'core'),
('matematik', 'angka', 0.80, 'basic'),
('matematik', 'operasi', 0.90, 'core'),
('matematik', 'hasil', 0.70, 'basic'),
('matematik', 'metode', 0.80, 'advanced'),
('matematik', 'langkah', 0.75, 'basic'),
('matematik', 'solusi', 0.85, 'core'),

-- Fisika
('fisika', 'gaya', 1.00, 'core'),
('fisika', 'gerak', 1.00, 'core'),
('fisika', 'energi', 1.00, 'core'),
('fisika', 'hukum', 0.90, 'core'),
('fisika', 'rumus', 0.80, 'basic'),
('fisika', 'percepatan', 0.90, 'core'),
('fisika', 'kecepatan', 0.85, 'basic'),
('fisika', 'massa', 0.80, 'basic'),

-- Kimia
('kimia', 'unsur', 1.00, 'core'),
('kimia', 'reaksi', 1.00, 'core'),
('kimia', 'molekul', 1.00, 'core'),
('kimia', 'senyawa', 0.90, 'core'),
('kimia', 'atom', 0.80, 'basic'),
('kimia', 'ikatan', 0.90, 'core'),
('kimia', 'elektron', 0.85, 'basic'),
('kimia', 'ion', 0.80, 'basic'),

-- Biologi
('biologi', 'sel', 1.00, 'core'),
('biologi', 'organisme', 1.00, 'core'),
('biologi', 'protein', 0.90, 'core'),
('biologi', 'gen', 0.80, 'basic'),
('biologi', 'evolusi', 0.90, 'core'),
('biologi', 'ekosistem', 0.80, 'basic'),
('biologi', 'DNA', 0.85, 'core'),
('biologi', 'kromosom', 0.80, 'basic'),

-- General Assignment
('assignment', 'analisis', 1.00, 'core'),
('assignment', 'konsep', 1.00, 'core'),
('assignment', 'penjelasan', 0.90, 'core'),
('assignment', 'contoh', 0.80, 'basic'),
('assignment', 'kesimpulan', 0.90, 'core'),
('assignment', 'argumen', 0.80, 'advanced'),
('assignment', 'evaluasi', 0.85, 'advanced'),
('assignment', 'implementasi', 0.80, 'advanced');

-- Tabel untuk menyimpan progress NLP siswa
CREATE TABLE `nlp_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `month` int(2) NOT NULL,
  `year` int(4) NOT NULL,
  `total_analyses` int(11) DEFAULT 0,
  `average_score` decimal(5,2) DEFAULT 0.00,
  `best_score` decimal(5,2) DEFAULT 0.00,
  `improvement_rate` decimal(5,2) DEFAULT 0.00,
  `grammar_improvement` decimal(5,2) DEFAULT 0.00,
  `keyword_improvement` decimal(5,2) DEFAULT 0.00,
  `structure_improvement` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_month_year` (`student_id`, `month`, `year`),
  KEY `student_id` (`student_id`),
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel untuk menyimpan feedback templates
CREATE TABLE `nlp_feedback_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(100) NOT NULL,
  `score_range_min` int(3) NOT NULL,
  `score_range_max` int(3) NOT NULL,
  `component` varchar(50) NOT NULL, -- grammar, keyword, structure, etc.
  `vark_style` varchar(50) DEFAULT 'all',
  `mslq_profile` varchar(50) DEFAULT 'all',
  `feedback_text` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `component` (`component`),
  KEY `vark_style` (`vark_style`),
  KEY `score_range` (`score_range_min`, `score_range_max`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default feedback templates
INSERT INTO `nlp_feedback_templates` (`template_name`, `score_range_min`, `score_range_max`, `component`, `vark_style`, `mslq_profile`, `feedback_text`) VALUES
-- Grammar feedback
('grammar_low', 0, 50, 'grammar', 'all', 'all', '⚠️ Terdapat beberapa kesalahan tata bahasa. Periksa kembali penggunaan kata hubung dan tanda baca.'),
('grammar_medium', 51, 75, 'grammar', 'all', 'all', '📝 Tata bahasa cukup baik, namun masih bisa diperbaiki pada beberapa bagian.'),
('grammar_high', 76, 100, 'grammar', 'all', 'all', '✅ Tata bahasa sudah sangat baik!'),

-- Keyword feedback
('keyword_low', 0, 50, 'keyword', 'all', 'all', '📝 Coba gunakan lebih banyak kata kunci yang relevan dengan topik.'),
('keyword_medium', 51, 75, 'keyword', 'all', 'all', '🎯 Penggunaan kata kunci sudah cukup baik, bisa ditingkatkan lagi.'),
('keyword_high', 76, 100, 'keyword', 'all', 'all', '🎯 Penggunaan kata kunci sudah tepat dan relevan!'),

-- Structure feedback
('structure_low', 0, 50, 'structure', 'all', 'all', '🔄 Struktur tulisan bisa diperbaiki dengan menggunakan kata penghubung dan numbering.'),
('structure_medium', 51, 75, 'structure', 'all', 'all', '📊 Struktur tulisan sudah cukup terorganisir, bisa lebih sistematis.'),
('structure_high', 76, 100, 'structure', 'all', 'all', '📊 Struktur tulisan sudah terorganisir dengan baik!'),

-- VARK-specific feedback
('visual_feedback', 0, 100, 'general', 'Visual', 'all', '👁️ Sebagai visual learner, coba tambahkan deskripsi visual atau diagram dalam jawaban Anda.'),
('auditory_feedback', 0, 100, 'general', 'Auditory', 'all', '🎵 Sebagai auditory learner, coba jelaskan konsep dengan kata-kata yang lebih deskriptif.'),
('reading_feedback', 0, 100, 'general', 'Reading/Writing', 'all', '📝 Gaya belajar reading/writing Anda sudah sesuai dengan format essay ini. Pertahankan!'),
('kinesthetic_feedback', 0, 100, 'general', 'Kinesthetic', 'all', '🏃 Sebagai kinesthetic learner, coba hubungkan konsep dengan contoh praktis dan aplikasi nyata.');
