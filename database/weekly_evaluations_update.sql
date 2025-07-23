-- Additional tables and updates for Weekly MSLQ/AMS Evaluations
-- Run this after the main pointmarket.sql

-- Add week tracking to questionnaire_results
ALTER TABLE `questionnaire_results` 
ADD COLUMN `week_number` INT NOT NULL DEFAULT 1,
ADD COLUMN `year` INT NOT NULL DEFAULT YEAR(NOW()),
ADD INDEX `idx_student_week` (`student_id`, `questionnaire_id`, `week_number`, `year`);

-- Table for weekly evaluation schedule and reminders
CREATE TABLE `weekly_evaluations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `questionnaire_id` int(11) NOT NULL,
  `week_number` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('pending','completed','overdue') DEFAULT 'pending',
  `reminder_sent` boolean DEFAULT FALSE,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_questionnaire_week` (`student_id`, `questionnaire_id`, `week_number`, `year`),
  KEY `student_id` (`student_id`),
  KEY `questionnaire_id` (`questionnaire_id`),
  KEY `due_date` (`due_date`),
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`questionnaire_id`) REFERENCES `questionnaires`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for questionnaire questions (MSLQ and AMS items)
CREATE TABLE `questionnaire_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `questionnaire_id` int(11) NOT NULL,
  `question_number` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `subscale` varchar(100) DEFAULT NULL,
  `reverse_scored` boolean DEFAULT FALSE,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `questionnaire_id` (`questionnaire_id`),
  FOREIGN KEY (`questionnaire_id`) REFERENCES `questionnaires`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert MSLQ questions (abbreviated version for demo)
INSERT INTO `questionnaire_questions` (`questionnaire_id`, `question_number`, `question_text`, `subscale`, `reverse_scored`) VALUES
-- MSLQ - Intrinsic Goal Orientation
(1, 1, 'Dalam sebuah kelas seperti ini, saya lebih suka materi pembelajaran yang benar-benar menantang sehingga saya dapat belajar hal-hal baru.', 'Intrinsic Goal Orientation', FALSE),
(1, 2, 'Dalam sebuah kelas seperti ini, saya lebih suka materi pembelajaran yang menggugah rasa ingin tahu saya, meskipun sulit untuk dipelajari.', 'Intrinsic Goal Orientation', FALSE),
(1, 3, 'Hal yang paling memuaskan bagi saya dalam kelas ini adalah mencoba memahami konten selengkap mungkin.', 'Intrinsic Goal Orientation', FALSE),
(1, 4, 'Ketika saya berkesempatan dalam kelas ini, saya memilih tugas yang dapat saya pelajari, bahkan jika itu tidak menjamin nilai yang baik.', 'Intrinsic Goal Orientation', FALSE),

-- MSLQ - Extrinsic Goal Orientation  
(1, 5, 'Mendapatkan nilai yang baik dalam kelas ini adalah hal yang paling memuaskan bagi saya saat ini.', 'Extrinsic Goal Orientation', FALSE),
(1, 6, 'Hal yang paling penting bagi saya sekarang adalah meningkatkan nilai rata-rata saya secara keseluruhan, jadi perhatian utama saya dalam kelas ini adalah mendapatkan nilai yang baik.', 'Extrinsic Goal Orientation', FALSE),
(1, 7, 'Jika saya dapat, saya ingin mendapat nilai yang lebih baik dalam kelas ini daripada kebanyakan siswa lain.', 'Extrinsic Goal Orientation', FALSE),
(1, 8, 'Saya ingin berbuat baik dalam kelas ini karena penting untuk menunjukkan kemampuan saya kepada keluarga, teman, atasan, atau orang lain.', 'Extrinsic Goal Orientation', FALSE),

-- MSLQ - Task Value
(1, 9, 'Saya pikir saya akan dapat menggunakan apa yang saya pelajari dalam kelas ini di kelas lain.', 'Task Value', FALSE),
(1, 10, 'Penting bagi saya untuk mempelajari materi dalam kelas ini.', 'Task Value', FALSE),
(1, 11, 'Saya sangat tertarik dengan bidang konten kelas ini.', 'Task Value', FALSE),
(1, 12, 'Saya pikir materi kelas ini berguna untuk dipelajari.', 'Task Value', FALSE),

-- MSLQ - Control of Learning Beliefs
(1, 13, 'Jika saya belajar dengan cara yang tepat, maka saya akan dapat mempelajari materi dalam kelas ini.', 'Control of Learning Beliefs', FALSE),
(1, 14, 'Terserah pada saya apakah saya mempelajari materi dengan baik dalam kelas ini atau tidak.', 'Control of Learning Beliefs', FALSE),
(1, 15, 'Jika saya mencoba cukup keras, maka saya akan memahami materi kelas.', 'Control of Learning Beliefs', FALSE),
(1, 16, 'Jika saya tidak mempelajari materi kelas dengan baik, itu karena saya tidak mencoba cukup keras.', 'Control of Learning Beliefs', FALSE),

-- MSLQ - Self-Efficacy for Learning and Performance
(1, 17, 'Saya yakin dapat memahami konsep yang paling sulit yang disajikan oleh instruktur dalam kelas ini.', 'Self-Efficacy for Learning and Performance', FALSE),
(1, 18, 'Saya yakin dapat memahami materi yang paling rumit yang disajikan dalam bacaan untuk kelas ini.', 'Self-Efficacy for Learning and Performance', FALSE),
(1, 19, 'Saya yakin dapat menguasai keterampilan yang diajarkan dalam kelas ini.', 'Self-Efficacy for Learning and Performance', FALSE),
(1, 20, 'Saya yakin dapat berbuat baik dalam tugas dan tes dalam kelas ini.', 'Self-Efficacy for Learning and Performance', FALSE);

-- Insert AMS questions (abbreviated version for demo)
INSERT INTO `questionnaire_questions` (`questionnaire_id`, `question_number`, `question_text`, `subscale`, `reverse_scored`) VALUES
-- AMS - Intrinsic Motivation
(2, 1, 'Karena saya merasakan kepuasan saat menemukan hal-hal baru yang tidak pernah saya lihat atau ketahui sebelumnya.', 'Intrinsic Motivation - To Know', FALSE),
(2, 2, 'Karena saya merasakan kepuasan saat membaca tentang berbagai topik menarik.', 'Intrinsic Motivation - To Know', FALSE),
(2, 3, 'Karena saya merasakan kepuasan saat saya merasakan diri saya benar-benar terlibat dalam apa yang saya lakukan.', 'Intrinsic Motivation - To Experience Stimulation', FALSE),
(2, 4, 'Karena saya merasakan kepuasan saat saya dapat berkomunikasi dengan baik dalam bahasa Inggris.', 'Intrinsic Motivation - To Experience Stimulation', FALSE),

-- AMS - Extrinsic Motivation
(2, 5, 'Karena menurut saya sekolah menengah akan membantu saya membuat pilihan karir yang lebih baik.', 'Extrinsic Motivation - Identified', FALSE),
(2, 6, 'Karena akan membantu saya membuat pilihan yang lebih baik mengenai orientasi karir saya.', 'Extrinsic Motivation - Identified', FALSE),
(2, 7, 'Karena saya ingin menunjukkan kepada diri saya sendiri bahwa saya dapat berhasil dalam studi saya.', 'Extrinsic Motivation - Introjected', FALSE),
(2, 8, 'Karena saya ingin menunjukkan kepada diri saya sendiri bahwa saya adalah orang yang cerdas.', 'Extrinsic Motivation - Introjected', FALSE),

-- AMS - Amotivation
(2, 9, 'Saya tidak tahu; saya tidak dapat memahami apa yang saya lakukan di sekolah.', 'Amotivation', FALSE),
(2, 10, 'Jujur, saya tidak tahu; saya benar-benar merasa bahwa saya membuang-buang waktu di sekolah.', 'Amotivation', FALSE);

-- Generate weekly evaluations for current students (next 4 weeks)
-- This will be done via PHP script for better date management

-- Insert VARK questionnaire data
INSERT INTO `questionnaires` (`id`, `name`, `description`, `type`, `total_questions`, `status`) VALUES
(3, 'VARK Learning Style Assessment', 'Kuesioner untuk mendeteksi gaya belajar Visual, Auditory, Reading/Writing, dan Kinesthetic', 'vark', 16, 'active')
ON DUPLICATE KEY UPDATE 
    name = VALUES(name),
    description = VALUES(description),
    total_questions = VALUES(total_questions);

-- Insert VARK questions (16 standard questions)
INSERT INTO `questionnaire_questions` (`questionnaire_id`, `question_number`, `question_text`, `subscale`, `reverse_scored`) VALUES
-- VARK Questions
(3, 1, 'Ketika saya ingin mempelajari sesuatu yang baru, saya lebih suka:', 'Learning Preference', FALSE),
(3, 2, 'Ketika saya mengikuti petunjuk untuk menggunakan peralatan baru, saya lebih suka:', 'Learning Preference', FALSE),
(3, 3, 'Ketika saya ingin mengingat nomor telepon, saya:', 'Memory Strategy', FALSE),
(3, 4, 'Ketika saya menjelaskan sesuatu kepada orang lain, saya cenderung:', 'Communication Style', FALSE),
(3, 5, 'Ketika saya belajar tentang tempat baru, saya lebih suka:', 'Information Processing', FALSE),
(3, 6, 'Ketika saya memasak hidangan baru, saya lebih suka:', 'Task Approach', FALSE),
(3, 7, 'Ketika saya memilih liburan, hal yang paling penting bagi saya adalah:', 'Decision Making', FALSE),
(3, 8, 'Ketika saya membeli produk baru, saya lebih suka:', 'Information Gathering', FALSE),
(3, 9, 'Ketika saya belajar keterampilan baru untuk olahraga atau hobi, saya lebih suka:', 'Skill Learning', FALSE),
(3, 10, 'Ketika saya memilih makanan di restoran atau kafe, saya cenderung:', 'Choice Making', FALSE),
(3, 11, 'Ketika saya mendengarkan musik, hal yang paling saya perhatikan adalah:', 'Attention Focus', FALSE),
(3, 12, 'Ketika saya berkonsentrasi, saya paling terganggu oleh:', 'Concentration', FALSE),
(3, 13, 'Ketika saya marah, saya cenderung:', 'Emotional Expression', FALSE),
(3, 14, 'Ketika saya menghadiri pernikahan atau pesta, hal yang paling saya ingat adalah:', 'Memory Formation', FALSE),
(3, 15, 'Ketika saya melihat suatu daerah untuk pertama kalinya, saya:', 'Spatial Processing', FALSE),
(3, 16, 'Ketika saya belajar bahasa asing, saya lebih suka:', 'Language Learning', FALSE);

-- Create VARK answer options table
CREATE TABLE IF NOT EXISTS `vark_answer_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `option_letter` char(1) NOT NULL,
  `option_text` text NOT NULL,
  `learning_style` enum('Visual','Auditory','Reading','Kinesthetic') NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`),
  FOREIGN KEY (`question_id`) REFERENCES `questionnaire_questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert VARK answer options
