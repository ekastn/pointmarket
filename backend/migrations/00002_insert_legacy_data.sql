-- +goose Up
-- SQL in section 'Up' is executed when this migration is applied

-- Data from pointmarket.sql
INSERT INTO `users` (`username`, `password`, `name`, `email`, `role`) VALUES
('andi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Andi Pratama', 'andi@student.com', 'siswa'),
('budi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso', 'budi@student.com', 'siswa'),
('citra', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Citra Dewi', 'citra@student.com', 'siswa'),
('sarah', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Wulandari', 'sarah@teacher.com', 'guru'),
('ahmad', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ahmad Rahman', 'ahmad@teacher.com', 'guru'),
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@pointmarket.com', 'admin');

INSERT INTO `questionnaires` (`type`, `name`, `description`, `total_questions`) VALUES
('mslq', 'Motivated Strategies for Learning Questionnaire', 'Kuesioner untuk mengukur motivasi dan strategi belajar siswa', 81),
('ams', 'Academic Motivation Scale', 'Skala untuk mengukur motivasi akademik siswa', 28);

INSERT INTO `assignments` (`title`, `description`, `subject`, `teacher_id`, `points`, `due_date`) VALUES
('Tugas Matematika - Aljabar Linear', 'Selesaikan soal-soal aljabar linear pada buku halaman 45-50', 'Matematika', 4, 100, '2025-07-15 23:59:59'),
('Essay Bahasa Indonesia', 'Tulis essay tentang "Pentingnya Literasi Digital" minimal 500 kata', 'Bahasa Indonesia', 4, 150, '2025-07-20 23:59:59'),
('Laporan Praktikum Fisika', 'Buat laporan hasil praktikum tentang Hukum Newton', 'Fisika', 5, 200, '2025-07-25 23:59:59');

INSERT INTO `quizzes` (`title`, `description`, `subject`, `teacher_id`, `points`, `duration`) VALUES
('Quiz Matematika - Trigonometri', 'Quiz pilihan ganda tentang konsep dasar trigonometri', 'Matematika', 4, 50, 30),
('Quiz Bahasa Inggris - Grammar', 'Quiz tentang tenses dan struktur kalimat', 'Bahasa Inggris', 5, 75, 45);

INSERT INTO `materials` (`title`, `description`, `subject`, `teacher_id`, `file_type`) VALUES
('Modul Matematika - Kalkulus', 'Materi pembelajaran kalkulus dasar', 'Matematika', 4, 'pdf'),
('Video Tutorial - Grammar', 'Video pembelajaran tentang grammar bahasa Inggris', 'Bahasa Inggris', 5, 'video'),
('Slide Presentasi - Fisika Quantum', 'Presentasi tentang konsep dasar fisika quantum', 'Fisika', 5, 'presentation');

INSERT INTO `student_assignments` (`student_id`, `assignment_id`, `score`, `status`, `submitted_at`) VALUES
(1, 1, 85, 'completed', '2025-07-10 14:30:00'),
(1, 2, 90, 'completed', '2025-07-12 16:45:00'),
(2, 1, 75, 'completed', '2025-07-11 10:20:00'),
(3, 1, 95, 'completed', '2025-07-09 20:15:00');

INSERT INTO `student_quiz` (`student_id`, `quiz_id`, `score`, `status`, `submitted_at`) VALUES
(1, 1, 80, 'completed', '2025-07-08 09:30:00'),
(1, 2, 85, 'completed', '2025-07-09 11:15:00'),
(2, 1, 70, 'completed', '2025-07-08 10:45:00');

-- Data from nlp-schema.sql
INSERT INTO `nlp_keywords` (`context`, `keyword`, `weight`, `category`) VALUES
('matematik', 'rumus', 1.00, 'core'),
('matematik', 'perhitungan', 1.00, 'core'),
('matematik', 'angka', 0.80, 'basic'),
('matematik', 'operasi', 0.90, 'core'),
('matematik', 'hasil', 0.70, 'basic'),
('matematik', 'metode', 0.80, 'advanced'),
('matematik', 'langkah', 0.75, 'basic'),
('matematik', 'solusi', 0.85, 'core'),
('fisika', 'gaya', 1.00, 'core'),
('fisika', 'gerak', 1.00, 'core'),
('fisika', 'energi', 1.00, 'core'),
('fisika', 'hukum', 0.90, 'core'),
('fisika', 'rumus', 0.80, 'basic'),
('fisika', 'percepatan', 0.90, 'core'),
('fisika', 'kecepatan', 0.85, 'basic'),
('fisika', 'massa', 0.80, 'basic'),
('kimia', 'unsur', 1.00, 'core'),
('kimia', 'reaksi', 1.00, 'core'),
('kimia', 'molekul', 1.00, 'core'),
('kimia', 'senyawa', 0.90, 'core'),
('kimia', 'atom', 0.80, 'basic'),
('kimia', 'ikatan', 0.90, 'core'),
('kimia', 'elektron', 0.85, 'advanced'),
('kimia', 'ion', 0.80, 'basic'),
('biologi', 'sel', 1.00, 'core'),
('biologi', 'organisme', 1.00, 'core'),
('biologi', 'protein', 0.90, 'core'),
('biologi', 'gen', 0.80, 'basic'),
('biologi', 'evolusi', 0.90, 'core'),
('biologi', 'ekosistem', 0.80, 'basic'),
('biologi', 'DNA', 0.85, 'core'),
('biologi', 'kromosom', 0.80, 'basic'),
('assignment', 'analisis', 1.00, 'core'),
('assignment', 'konsep', 1.00, 'core'),
('assignment', 'penjelasan', 0.90, 'core'),
('assignment', 'contoh', 0.80, 'basic'),
('assignment', 'kesimpulan', 0.90, 'core'),
('assignment', 'argumen', 0.80, 'advanced'),
('assignment', 'evaluasi', 0.85, 'advanced'),
('assignment', 'implementasi', 0.80, 'advanced');

INSERT INTO `nlp_feedback_templates` (`template_name`, `score_range_min`, `score_range_max`, `component`, `vark_style`, `mslq_profile`, `feedback_text`) VALUES
('grammar_low', 0, 50, 'grammar', 'all', 'all', '⚠️ Terdapat beberapa kesalahan tata bahasa. Periksa kembali penggunaan kata hubung dan tanda baca.'),
('grammar_medium', 51, 75, 'grammar', 'all', 'all', '📝 Tata bahasa cukup baik, namun masih bisa diperbaiki pada beberapa bagian.'),
('grammar_high', 76, 100, 'grammar', 'all', 'all', '✅ Tata bahasa sudah sangat baik!'),
('keyword_low', 0, 50, 'keyword', 'all', 'all', '📝 Coba gunakan lebih banyak kata kunci yang relevan dengan topik.'),
('keyword_medium', 51, 75, 'keyword', 'all', 'all', '🎯 Penggunaan kata kunci sudah cukup baik, bisa ditingkatkan lagi.'),
('keyword_high', 76, 100, 'keyword', 'all', 'all', '🎯 Penggunaan kata kunci sudah tepat dan relevan!'),
('structure_low', 0, 50, 'structure', 'all', 'all', '🔄 Struktur tulisan bisa diperbaiki dengan menggunakan kata penghubung dan numbering.'),
('structure_medium', 51, 75, 'structure', 'all', 'all', '📊 Struktur tulisan sudah cukup terorganisir, bisa lebih sistematis.'),
('structure_high', 76, 100, 'structure', 'all', 'all', '📊 Struktur tulisan sudah terorganisir dengan baik!'),
('visual_feedback', 0, 100, 'general', 'Visual', 'all', '👁️ Sebagai visual learner, coba tambahkan deskripsi visual atau diagram dalam jawaban Anda.'),
('auditory_feedback', 0, 100, 'general', 'Auditory', 'all', '🎵 Sebagai auditory learner, coba jelaskan konsep dengan kata-kata yang lebih deskriptif.'),
('reading_feedback', 0, 100, 'general', 'Reading/Writing', 'all', '📝 Gaya belajar reading/writing Anda sudah sesuai dengan format essay ini. Pertahankan!'),
('kinesthetic_feedback', 0, 100, 'general', 'Kinesthetic', 'all', '🏃 Sebagai kinesthetic learner, coba hubungkan konsep dengan contoh praktis dan aplikasi nyata.');

-- Data from vark_data.sql
INSERT INTO questionnaires (id, name, description, type, total_questions) VALUES 
(3, 'VARK Learning Style Assessment', 'Assessment to determine Visual, Auditory, Reading/Writing, and Kinesthetic learning preferences', 'vark', 16)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), type = VALUES(type), total_questions = VALUES(total_questions);

INSERT INTO questionnaire_questions (questionnaire_id, question_number, question_text, subscale) VALUES
(3, 1, 'Ketika saya ingin mempelajari sesuatu yang baru, saya lebih suka:', 'Learning Preference'),
(3, 2, 'Ketika saya mengikuti petunjuk untuk menggunakan peralatan baru, saya lebih suka:', 'Learning Preference'),
(3, 3, 'Ketika saya ingin mengingat nomor telepon, saya:', 'Memory Strategy'),
(3, 4, 'Ketika saya menjelaskan sesuatu kepada orang lain, saya cenderung:', 'Communication Style'),
(3, 5, 'Ketika saya belajar tentang tempat baru, saya lebih suka:', 'Information Processing'),
(3, 6, 'Ketika saya memasak hidangan baru, saya lebih suka:', 'Task Approach'),
(3, 7, 'Ketika saya memilih liburan, hal yang paling penting bagi saya adalah:', 'Decision Making'),
(3, 8, 'Ketika saya membeli produk baru, saya lebih suka:', 'Information Gathering'),
(3, 9, 'Ketika saya belajar keterampilan baru untuk olahraga atau hobi, saya lebih suka:', 'Skill Learning'),
(3, 10, 'Ketika saya memilih makanan di restoran atau kafe, saya cenderung:', 'Choice Making'),
(3, 11, 'Ketika saya mendengarkan musik, hal yang paling saya perhatikan adalah:', 'Attention Focus'),
(3, 12, 'Ketika saya berkonsentrasi, saya paling terganggu oleh:', 'Concentration'),
(3, 13, 'Ketika saya marah, saya cenderung:', 'Emotional Expression'),
(3, 14, 'Ketika saya menghadiri pernikahan atau pesta, hal yang paling saya ingat adalah:', 'Memory Formation'),
(3, 15, 'Ketika saya melihat suatu daerah untuk pertama kalinya, saya:', 'Spatial Processing'),
(3, 16, 'Ketika saya belajar bahasa asing, saya lebih suka:', 'Language Learning');

INSERT INTO vark_answer_options (question_id, option_letter, option_text, learning_style) VALUES
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 1), 'a', 'Menonton video atau melihat demonstrasi', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 1), 'b', 'Mendengarkan penjelasan dari ahli', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 1), 'c', 'Membaca buku atau artikel tentang topik tersebut', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 1), 'd', 'Mencoba langsung dan mempraktikkannya', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 2), 'a', 'Melihat diagram atau gambar instruksi', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 2), 'b', 'Meminta seseorang menjelaskan secara lisan', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 2), 'c', 'Membaca manual atau petunjuk tertulis', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 2), 'd', 'Mencoba menggunakan peralatan sambil belajar', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 3), 'a', 'Membayangkan angka-angka tersebut', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 3), 'b', 'Mengucapkan angka tersebut berulang-ulang', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 3), 'c', 'Menuliskannya beberapa kali', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 3), 'd', 'Menekan tombol-tombol angka sambil mengingat', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 4), 'a', 'Menggambar diagram atau membuat sketsa', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 4), 'b', 'Menjelaskan secara verbal dengan detail', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 4), 'c', 'Menulis poin-poin penting', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 4), 'd', 'Memberikan contoh atau demonstrasi praktis', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 5), 'a', 'Melihat foto-foto atau peta tempat tersebut', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 5), 'b', 'Mendengar cerita orang tentang tempat itu', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 5), 'c', 'Membaca panduan wisata atau artikel', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 5), 'd', 'Langsung mengunjungi dan menjelajahi tempat itu', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 6), 'a', 'Menonton video tutorial memasak', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 6), 'b', 'Meminta seseorang menjelaskan langkah-langkahnya', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 6), 'c', 'Mengikuti resep tertulis langkah demi langkah', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 6), 'd', 'Mencoba memasak sambil mengira-ngira takaran', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 7), 'a', 'Pemandangan yang indah untuk dilihat', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 7), 'b', 'Tempat yang tenang untuk bersantai', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 7), 'c', 'Tempat dengan sejarah menarik untuk dipelajari', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 7), 'd', 'Aktivitas fisik dan petualangan', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 8), 'a', 'Melihat produk secara langsung dan tampilannya', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 8), 'b', 'Mendengar review atau rekomendasi orang lain', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 8), 'c', 'Membaca spesifikasi dan review online', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 8), 'd', 'Mencoba atau memegang produk terlebih dahulu', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 9), 'a', 'Menonton demonstrasi video atau tutorial', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 9), 'b', 'Mendengarkan instruksi verbal dari pelatih', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 9), 'c', 'Membaca buku panduan atau manual', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 9), 'd', 'Langsung praktik dengan trial and error', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 10), 'a', 'Melihat foto makanan di menu', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 10), 'b', 'Bertanya kepada pelayan tentang rekomendasi', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 10), 'c', 'Membaca deskripsi menu dengan detail', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 10), 'd', 'Memilih berdasarkan aroma atau makanan yang terlihat', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 11), 'a', 'Video musik atau visualisasi', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 11), 'b', 'Melodi dan harmoni musik', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 11), 'c', 'Lirik atau kata-kata dalam lagu', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 11), 'd', 'Ritme yang membuat saya ingin bergerak', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 12), 'a', 'Gerakan atau objek yang bergerak di sekitar saya', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 12), 'b', 'Suara atau kebisingan di sekitar', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 12), 'c', 'Teks atau tulisan yang tidak rapi', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 12), 'd', 'Posisi duduk yang tidak nyaman', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 13), 'a', 'Diam dan memberikan tatapan tajam', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 13), 'b', 'Mengungkapkan perasaan secara verbal', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 13), 'c', 'Menulis perasaan saya di diary atau surat', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 13), 'd', 'Pergi keluar atau melakukan aktivitas fisik', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 14), 'a', 'Dekorasi dan penampilan tempat acara', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 14), 'b', 'Musik atau percakapan yang saya dengar', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 14), 'c', 'Nama-nama orang yang hadir atau detail acara', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 14), 'd', 'Tarian atau aktivitas yang saya lakukan', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 15), 'a', 'Mengingat landmark atau bangunan yang menonjol', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 15), 'b', 'Mengingat nama jalan atau petunjuk arah verbal', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 15), 'c', 'Mencatat atau mengingat alamat dan petunjuk tertulis', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 15), 'd', 'Mengingat rute berdasarkan gerakan dan arah yang saya ambil', 'Kinesthetic'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 16), 'a', 'Melihat gambar dan flash cards visual', 'Visual'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 16), 'b', 'Mendengarkan audio dan berbicara dengan native speaker', 'Auditory'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 16), 'c', 'Membaca teks dan mempelajari tata bahasa', 'Reading'),
((SELECT id FROM questionnaire_questions WHERE questionnaire_id = 3 AND question_number = 16), 'd', 'Bermain games atau role-play dalam bahasa tersebut', 'Kinesthetic');

