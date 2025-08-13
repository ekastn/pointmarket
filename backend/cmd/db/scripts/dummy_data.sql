-- insert users (from legacy data)
insert ignore into users (username, password, display_name, email, role) values
('andi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'andi pratama', 'andi@student.com', 'siswa'),
('budi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'budi santoso', 'budi@student.com', 'siswa'),
('citra', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'citra dewi', 'citra@student.com', 'siswa'),
('sarah', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sarah wulandari', 'sarah@teacher.com', 'guru'),
('ahmad', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ahmad rahman', 'ahmad@teacher.com', 'guru'),
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrator', 'admin@pointmarket.com', 'admin');

-- insert questionnaires (mslq, ams, vark)
insert ignore into questionnaires (id, type, name, description, total_questions, status) values
(1, 'mslq', 'motivated strategies for learning questionnaire', 'kuesioner untuk mengukur motivasi dan strategi belajar siswa', 81, 'active'),
(2, 'ams', 'academic motivation scale', 'skala untuk mengukur motivasi akademik siswa', 28, 'active');

insert into questionnaires (id, name, description, type, total_questions) values
(3, 'vark learning style assessment', 'assessment to determine visual, auditory, reading/writing, and kinesthetic learning preferences', 'vark', 16)
on duplicate key update name = values(name), description = values(description), type = values(type), total_questions = values(total_questions);

-- get teacher ids for course owners
set @sarah_id = (select id from users where email = 'sarah@teacher.com' limit 1);
set @ahmad_id = (select id from users where email = 'ahmad@teacher.com' limit 1);

-- insert courses (mapping legacy subjects to new courses)
insert ignore into courses (title, slug, description, owner_id, metadata) values
('matematika dasar', 'matematika-dasar', 'kursus dasar matematika', @sarah_id, '{}'),
('bahasa indonesia dasar', 'bahasa-indonesia-dasar', 'kursus dasar bahasa indonesia', @sarah_id, '{}'),
('fisika dasar', 'fisika-dasar', 'kursus dasar fisika', @ahmad_id, '{}'),
('bahasa inggris dasar', 'bahasa-inggris-dasar', 'kursus dasar bahasa inggris', @ahmad_id, '{}');

-- get course ids for linking assignments, quizzes, materials
set @matematika_course_id = (select id from courses where slug = 'matematika-dasar' limit 1);
set @bahasa_indonesia_course_id = (select id from courses where slug = 'bahasa-indonesia-dasar' limit 1);
set @fisika_course_id = (select id from courses where slug = 'fisika-dasar' limit 1);
set @bahasa_inggris_course_id = (select id from courses where slug = 'bahasa-inggris-dasar' limit 1);

-- insert assignments
insert ignore into assignments (title, description, course_id, reward_points, due_date, status) values
('tugas matematika - aljabar linear', 'selesaikan soal-soal aljabar linear pada buku halaman 45-50', @matematika_course_id, 100, '2025-07-15 23:59:59', 'published'),
('essay bahasa indonesia', 'tulis essay tentang "pentingnya literasi digital" minimal 500 kata', @bahasa_indonesia_course_id, 150, '2025-07-20 23:59:59', 'published'),
('laporan praktikum fisika', 'buat laporan hasil praktikum tentang hukum newton', @fisika_course_id, 200, '2025-07-25 23:59:59', 'published');

-- insert quizzes
insert ignore into quizzes (title, description, course_id, reward_points, duration_minutes, status) values
('quiz matematika - trigonometri', 'quiz pilihan ganda tentang konsep dasar trigonometri', @matematika_course_id, 50, 30, 'published'),
('quiz bahasa inggris - grammar', 'quiz tentang tenses dan struktur kalimat', @bahasa_inggris_course_id, 75, 45, 'published');

-- insert student assignments (requires assignment_id and student_id lookups)
set @andi_id = (select id from users where username = 'andi' limit 1);
set @budi_id = (select id from users where username = 'budi' limit 1);
set @citra_id = (select id from users where username = 'citra' limit 1);

set @assignment_aljabar_id = (select id from assignments where title = 'tugas matematika - aljabar linear' limit 1);
set @assignment_essay_id = (select id from assignments where title = 'essay bahasa indonesia' limit 1);
set @assignment_laporan_id = (select id from assignments where title = 'laporan praktikum fisika' limit 1);

insert ignore into student_assignments (student_id, assignment_id, score, status, submitted_at) values
(@andi_id, @assignment_aljabar_id, 85, 'completed', '2025-07-10 14:30:00'),
(@andi_id, @assignment_essay_id, 90, 'completed', '2025-07-12 16:45:00'),
(@budi_id, @assignment_aljabar_id, 75, 'completed', '2025-07-11 10:20:00'),
(@citra_id, @assignment_aljabar_id, 95, 'completed', '2025-07-09 20:15:00');

-- insert student quizzes (requires quiz_id and student_id lookups)
set @quiz_matematika_id = (select id from quizzes where title = 'quiz matematika - trigonometri' limit 1);
set @quiz_bahasa_inggris_id = (select id from quizzes where title = 'quiz bahasa inggris - grammar' limit 1);

insert ignore into student_quizzes (student_id, quiz_id, score, status, started_at, completed_at) values
(@andi_id, @quiz_matematika_id, 80, 'completed', '2025-07-08 09:00:00', '2025-07-08 09:30:00'),
(@andi_id, @quiz_bahasa_inggris_id, 85, 'completed', '2025-07-09 10:30:00', '2025-07-09 11:15:00'),
(@budi_id, @quiz_matematika_id, 70, 'completed', '2025-07-08 10:00:00', '2025-07-08 10:45:00');

-- insert vark questionnaire questions (questionnaire_id = 3)
insert ignore into questionnaire_questions (questionnaire_id, question_number, question_text, subscale) values
(3, 1, 'ketika saya ingin mempelajari sesuatu yang baru, saya lebih suka:', 'learning preference'), (3, 2, 'ketika saya mengikuti petunjuk untuk menggunakan peralatan baru, saya lebih suka:', 'learning preference'), (3, 3, 'ketika saya ingin mengingat nomor telepon, saya:', 'memory strategy'), (3, 4, 'ketika saya menjelaskan sesuatu kepada orang lain, saya cenderung:', 'communication style'), (3, 5, 'ketika saya belajar tentang tempat baru, saya lebih suka:', 'information processing'), (3, 6, 'ketika saya memasak hidangan baru, saya lebih suka:', 'task approach'), (3, 7, 'ketika saya memilih liburan, hal yang paling penting bagi saya adalah:', 'decision making'), (3, 8, 'ketika saya membeli produk baru, saya lebih suka:', 'information gathering'), (3, 9, 'ketika saya belajar keterampilan baru untuk olahraga atau hobi, saya lebih suka:', 'skill learning'), (3, 10, 'ketika saya memilih makanan di restoran atau kafe, saya cenderung:', 'choice making'), (3, 11, 'ketika saya mendengarkan musik, hal yang paling saya perhatikan adalah:', 'attention focus'), (3, 12, 'ketika saya berkonsentrasi, saya paling terganggu oleh:', 'concentration'), (3, 13, 'ketika saya marah, saya cenderung:', 'emotional expression'), (3, 14, 'ketika saya menghadiri pernikahan atau pesta, hal yang paling saya ingat adalah:', 'memory formation'), (3, 15, 'ketika saya melihat suatu daerah untuk pertama kalinya, saya:', 'spatial processing'), (3, 16, 'ketika saya belajar bahasa asing, saya lebih suka:', 'language learning');

-- insert vark answer options (questionnaire_id = 3)
insert ignore into questionnaire_vark_options (question_id, option_letter, option_text, learning_style) values
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 1), 'a', 'menonton video atau melihat demonstrasi', 'visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 1), 'b', 'mendengarkan penjelasan dari ahli', 'auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 1), 'c', 'membaca buku atau artikel tentang topik tersebut', 'reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 1), 'd', 'mencoba langsung dan mempraktikkannya', 'kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 2), 'a', 'melihat diagram atau gambar instruksi', 'visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 2), 'b', 'meminta seseorang menjelaskan secara lisan', 'auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 2), 'c', 'membaca manual atau petunjuk tertulis', 'reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 2), 'd', 'mencoba menggunakan peralatan sambil belajar', 'kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 3), 'a', 'membayangkan angka-angka tersebut', 'visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 3), 'b', 'mengucapkan angka tersebut berulang-ulang', 'auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 3), 'c', 'menuliskannya beberapa kali', 'reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 3), 'd', 'menekan tombol-tombol angka sambil mengingat', 'kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 4), 'a', 'menggambar diagram atau membuat sketsa', 'visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 4), 'b', 'menjelaskan secara verbal dengan detail', 'auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 4), 'c', 'menulis poin-poin penting', 'reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 4), 'd', 'memberikan contoh atau demonstrasi praktis', 'kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 5), 'a', 'melihat foto-foto atau peta tempat tersebut', 'visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 5), 'b', 'mendengar cerita orang tentang tempat itu', 'auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 5), 'c', 'membaca panduan wisata atau artikel', 'reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 5), 'd', 'langsung mengunjungi dan menjelajahi tempat itu', 'kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 6), 'a', 'menonton video tutorial memasak', 'visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 6), 'b', 'meminta seseorang menjelaskan langkah-langkahnya', 'auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 6), 'c', 'mengikuti resep tertulis langkah demi langkah', 'reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 6), 'd', 'mencoba memasak sambil mengira-ngira takaran', 'kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 7), 'a', 'pemandangan yang indah untuk dilihat', 'visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 7), 'b', 'tempat yang tenang untuk bersantai', 'auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 7), 'c', 'tempat dengan sejarah menarik untuk dipelajari', 'reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 7), 'd', 'aktivitas fisik dan petualangan', 'kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 8), 'a', 'melihat produk secara langsung dan tampilannya', 'visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 8), 'b', 'mendengar review atau rekomendasi orang lain', 'auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 8), 'c', 'membaca spesifikasi dan review online', 'reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 8), 'd', 'mencoba atau memegang produk terlebih dahulu', 'kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 9), 'a', 'menonton demonstrasi video atau tutorial', 'visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 9), 'b', 'mendengarkan instruksi verbal dari pelatih', 'auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 9), 'c', 'membaca buku panduan atau manual', 'reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 9), 'd', 'langsung praktik dengan trial and error', 'kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 10), 'a', 'melihat foto makanan di menu', 'visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 10), 'b', 'bertanya kepada pelayan tentang rekomendasi', 'auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 10), 'c', 'membaca deskripsi menu dengan detail', 'reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 10), 'd', 'memilih berdasarkan aroma atau makanan yang terlihat', 'kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 11), 'a', 'video musik atau visualisasi', 'visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 11), 'b', 'melodi dan harmoni musik', 'auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 11), 'c', 'lirik atau kata-kata dalam lagu', 'reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 11), 'd', 'ritme yang membuat saya ingin bergerak', 'kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 12), 'a', 'gerakan atau objek yang bergerak di sekitar saya', 'visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 12), 'b', 'suara atau kebisingan di sekitar', 'auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 12), 'c', 'teks atau tulisan yang tidak rapi', 'reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 12), 'd', 'posisi duduk yang tidak nyaman', 'kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 13), 'a', 'diam dan memberikan tatapan tajam', 'visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 13), 'b', 'mengungkapkan perasaan secara verbal', 'auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 13), 'c', 'menulis perasaan saya di diary atau surat', 'reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 13), 'd', 'pergi keluar atau melakukan aktivitas fisik', 'kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 14), 'a', 'dekorasi dan penampilan tempat acara', 'visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 14), 'b', 'musik atau percakapan yang saya dengar', 'auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 14), 'c', 'nama-nama orang yang hadir atau detail acara', 'reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 14), 'd', 'tarian atau aktivitas yang saya lakukan', 'kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 15), 'a', 'mengingat landmark atau bangunan yang menonjol', 'visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 15), 'b', 'mengingat nama jalan atau petunjuk arah verbal', 'auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 15), 'c', 'mencatat atau mengingat alamat dan petunjuk tertulis', 'reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 15), 'd', 'mengingat rute berdasarkan gerakan dan arah yang saya ambil', 'kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 16), 'a', 'melihat gambar dan flash cards visual', 'visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 16), 'b', 'mendengarkan audio dan berbicara dengan native speaker', 'auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 16), 'c', 'membaca teks dan mempelajari tata bahasa', 'reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 16), 'd', 'bermain games atau role-play dalam bahasa tersebut', 'kinesthetic');