INSERT INTO `vark_answer_options` (`question_id`, `option_letter`, `option_text`, `learning_style`) VALUES
-- Question 1: Ketika saya ingin mempelajari sesuatu yang baru, saya lebih suka:
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 1), 'a', 'Menonton video atau melihat demonstrasi', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 1), 'b', 'Mendengarkan penjelasan dari ahli', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 1), 'c', 'Membaca buku atau artikel tentang topik tersebut', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 1), 'd', 'Mencoba langsung dan mempraktikkannya', 'Kinesthetic'),

-- Question 2: Ketika saya mengikuti petunjuk untuk menggunakan peralatan baru, saya lebih suka:
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 2), 'a', 'Melihat diagram atau gambar instruksi', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 2), 'b', 'Meminta seseorang menjelaskan secara lisan', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 2), 'c', 'Membaca manual atau petunjuk tertulis', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 2), 'd', 'Mencoba menggunakan peralatan sambil belajar', 'Kinesthetic'),

-- Question 3: Ketika saya ingin mengingat nomor telepon, saya:
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 3), 'a', 'Membayangkan angka-angka tersebut', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 3), 'b', 'Mengucapkan angka tersebut berulang-ulang', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 3), 'c', 'Menuliskannya beberapa kali', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 3), 'd', 'Menekan tombol-tombol angka sambil mengingat', 'Kinesthetic'),