-- Data from weekly_evaluations_update.sql
INSERT INTO `questionnaire_questions` (`questionnaire_id`, `question_number`, `question_text`, `subscale`, `reverse_scored`) VALUES
(1, 1, 'Dalam sebuah kelas seperti ini, saya lebih suka materi pembelajaran yang benar-benar menantang sehingga saya dapat belajar hal-hal baru.', 'Intrinsic Goal Orientation', FALSE),
(1, 2, 'Dalam sebuah kelas seperti ini, saya lebih suka materi pembelajaran yang menggugah rasa ingin tahu saya, meskipun sulit untuk dipelajari.', 'Intrinsic Goal Orientation', FALSE),
(1, 3, 'Hal yang paling memuaskan bagi saya dalam kelas ini adalah mencoba memahami konten selengkap mungkin.', 'Intrinsic Goal Orientation', FALSE),
(1, 4, 'Ketika saya berkesempatan dalam kelas ini, saya memilih tugas yang dapat saya pelajari, bahkan jika itu tidak menjamin nilai yang baik.', 'Intrinsic Goal Orientation', FALSE),
(1, 5, 'Mendapatkan nilai yang baik dalam kelas ini adalah hal yang paling memuaskan bagi saya saat ini.', 'Extrinsic Goal Orientation', FALSE),
(1, 6, 'Hal yang paling penting bagi saya sekarang adalah meningkatkan nilai rata-rata saya secara keseluruhan, jadi perhatian utama saya dalam kelas ini adalah mendapatkan nilai yang baik.', 'Extrinsic Goal Orientation', FALSE),
(1, 7, 'Jika saya dapat, saya ingin mendapat nilai yang lebih baik dalam kelas ini daripada kebanyakan siswa lain.', 'Extrinsic Goal Orientation', FALSE),
(1, 8, 'Saya ingin berbuat baik dalam kelas ini karena penting untuk menunjukkan kemampuan saya kepada keluarga, teman, atasan, atau orang lain.', 'Extrinsic Goal Orientation', FALSE),
(1, 9, 'Saya pikir saya akan dapat menggunakan apa yang saya pelajari dalam kelas ini di kelas lain.', 'Task Value', FALSE),
(1, 10, 'Penting bagi saya untuk mempelajari materi dalam kelas ini.', 'Task Value', FALSE),
(1, 11, 'Saya sangat tertarik dengan bidang konten kelas ini.', 'Task Value', FALSE),
(1, 12, 'Saya pikir materi kelas ini berguna untuk dipelajari.', 'Task Value', FALSE),
(1, 13, 'Jika saya belajar dengan cara yang tepat, maka saya akan dapat mempelajari materi dalam kelas ini.', 'Control of Learning Beliefs', FALSE),
(1, 14, 'Terserah pada saya apakah saya mempelajari materi dengan baik dalam kelas ini atau tidak.', 'Control of Learning Beliefs', FALSE),
(1, 15, 'Jika saya mencoba cukup keras, maka saya akan memahami materi kelas.', 'Control of Learning Beliefs', FALSE),
(1, 16, 'Jika saya tidak mempelajari materi kelas dengan baik, itu karena saya tidak mencoba cukup keras.', 'Control of Learning Beliefs', FALSE),
(1, 17, 'Saya yakin dapat memahami konsep yang paling sulit yang disajikan oleh instruktur dalam kelas ini.', 'Self-Efficacy for Learning and Performance', FALSE),
(1, 18, 'Saya yakin dapat memahami materi yang paling rumit yang disajikan dalam bacaan untuk kelas ini.', 'Self-Efficacy for Learning and Performance', FALSE),
(1, 19, 'Saya yakin dapat menguasai keterampilan yang diajarkan dalam kelas ini.', 'Self-Efficacy for Learning and Performance', FALSE),
(1, 20, 'Saya yakin dapat berbuat baik dalam tugas dan tes dalam kelas ini.', 'Self-Efficacy for Learning and Performance', FALSE),
(2, 1, 'Karena saya merasakan kepuasan saat menemukan hal-hal baru yang tidak pernah saya lihat atau ketahui sebelumnya.', 'Intrinsic Motivation - To Know', FALSE),
(2, 2, 'Karena saya merasakan kepuasan saat membaca tentang berbagai topik menarik.', 'Intrinsic Motivation - To Know', FALSE),
(2, 3, 'Karena saya merasakan kepuasan saat saya merasakan diri saya benar-benar terlibat dalam apa yang saya lakukan.', 'Intrinsic Motivation - To Experience Stimulation', FALSE),
(2, 4, 'Karena saya merasakan kepuasan saat saya dapat berkomunikasi dengan baik dalam bahasa Inggris.', 'Intrinsic Motivation - To Experience Stimulation', FALSE),
(2, 5, 'Karena menurut saya sekolah menengah akan membantu saya membuat pilihan karir yang lebih baik.', 'Extrinsic Motivation - Identified', FALSE),
(2, 6, 'Karena akan membantu saya membuat pilihan yang lebih baik mengenai orientasi karir saya.', 'Extrinsic Motivation - Identified', FALSE),
(2, 7, 'Karena saya ingin menunjukkan kepada diri saya sendiri bahwa saya dapat berhasil dalam studi saya.', 'Extrinsic Motivation - Introjected', FALSE),
(2, 8, 'Karena saya ingin menunjukkan kepada diri saya sendiri bahwa saya adalah orang yang cerdas.', 'Extrinsic Motivation - Introjected', FALSE),
(2, 9, 'Saya tidak tahu; saya tidak dapat memahami apa yang saya lakukan di sekolah.', 'Amotivation', FALSE),
(2, 10, 'Jujur, saya tidak tahu; saya benar-benar merasa bahwa saya membuang-buang waktu di sekolah.', 'Amotivation', FALSE);

-- +goose Down
-- SQL in section 'Down' is executed when this migration is rolled back
-- No specific down operations for data insertion, as it's typically handled by schema rollback