use pointmarket_meta;

-- Membuat tabel jika belum ada (untuk kelengkapan)
CREATE TABLE IF NOT EXISTS `nlp_lexicon` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `keyword` VARCHAR(255) NOT NULL,
  `style` ENUM('Visual', 'Aural', 'Read/Write', 'Kinesthetic') NOT NULL,
  `weight` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_keyword_style` (`keyword`, `style`)
);

-- Mengosongkan tabel sebelum memasukkan data baru (opsional)
TRUNCATE TABLE `nlp_lexicon`;


-- =================================================================
-- KATA KUNCI VISUAL
-- (Fokus pada gambar, ruang, warna, pola, dan pemetaan)
-- =================================================================
INSERT INTO `nlp_lexicon` (keyword, style, weight) VALUES
-- Bobot 3 (Sinyal Sangat Kuat)
('visualisasi', 'Visual', 3),
('diagram', 'Visual', 3),
('peta konsep', 'Visual', 3),
('peta pikiran', 'Visual', 3),
('alur kerja visual', 'Visual', 3),
('infografis', 'Visual', 3),
-- Bobot 2 (Sinyal Kuat)
('skema', 'Visual', 2),
('grafik', 'Visual', 2),
('gambar', 'Visual', 2),
('ilustrasi', 'Visual', 2),
('bagan', 'Visual', 2),
('pola', 'Visual', 2),
('desain', 'Visual', 2),
('layout', 'Visual', 2),
('membayangkan', 'Visual', 2),
-- Bobot 1 (Sinyal Pendukung)
('lihat', 'Visual', 1),
('tampak', 'Visual', 1),
('warna', 'Visual', 1),
('bentuk', 'Visual', 1),
('menunjukkan', 'Visual', 1),
('kerangka', 'Visual', 1),
('peta', 'Visual', 1);


-- =================================================================
-- KATA KUNCI AURAL
-- (Fokus pada suara, pendengaran, berbicara, dan diskusi)
-- =================================================================
INSERT INTO `nlp_lexicon` (keyword, style, weight) VALUES
-- Bobot 3 (Sinyal Sangat Kuat)
('diskusi kelompok', 'Aural', 3),
('jelas ulang', 'Aural', 3),
('tanya jawab', 'Aural', 3),
('presentasi lisan', 'Aural', 3),
('menjelaskan ke orang lain', 'Aural', 3),
-- Bobot 2 (Sinyal Kuat)
('ceramah', 'Aural', 2),
('diskusi', 'Aural', 2),
('dengar', 'Aural', 2),
('bicara', 'Aural', 2),
('podcast', 'Aural', 2),
('wawancara', 'Aural', 2),
('debat', 'Aural', 2),
('mengucapkan', 'Aural', 2),
-- Bobot 1 (Sinyal Pendukung)
('suara', 'Aural', 1),
('ngobrol', 'Aural', 1),
('nada', 'Aural', 1),
('ritme', 'Aural', 1),
('rekaman', 'Aural', 1);


-- =================================================================
-- KATA KUNCI READ/WRITE
-- (Fokus pada teks, daftar, logika, dan dokumentasi)
-- =================================================================
INSERT INTO `nlp_lexicon` (keyword, style, weight) VALUES
-- Bobot 3 (Sinyal Sangat Kuat)
('buat ringkas', 'Read/Write', 3),
('studi literatur', 'Read/Write', 3),
('poin-poin penting', 'Read/Write', 3),
('mencatat', 'Read/Write', 3),
-- Bobot 2 (Sinyal Kuat)
('definisi', 'Read/Write', 2),
('artikel', 'Read/Write', 2),
('baca', 'Read/Write', 2),
('tulis', 'Read/Write', 2),
('analisis', 'Read/Write', 2),
('prosedur', 'Read/Write', 2),
('konsep', 'Read/Write', 2),
('teori', 'Read/Write', 2),
('kutipan', 'Read/Write', 2),
('jurnal', 'Read/Write', 2),
-- Bobot 1 (Sinyal Pendukung)
('buku', 'Read/Write', 1),
('daftar', 'Read/Write', 1),
('teks', 'Read/Write', 1),
('manual', 'Read/Write', 1),
('referensi', 'Read/Write', 1),
('esai', 'Read/Write', 1);


-- =================================================================
-- KATA KUNCI KINESTHETIC
-- (Fokus pada tindakan, pengalaman, aplikasi nyata, dan gerakan)
-- =================================================================
INSERT INTO `nlp_lexicon` (keyword, style, weight) VALUES
-- Bobot 3 (Sinyal Sangat Kuat)
('contoh nyata', 'Kinesthetic', 3),
('praktik langsung', 'Kinesthetic', 3),
('eksperimen', 'Kinesthetic', 3),
('studi kasus', 'Kinesthetic', 3),
('simulasi interaktif', 'Kinesthetic', 3),
('belajar sambil melakukan', 'Kinesthetic', 3),
-- Bobot 2 (Sinyal Kuat)
('simulasi', 'Kinesthetic', 2),
('laku', 'Kinesthetic', 2),
('coba', 'Kinesthetic', 2),
('bangun', 'Kinesthetic', 2),
('pengalaman', 'Kinesthetic', 2),
('proyek', 'Kinesthetic', 2),
('penerapan', 'Kinesthetic', 2),
('merakit', 'Kinesthetic', 2),
-- Bobot 1 (Sinyal Pendukung)
('gerak', 'Kinesthetic', 1),
('aplikasi', 'Kinesthetic', 1),
('menyentuh', 'Kinesthetic', 1),
('merasakan', 'Kinesthetic', 1),
('kasus', 'Kinesthetic', 1);