-- Question 4: Ketika saya menjelaskan sesuatu kepada orang lain, saya cenderung:
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 4), 'a', 'Menggambar diagram atau membuat sketsa', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 4), 'b', 'Menjelaskan secara verbal dengan detail', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 4), 'c', 'Menulis poin-poin penting', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 4), 'd', 'Memberikan contoh atau demonstrasi praktis', 'Kinesthetic'),

-- Question 5: Ketika saya belajar tentang tempat baru, saya lebih suka:
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 5), 'a', 'Melihat foto-foto atau peta tempat tersebut', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 5), 'b', 'Mendengar cerita orang tentang tempat itu', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 5), 'c', 'Membaca panduan wisata atau artikel', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 5), 'd', 'Langsung mengunjungi dan menjelajahi tempat itu', 'Kinesthetic'),

-- Question 6: Ketika saya memasak hidangan baru, saya lebih suka:
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 6), 'a', 'Menonton video tutorial memasak', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 6), 'b', 'Meminta seseorang menjelaskan langkah-langkahnya', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 6), 'c', 'Mengikuti resep tertulis langkah demi langkah', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 6), 'd', 'Mencoba memasak sambil mengira-ngira takaran', 'Kinesthetic'),

-- Question 7: Ketika saya memilih liburan, hal yang paling penting bagi saya adalah:
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 7), 'a', 'Pemandangan yang indah untuk dilihat', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 7), 'b', 'Tempat yang tenang untuk bersantai', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 7), 'c', 'Tempat dengan sejarah menarik untuk dipelajari', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 7), 'd', 'Aktivitas fisik dan petualangan', 'Kinesthetic'),

