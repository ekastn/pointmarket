-- VARK Learning Style Assessment Tables and Data

-- First, ensure we have the VARK questionnaire in the questionnaires table
INSERT INTO questionnaires (id, name, description, type) VALUES 
(3, 'VARK Learning Style Assessment', 'Assessment to determine Visual, Auditory, Reading/Writing, and Kinesthetic learning preferences', 'vark')
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), type = VALUES(type);

-- Create VARK answer options table
CREATE TABLE IF NOT EXISTS `vark_answer_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `option_letter` char(1) NOT NULL,
  `option_text` text NOT NULL,
  `learning_style` enum('Visual','Auditory','Reading','Kinesthetic') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`),
  FOREIGN KEY (`question_id`) REFERENCES `questionnaire_questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create VARK results table
CREATE TABLE IF NOT EXISTS `vark_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `visual_score` int(11) DEFAULT 0,
  `auditory_score` int(11) DEFAULT 0,
  `reading_score` int(11) DEFAULT 0,
  `kinesthetic_score` int(11) DEFAULT 0,
  `dominant_style` varchar(50) NOT NULL,
  `learning_preference` text,
  `answers` json,
  `completed_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert VARK questions
INSERT INTO questionnaire_questions (questionnaire_id, question_number, question_text, subscale) VALUES
(3, 1, 'Saya ingin mengoperasikan perangkat elektronik baru, saya akan:', 'learning_preference'),
(3, 2, 'Saya memilih restoran atau hotel berdasarkan:', 'learning_preference'),
(3, 3, 'Saya ingin membuat sesuatu sebagai hadiah untuk keluarga. Saya akan:', 'learning_preference'),
(3, 4, 'Saya telah menyelesaikan kompetisi atau ujian dan ingin tahu bagaimana saya bisa melakukannya. Saya akan:', 'learning_preference'),
(3, 5, 'Saya akan belajar menggunakan program komputer baru dengan:', 'learning_preference'),
(3, 6, 'Seorang pengajar sedang menjelaskan apa yang harus dipelajari dalam kursus baru. Paling saya inginkan mereka:', 'learning_preference'),
(3, 7, 'Saya ingin mengetahui pendapat seseorang tentang suatu produk. Saya akan:', 'learning_preference'),
(3, 8, 'Ketika saya belajar dari Internet, saya suka:', 'learning_preference'),
(3, 9, 'Jika saya ingin belajar cara memainkan alat musik baru, saya akan:', 'learning_preference'),
(3, 10, 'Saya lebih suka mengajar dengan memperhatikan presentasi yang memiliki:', 'learning_preference'),
(3, 11, 'Ketika memilih makanan dari menu yang tidak saya kenal, saya akan:', 'learning_preference'),
(3, 12, 'Saya harus memberikan arah kepada seseorang untuk pergi ke bandara, rumah sakit umum, atau stasiun. Saya akan:', 'learning_preference'),
(3, 13, 'Saya tidak yakin ejaan kata yang benar. Saya akan:', 'learning_preference'),
(3, 14, 'Saya ingin mempelajari tentang kota baru. Saya akan:', 'learning_preference'),
(3, 15, 'Saya ingin mempelajari keterampilan praktis baru. Saya akan:', 'learning_preference'),
(3, 16, 'Ketika saya belajar, saya suka:', 'learning_preference');

-- Insert VARK answer options for all 16 questions
-- Question 1
INSERT INTO vark_answer_options (question_id, option_letter, option_text, learning_style) VALUES
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 1), 'a', 'membaca instruksi terlebih dahulu', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 1), 'b', 'mendengarkan penjelasan dari seseorang yang telah menggunakannya', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 1), 'c', 'mencoba-coba dan mencari tahu cara kerjanya', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 1), 'd', 'mengikuti diagram atau gambar', 'Visual');

-- Question 2
INSERT INTO vark_answer_options (question_id, option_letter, option_text, learning_style) VALUES
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 2), 'a', 'rekomendasi dari teman', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 2), 'b', 'membaca ulasan online', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 2), 'c', 'melihat foto dan video', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 2), 'd', 'mengunjungi tempat itu terlebih dahulu', 'Kinesthetic');

-- Question 3
INSERT INTO vark_answer_options (question_id, option_letter, option_text, learning_style) VALUES
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 3), 'a', 'mencari ide di majalah atau online', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 3), 'b', 'membuat sesuatu dengan tangan saya', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 3), 'c', 'mencari petunjuk tertulis', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 3), 'd', 'bertanya kepada teman untuk saran', 'Auditory');

-- Question 4
INSERT INTO vark_answer_options (question_id, option_letter, option_text, learning_style) VALUES
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 4), 'a', 'menunggu sampai hasil diumumkan', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 4), 'b', 'memeriksa website untuk hasil', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 4), 'c', 'meminta seseorang untuk memberi tahu saya', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 4), 'd', 'melihat grafik atau diagram hasil', 'Visual');

-- Question 5
INSERT INTO vark_answer_options (question_id, option_letter, option_text, learning_style) VALUES
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 5), 'a', 'mengikuti instruksi tertulis yang disediakan', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 5), 'b', 'meminta seseorang untuk menunjukkan kepada saya', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 5), 'c', 'menggunakan bantuan dan tutorial', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 5), 'd', 'menelepon helpline atau bertanya kepada teman', 'Auditory');

-- Continue with remaining questions...
-- Question 6
INSERT INTO vark_answer_options (question_id, option_letter, option_text, learning_style) VALUES
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 6), 'a', 'menampilkan contoh bagaimana saya bisa menerapkan apa yang dipelajari', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 6), 'b', 'memberikan saya bahan tertulis untuk dibaca', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 6), 'c', 'menggunakan diagram, grafik, dan bagan', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 6), 'd', 'berbicara tentang apa yang akan dipelajari', 'Auditory');

-- Question 7
INSERT INTO vark_answer_options (question_id, option_letter, option_text, learning_style) VALUES
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 7), 'a', 'membaca ulasan online', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 7), 'b', 'mendiskusikannya dengan teman', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 7), 'c', 'mencoba atau menguji produk', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 7), 'd', 'melihat demonstrasi atau video', 'Visual');

-- Question 8
INSERT INTO vark_answer_options (question_id, option_letter, option_text, learning_style) VALUES
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 8), 'a', 'video dan animasi', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 8), 'b', 'simulasi dan permainan interaktif', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 8), 'c', 'artikel dan teks tertulis', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 8), 'd', 'podcast dan rekaman audio', 'Auditory');

-- Question 9
INSERT INTO vark_answer_options (question_id, option_letter, option_text, learning_style) VALUES
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 9), 'a', 'mendengarkan musik dan mencoba menirunya', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 9), 'b', 'mempraktikkan dengan instrumen', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 9), 'c', 'membaca tentang teknik bermain', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 9), 'd', 'menonton video tutorial', 'Visual');

-- Question 10
INSERT INTO vark_answer_options (question_id, option_letter, option_text, learning_style) VALUES
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 10), 'a', 'banyak diagram dan chart', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 10), 'b', 'diskusi kelompok dan tanya jawab', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 10), 'c', 'aktivitas praktik langsung', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 10), 'd', 'handout dan materi bacaan', 'Reading');

-- Question 11
INSERT INTO vark_answer_options (question_id, option_letter, option_text, learning_style) VALUES
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 11), 'a', 'melihat gambar makanan', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 11), 'b', 'mendengar deskripsi dari pelayan', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 11), 'c', 'membaca deskripsi detail di menu', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 11), 'd', 'memesan sesuatu yang terlihat atau tercium baik', 'Kinesthetic');

-- Question 12
INSERT INTO vark_answer_options (question_id, option_letter, option_text, learning_style) VALUES
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 12), 'a', 'menggambar peta atau diagram', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 12), 'b', 'menjelaskan secara lisan', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 12), 'c', 'menulis petunjuk jalan', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 12), 'd', 'pergi dengan mereka atau menunjukkan jalan', 'Kinesthetic');

-- Question 13
INSERT INTO vark_answer_options (question_id, option_letter, option_text, learning_style) VALUES
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 13), 'a', 'mencari di kamus', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 13), 'b', 'menuliskan kata dalam berbagai cara', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 13), 'c', 'menggunakan spell-checker komputer', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 13), 'd', 'bertanya kepada seseorang', 'Auditory');

-- Question 14
INSERT INTO vark_answer_options (question_id, option_letter, option_text, learning_style) VALUES
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 14), 'a', 'melihat peta dan foto', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 14), 'b', 'berbicara dengan orang yang pernah ke sana', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 14), 'c', 'membaca buku panduan wisata', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 14), 'd', 'merencanakan kegiatan untuk dilakukan', 'Kinesthetic');

-- Question 15
INSERT INTO vark_answer_options (question_id, option_letter, option_text, learning_style) VALUES
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 15), 'a', 'menonton demonstrasi video', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 15), 'b', 'mendengarkan penjelasan ahli', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 15), 'c', 'membaca manual atau buku panduan', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 15), 'd', 'berlatih dengan seseorang yang sudah bisa', 'Kinesthetic');

-- Question 16
INSERT INTO vark_answer_options (question_id, option_letter, option_text, learning_style) VALUES
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 16), 'a', 'menggunakan diagram dan chart', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 16), 'b', 'mendengarkan penjelasan', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 16), 'c', 'membaca dan membuat catatan', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 16), 'd', 'melakukan aktivitas praktik', 'Kinesthetic');