-- data from weekly_evaluations_update.sql
insert into `questionnaire_questions` (`questionnaire_id`, `question_number`, `question_text`, `subscale`) values
(1, 1, 'dalam sebuah kelas seperti ini, saya lebih suka materi pembelajaran yang benar-benar menantang sehingga saya dapat belajar hal-hal baru.', 'intrinsic goal orientation'),
(1, 2, 'dalam sebuah kelas seperti ini, saya lebih suka materi pembelajaran yang menggugah rasa ingin tahu saya, meskipun sulit untuk dipelajari.', 'intrinsic goal orientation'),
(1, 3, 'hal yang paling memuaskan bagi saya dalam kelas ini adalah mencoba memahami konten selengkap mungkin.', 'intrinsic goal orientation'),
(1, 4, 'ketika saya berkesempatan dalam kelas ini, saya memilih tugas yang dapat saya pelajari, bahkan jika itu tidak menjamin nilai yang baik.', 'intrinsic goal orientation'),
(1, 5, 'mendapatkan nilai yang baik dalam kelas ini adalah hal yang paling memuaskan bagi saya saat ini.', 'extrinsic goal orientation'),
(1, 6, 'hal yang paling penting bagi saya sekarang adalah meningkatkan nilai rata-rata saya secara keseluruhan, jadi perhatian utama saya dalam kelas ini adalah mendapatkan nilai yang baik.', 'extrinsic goal orientation'),
(1, 7, 'jika saya dapat, saya ingin mendapat nilai yang lebih baik dalam kelas ini daripada kebanyakan siswa lain.', 'extrinsic goal orientation'),
(1, 8, 'saya ingin berbuat baik dalam kelas ini karena penting untuk menunjukkan kemampuan saya kepada keluarga, teman, atasan, atau orang lain.', 'extrinsic goal orientation'),
(1, 9, 'saya pikir saya akan dapat menggunakan apa yang saya pelajari dalam kelas ini di kelas lain.', 'task value'),
(1, 10, 'penting bagi saya untuk mempelajari materi dalam kelas ini.', 'task value'),
(1, 11, 'saya sangat tertarik dengan bidang konten kelas ini.', 'task value'),
(1, 12, 'saya pikir materi kelas ini berguna untuk dipelajari.', 'task value'),
(1, 13, 'jika saya belajar dengan cara yang tepat, maka saya akan dapat mempelajari materi dalam kelas ini.', 'control of learning beliefs'),
(1, 14, 'terserah pada saya apakah saya mempelajari materi dengan baik dalam kelas ini atau tidak.', 'control of learning beliefs'),
(1, 15, 'jika saya mencoba cukup keras, maka saya akan memahami materi kelas.', 'control of learning beliefs'),
(1, 16, 'jika saya tidak mempelajari materi kelas dengan baik, itu karena saya tidak mencoba cukup keras.', 'control of learning beliefs'),
(1, 17, 'saya yakin dapat memahami konsep yang paling sulit yang disajikan oleh instruktur dalam kelas ini.', 'self-efficacy for learning and performance'),
(1, 18, 'saya yakin dapat memahami materi yang paling rumit yang disajikan dalam bacaan untuk kelas ini.', 'self-efficacy for learning and performance'),
(1, 19, 'saya yakin dapat menguasai keterampilan yang diajarkan dalam kelas ini.', 'self-efficacy for learning and performance'),
(1, 20, 'saya yakin dapat berbuat baik dalam tugas dan tes dalam kelas ini.', 'self-efficacy for learning and performance'),
(2, 1, 'karena saya merasakan kepuasan saat menemukan hal-hal baru yang tidak pernah saya lihat atau ketahui sebelumnya.', 'intrinsic motivation - to know'),
(2, 2, 'karena saya merasakan kepuasan saat membaca tentang berbagai topik menarik.', 'intrinsic motivation - to know'),
(2, 3, 'karena saya merasakan kepuasan saat saya merasakan diri saya benar-benar terlibat dalam apa yang saya lakukan.', 'intrinsic motivation - to experience stimulation'),
(2, 4, 'karena saya merasakan kepuasan saat saya dapat berkomunikasi dengan baik dalam bahasa inggris.', 'intrinsic motivation - to experience stimulation'),
(2, 5, 'karena menurut saya sekolah menengah akan membantu saya membuat pilihan karir yang lebih baik.', 'extrinsic motivation - identified'),
(2, 6, 'karena akan membantu saya membuat pilihan yang lebih baik mengenai orientasi karir saya.', 'extrinsic motivation - identified'),
(2, 7, 'karena saya ingin menunjukkan kepada diri saya sendiri bahwa saya dapat berhasil dalam studi saya.', 'extrinsic motivation - introjected'),
(2, 8, 'karena saya ingin menunjukkan kepada diri saya sendiri bahwa saya adalah orang yang cerdas.', 'extrinsic motivation - introjected'),
(2, 9, 'saya tidak tahu; saya tidak dapat memahami apa yang saya lakukan di sekolah.', 'amotivation'),
(2, 10, 'jujur, saya tidak tahu; saya benar-benar merasa bahwa saya membuang-buang waktu di sekolah.', 'amotivation');