-- Question 8: Ketika saya membeli produk baru, saya lebih suka:
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 8), 'a', 'Melihat produk secara langsung dan tampilannya', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 8), 'b', 'Mendengar review atau rekomendasi orang lain', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 8), 'c', 'Membaca spesifikasi dan review online', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 8), 'd', 'Mencoba atau memegang produk terlebih dahulu', 'Kinesthetic'),

-- Question 9: Ketika saya belajar keterampilan baru untuk olahraga atau hobi, saya lebih suka:
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 9), 'a', 'Menonton demonstrasi atau video tutorial', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 9), 'b', 'Mendengarkan instruksi verbal dari pelatih', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 9), 'c', 'Membaca buku panduan atau manual', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 9), 'd', 'Langsung praktik dengan trial and error', 'Kinesthetic'),

-- Question 10: Ketika saya memilih makanan di restoran atau kafe, saya cenderung:
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 10), 'a', 'Melihat foto makanan di menu', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 10), 'b', 'Bertanya kepada pelayan tentang rekomendasi', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 10), 'c', 'Membaca deskripsi menu dengan detail', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 10), 'd', 'Memilih berdasarkan aroma atau makanan yang terlihat', 'Kinesthetic'),

-- Question 11: Ketika saya mendengarkan musik, hal yang paling saya perhatikan adalah:
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 11), 'a', 'Video musik atau visualisasi', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 11), 'b', 'Melodi dan harmoni musik', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 11), 'c', 'Lirik atau kata-kata dalam lagu', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 11), 'd', 'Ritme yang membuat saya ingin bergerak', 'Kinesthetic'),

-- Question 12: Ketika saya berkonsentrasi, saya paling terganggu oleh:
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 12), 'a', 'Gerakan atau objek yang bergerak di sekitar saya', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 12), 'b', 'Suara atau kebisingan di sekitar', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 12), 'c', 'Teks atau tulisan yang tidak rapi', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 12), 'd', 'Posisi duduk yang tidak nyaman', 'Kinesthetic'),

-- Question 13: Ketika saya marah, saya cenderung:
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 13), 'a', 'Diam dan memberikan tatapan tajam', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 13), 'b', 'Mengungkapkan perasaan secara verbal', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 13), 'c', 'Menulis perasaan saya di diary atau surat', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 13), 'd', 'Pergi keluar atau melakukan aktivitas fisik', 'Kinesthetic'),

-- Question 14: Ketika saya menghadiri pernikahan atau pesta, hal yang paling saya ingat adalah:
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 14), 'a', 'Dekorasi dan penampilan tempat acara', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 14), 'b', 'Musik atau percakapan yang saya dengar', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 14), 'c', 'Nama-nama orang yang hadir atau detail acara', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 14), 'd', 'Tarian atau aktivitas yang saya lakukan', 'Kinesthetic'),

-- Question 15: Ketika saya melihat suatu daerah untuk pertama kalinya, saya:
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 15), 'a', 'Mengingat landmark atau bangunan yang menonjol', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 15), 'b', 'Mengingat nama jalan atau petunjuk arah verbal', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 15), 'c', 'Mencatat atau mengingat alamat dan petunjuk tertulis', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 15), 'd', 'Mengingat rute berdasarkan gerakan dan arah yang saya ambil', 'Kinesthetic'),

-- Question 16: Ketika saya belajar bahasa asing, saya lebih suka:
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 16), 'a', 'Melihat gambar dan flash cards visual', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 16), 'b', 'Mendengarkan audio dan berbicara dengan native speaker', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 16), 'c', 'Membaca teks dan mempelajari tata bahasa', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 16), 'd', 'Bermain games atau role-play dalam bahasa tersebut', 'Kinesthetic');

-- Create VARK results table
CREATE TABLE IF NOT EXISTS `vark_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `visual_score` int(11) DEFAULT 0,
  `auditory_score` int(11) DEFAULT 0,
  `reading_score` int(11) DEFAULT 0,
  `kinesthetic_score` int(11) DEFAULT 0,
  `dominant_style` varchar(50) DEFAULT NULL,
  `learning_preference` varchar(100) DEFAULT NULL,
  `answers` JSON DEFAULT NULL,
  `completed_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `week_number` int(11) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `completed_at` (`completed_at`),
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add activity log table for tracking system activities
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
