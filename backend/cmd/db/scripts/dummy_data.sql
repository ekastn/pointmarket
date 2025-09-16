-- insert users (from legacy data)
insert ignore into users (username, password, display_name, email, role) values
('andi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'andi pratama', 'andi@student.com', 'siswa'),
('budi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'budi santoso', 'budi@student.com', 'siswa'),
('citra', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'citra dewi', 'citra@student.com', 'siswa'),
('sarah', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sarah wulandari', 'sarah@teacher.com', 'guru'),
('ahmad', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ahmad rahman', 'ahmad@teacher.com', 'guru'),
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrator', 'admin@pointmarket.com', 'admin');

-- Additional users
insert ignore into users (username, password, display_name, email, role) values
('dewi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dewi lestari', 'dewi@student.com', 'siswa'),
('eko', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'eko prasetyo', 'eko@student.com', 'siswa'),
('fajar', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'fajar ramadhan', 'fajar@teacher.com', 'guru'),
('gita', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gita permata', 'gita@student.com', 'siswa');

-- User Profiles
set @andi_id = (select id from users where username = 'andi' limit 1);
set @budi_id = (select id from users where username = 'budi' limit 1);
set @citra_id = (select id from users where username = 'citra' limit 1);
set @sarah_id = (select id from users where username = 'sarah' limit 1);
set @ahmad_id = (select id from users where username = 'ahmad' limit 1);
set @admin_id = (select id from users where username = 'admin' limit 1);
set @dewi_id = (select id from users where username = 'dewi' limit 1);
set @eko_id = (select id from users where username = 'eko' limit 1);
set @fajar_id = (select id from users where username = 'fajar' limit 1);
set @gita_id = (select id from users where username = 'gita' limit 1);

-- Faculties and Programs (academic schema)
insert ignore into faculties (name) values
('vokasi');

set @vokasi_id = (select id from faculties where name = 'vokasi' limit 1);

insert ignore into programs (name, faculty_id) values
('D4 Teknik Informatika', @vokasi_id),
('D3 Sistem Informasi', @vokasi_id);

set @prodi_ti = (select id from programs where name = 'D4 Teknik Informatika' limit 1);
set @prodi_si = (select id from programs where name = 'D3 Sistem Informasi' limit 1);

-- Map existing student users to students table with sample identities
insert ignore into students (user_id, student_id, program_id, cohort_year, status, phone) values
(@andi_id,  '2401001', @prodi_ti, 2024, 'active',  '081234567801'),
(@budi_id,  '2401002', @prodi_ti, 2024, 'active',  '081234567802'),
(@citra_id, '2401003', @prodi_si, 2023, 'active',  '081234567803'),
(@dewi_id,  '2401004', @prodi_si, 2023, 'active',  '081234567804'),
(@eko_id,   '2401005', @prodi_ti, 2024, 'active',  '081234567805'),
(@gita_id,  '2401006', @prodi_ti, 2024, 'active',  '081234567806');

-- Convenience vars for student codes (VARCHAR student_id)
set @andi_sid  = (select student_id from students where user_id = @andi_id  limit 1);
set @budi_sid  = (select student_id from students where user_id = @budi_id  limit 1);
set @citra_sid = (select student_id from students where user_id = @citra_id limit 1);
set @dewi_sid  = (select student_id from students where user_id = @dewi_id  limit 1);
set @eko_sid   = (select student_id from students where user_id = @eko_id   limit 1);
set @gita_sid  = (select student_id from students where user_id = @gita_id  limit 1);

insert ignore into user_profiles (user_id, avatar_url, bio, metadata) values
(@andi_id, 'https://i.pravatar.cc/150?img=1', 'Siswa yang bersemangat dalam belajar matematika.', '{}'),
(@budi_id, 'https://i.pravatar.cc/150?img=2', 'Siswa yang suka membaca dan menulis.', '{}'),
(@citra_id, 'https://i.pravatar.cc/150?img=3', 'Siswa yang aktif dalam kegiatan ekstrakurikuler.', '{}'),
(@sarah_id, 'https://i.pravatar.cc/150?img=4', 'Guru matematika dengan pengalaman 10 tahun.', '{}'),
(@ahmad_id, 'https://i.pravatar.cc/150?img=5', 'Guru fisika yang inovatif.', '{}'),
(@admin_id, 'https://i.pravatar.cc/150?img=6', 'Administrator sistem PointMarket.', '{}'),
(@dewi_id, 'https://i.pravatar.cc/150?img=7', 'Siswa yang tertarik pada biologi dan sains.', '{}'),
(@eko_id, 'https://i.pravatar.cc/150?img=8', 'Siswa yang gemar belajar bahasa asing.', '{}'),
(@fajar_id, 'https://i.pravatar.cc/150?img=9', 'Guru bahasa Indonesia yang berdedikasi.', '{}'),
(@gita_id, 'https://i.pravatar.cc/150?img=10', 'Siswa yang menyukai seni dan musik.', '{}');

-- User Stats
insert ignore into user_stats (user_id, total_points) values
(@andi_id, 1000),
(@budi_id, 800),
(@citra_id, 1200),
(@sarah_id, 500),
(@ahmad_id, 600),
(@admin_id, 9999),
(@dewi_id, 750),
(@eko_id, 900),
(@fajar_id, 550),
(@gita_id, 1100);

-- Product Categories
insert ignore into product_categories (name, description) values
('Buku Pelajaran', 'Kategori untuk buku-buku pelajaran sekolah dan universitas.'),
('Alat Tulis', 'Kategori untuk berbagai macam alat tulis dan perlengkapan kantor.'),
('Kursus Online', 'Kategori untuk kursus-kursus online interaktif.'),
('Merchandise', 'Kategori untuk barang-barang promosi dan souvenir.');

-- Get product category IDs
set @buku_pelajaran_id = (select id from product_categories where name = 'Buku Pelajaran' limit 1);
set @alat_tulis_id = (select id from product_categories where name = 'Alat Tulis' limit 1);
set @kursus_online_id = (select id from product_categories where name = 'Kursus Online' limit 1);
set @merchandise_id = (select id from product_categories where name = 'Merchandise' limit 1);

-- Products
insert ignore into products (category_id, name, description, points_price, type, stock_quantity, is_active, metadata) values
(@buku_pelajaran_id, 'Buku Matematika SMA Kelas X', 'Buku pelajaran matematika untuk siswa SMA kelas X kurikulum terbaru.', 250, 'digital', 100, TRUE, '{}'),
(@buku_pelajaran_id, 'Modul Fisika Dasar', 'Modul pembelajaran fisika dasar dilengkapi dengan contoh soal dan pembahasan.', 200, 'digital', 150, TRUE, '{}'),
(@alat_tulis_id, 'Paket Alat Tulis Lengkap', 'Terdiri dari pensil, pulpen, penghapus, dan penggaris.', 100, 'physical', 50, TRUE, '{}'),
(@alat_tulis_id, 'Buku Catatan A5', 'Buku catatan dengan sampul keras dan kertas berkualitas tinggi.', 80, 'physical', 200, TRUE, '{}'),
(@kursus_online_id, 'Kursus Pemrograman Python Dasar', 'Kursus interaktif untuk mempelajari dasar-dasar pemrograman Python.', 500, 'course', NULL, TRUE, '{}'),
(@kursus_online_id, 'Kursus Desain Grafis Fundamental', 'Pelajari prinsip dasar desain grafis menggunakan software populer.', 450, 'course', NULL, TRUE, '{}'),
(@merchandise_id, 'Kaos PointMarket', 'Kaos eksklusif dengan logo PointMarket.', 150, 'physical', 30, TRUE, '{}'),
(@merchandise_id, 'Tumbler PointMarket', 'Tumbler stainless steel dengan desain modern.', 120, 'physical', 40, TRUE, '{}');

-- (moved lessons insert later after courses are created)

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
('bahasa inggris dasar', 'bahasa-inggris-dasar', 'kursus dasar bahasa inggris', @ahmad_id, '{}'),
('python dasar', 'python-dasar', 'kursus pemrograman python dasar', @fajar_id, '{}'),
('desain grafis fundamental', 'desain-grafis-fundamental', 'kursus desain grafis untuk pemula', @fajar_id, '{}');

-- get course ids for linking assignments, quizzes, materials
set @matematika_course_id = (select id from courses where slug = 'matematika-dasar' limit 1);
set @bahasa_indonesia_course_id = (select id from courses where slug = 'bahasa-indonesia-dasar' limit 1);
set @fisika_course_id = (select id from courses where slug = 'fisika-dasar' limit 1);
set @bahasa_inggris_course_id = (select id from courses where slug = 'bahasa-inggris-dasar' limit 1);
set @python_course_id = (select id from courses where slug = 'python-dasar' limit 1);
set @desain_grafis_course_id = (select id from courses where slug = 'desain-grafis-fundamental' limit 1);

-- Lessons for existing courses
insert ignore into lessons (course_id, title, ordinal, content) values
(@matematika_course_id, 'Pengantar Aljabar', 1, '{"type": "text", "value": "Mempelajari konsep dasar aljabar."}'),
(@matematika_course_id, 'Persamaan Linear', 2, '{"type": "text", "value": "Memahami cara menyelesaikan persamaan linear."}'),
(@matematika_course_id, 'Sistem Persamaan Linear', 3, '{"type": "text", "value": "Menyelesaikan sistem persamaan dua variabel."}'),
(@bahasa_indonesia_course_id, 'Struktur Kalimat Efektif', 1, '{"type": "text", "value": "Mempelajari kaidah penulisan kalimat yang efektif."}'),
(@bahasa_indonesia_course_id, 'Menulis Paragraf Deskriptif', 2, '{"type": "text", "value": "Latihan menulis paragraf yang mendeskripsikan sesuatu."}'),
(@fisika_course_id, 'Hukum Newton I', 1, '{"type": "text", "value": "Memahami konsep inersia dan Hukum Newton pertama."}'),
(@fisika_course_id, 'Hukum Newton II', 2, '{"type": "text", "value": "Mempelajari hubungan antara gaya, massa, dan percepatan."}'),
(@fisika_course_id, 'Hukum Newton III', 3, '{"type": "text", "value": "Tindakan dan reaksi dalam interaksi gaya."}'),
(@bahasa_inggris_course_id, 'Basic Greetings', 1, '{"type": "text", "value": "Learn common English greetings."}'),
(@bahasa_inggris_course_id, 'Introducing Yourself', 2, '{"type": "text", "value": "Practice introducing yourself in English."}'),
(@python_course_id, 'Getting Started with Python', 1, '{"type": "text", "value": "Instalasi dan hello world."}'),
(@python_course_id, 'Variables and Data Types', 2, '{"type": "text", "value": "Memahami tipe data dasar dan variabel."}'),
(@python_course_id, 'Control Flow', 3, '{"type": "text", "value": "Percabangan dan perulangan."}'),
(@desain_grafis_course_id, 'Pengenalan Desain Grafis', 1, '{"type": "text", "value": "Konsep dasar desain grafis."}'),
(@desain_grafis_course_id, 'Tipografi Dasar', 2, '{"type": "text", "value": "Memilih dan mengatur huruf."}'),
(@desain_grafis_course_id, 'Warna dan Komposisi', 3, '{"type": "text", "value": "Teori warna dan komposisi visual."}');

-- Enroll students to courses for demo
insert ignore into student_courses (student_id, course_id) values
(@andi_sid, @matematika_course_id),
(@andi_sid, @python_course_id),
(@budi_sid, @bahasa_indonesia_course_id),
(@budi_sid, @desain_grafis_course_id),
(@citra_sid, @fisika_course_id),
(@dewi_sid, @bahasa_inggris_course_id),
(@eko_sid, @matematika_course_id),
(@gita_sid, @desain_grafis_course_id);

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

-- insert student quizzes (requires quiz_id and student_id lookups)
set @quiz_matematika_id = (select id from quizzes where title = 'quiz matematika - trigonometri' limit 1);
set @quiz_bahasa_inggris_id = (select id from quizzes where title = 'quiz bahasa inggris - grammar' limit 1);

insert ignore into student_quizzes (student_id, quiz_id, score, status, started_at, completed_at) values
(@andi_sid, @quiz_matematika_id, 80, 'completed', '2025-07-08 09:00:00', '2025-07-08 09:30:00'),
(@andi_sid, @quiz_bahasa_inggris_id, 85, 'completed', '2025-07-09 10:30:00', '2025-07-09 11:15:00'),
(@budi_sid, @quiz_matematika_id, 70, 'completed', '2025-07-08 10:00:00', '2025-07-08 10:45:00');

-- Quiz Questions for 'quiz matematika - trigonometri' (quiz_id = @quiz_matematika_id)
insert ignore into quiz_questions (quiz_id, question_text, question_type, answer_options, correct_answer) values
(@quiz_matematika_id, 'Berapakah nilai sin(30 derajat)?', 'multiple_choice', '{"a": "0.5", "b": "0.707", "c": "0.866", "d": "1"}', 'a'),
(@quiz_matematika_id, 'Jika cos(x) = 0.8, berapakah nilai sin(x)?', 'multiple_choice', '{"a": "0.2", "b": "0.4", "c": "0.6", "d": "1"}', 'c'),
(@quiz_matematika_id, 'Sudut 90 derajat dalam radian adalah...', 'multiple_choice', '{"a": "pi/4", "b": "pi/2", "c": "pi", "d": "2pi"}', 'b');

-- Quiz Questions for 'quiz bahasa inggris - grammar' (quiz_id = @quiz_bahasa_inggris_id)
insert ignore into quiz_questions (quiz_id, question_text, question_type, answer_options, correct_answer) values
(@quiz_bahasa_inggris_id, 'She ___ to the store yesterday.', 'multiple_choice', '{"a": "go", "b": "goes", "c": "went", "d": "going"}', 'c'),
(@quiz_bahasa_inggris_id, 'They ___ playing football now.', 'multiple_choice', '{"a": "is", "b": "are", "c": "am", "d": "be"}', 'b'),
(@quiz_bahasa_inggris_id, 'The cat is ___ the table.', 'multiple_choice', '{"a": "in", "b": "on", "c": "at", "d": "under"}', 'b');

insert ignore into badges (title, description, criteria) values
('Silver',    'Level Silver rank.',    '{"type":"points_min","value":1000}'),
('Gold',      'Level Gold rank.',      '{"type":"points_min","value":1200}'),
('Platinum',  'Level Platinum rank.',  '{"type":"points_min","value":1500}'),
('Diamond',   'Level Diamond rank.',   '{"type":"points_min","value":2000}'),
('Master',    'Level Master rank.',    '{"type":"points_min","value":5000}'),
('King',      'Level King rank.',      '{"type":"points_min","value":9000}');

-- Missions
insert ignore into missions (title, description, reward_points, metadata) values
('Misi Belajar Mandiri: Aljabar', 'Selesaikan modul aljabar linear dan kerjakan tugas terkait.', 200, '{}'),
('Tantangan Membaca Buku: Literasi Digital', 'Baca buku tentang literasi digital dan buat ringkasan.', 150, '{}'),
('Eksperimen Fisika: Hukum Newton', 'Lakukan eksperimen sederhana tentang Hukum Newton dan buat laporan.', 250, '{}'),
('Misi Bahasa Inggris: Percakapan Dasar', 'Latih percakapan dasar bahasa Inggris dengan teman.', 100, '{}');

-- set @silver_badge_id    = (select id from badges where title = 'Silver'    limit 1);
-- set @gold_badge_id      = (select id from badges where title = 'Gold'      limit 1);
-- set @platinum_badge_id  = (select id from badges where title = 'Platinum'  limit 1);
-- set @diamond_badge_id   = (select id from badges where title = 'Diamond'   limit 1);
-- set @master_badge_id    = (select id from badges where title = 'Master'    limit 1);
-- set @king_badge_id      = (select id from badges where title = 'King'      limit 1);

-- User Missions
set @misi_aljabar_id = (select id from missions where title = 'Misi Belajar Mandiri: Aljabar' limit 1);
set @tantangan_literasi_id = (select id from missions where title = 'Tantangan Membaca Buku: Literasi Digital' limit 1);

insert ignore into user_missions (mission_id, user_id, status, completed_at, progress) values
(@misi_aljabar_id, @andi_id, 'completed', '2025-07-15 10:00:00', '{"steps_completed": 5, "total_steps": 5}'),
(@tantangan_literasi_id, @budi_id, 'in_progress', NULL, '{"pages_read": 50, "total_pages": 100}');

-- Points Transactions
insert ignore into points_transactions (user_id, amount, reason, reference_type, reference_id) values
(@andi_id, 100, 'Mengerjakan Tugas', 'assignment', @assignment_aljabar_id),
(@andi_id, 50, 'Menyelesaikan Kuis', 'quiz', @quiz_matematika_id),
(@budi_id, 150, 'Mengerjakan Tugas', 'assignment', @assignment_essay_id),
(@citra_id, 200, 'Menyelesaikan Misi', 'mission', @misi_aljabar_id);

-- Orders
set @buku_matematika_id = (select id from products where name = 'Buku Matematika SMA Kelas X' limit 1);
set @kursus_python_id = (select id from products where name = 'Kursus Pemrograman Python Dasar' limit 1);
set @kursus_desain_id = (select id from products where name = 'Kursus Desain Grafis Fundamental' limit 1);

insert ignore into orders (user_id, product_id, points_spent, status) values
(@andi_id, @buku_matematika_id, 250, 'completed'),
(@budi_id, @kursus_python_id, 500, 'completed');

-- Link course products to courses for enrollment on purchase
insert ignore into product_course_details (product_id, course_id, access_duration_days, enrollment_behavior) values
(@kursus_python_id, @python_course_id, 90, 'immediate'),
(@kursus_desain_id, @desain_grafis_course_id, 60, 'immediate');

-- insert vark questionnaire questions (questionnaire_id = 3)
insert ignore into questionnaire_questions (questionnaire_id, question_number, question_text, subscale) values
(3, 1, 'ketika saya ingin mempelajari sesuatu yang baru, saya lebih suka:', 'learning preference'), (3, 2, 'ketika saya mengikuti petunjuk untuk menggunakan peralatan baru, saya lebih suka:', 'learning preference'), (3, 3, 'ketika saya ingin mengingat nomor telepon, saya:', 'memory strategy'), (3, 4, 'ketika saya menjelaskan sesuatu kepada orang lain, saya cenderung:', 'communication style'), (3, 5, 'ketika saya belajar tentang tempat baru, saya lebih suka:', 'information processing'), (3, 6, 'ketika saya memasak hidangan baru, saya lebih suka:', 'task approach'), (3, 7, 'ketika saya memilih liburan, hal yang paling penting bagi saya adalah:', 'decision making'), (3, 8, 'ketika saya membeli produk baru, saya lebih suka:', 'information gathering'), (3, 9, 'ketika saya belajar keterampilan baru untuk olahraga atau hobi, saya lebih suka:', 'skill learning'), (3, 10, 'ketika saya memilih makanan di restoran atau kafe, saya cenderung:', 'choice making'), (3, 11, 'ketika saya mendengarkan musik, hal yang paling saya perhatikan adalah:', 'attention focus'), (3, 12, 'ketika saya berkonsentrasi, saya paling terganggu oleh:', 'concentration'), (3, 13, 'ketika saya marah, saya cenderung:', 'emotional expression'), (3, 14, 'ketika saya menghadiri pernikahan atau pesta, hal yang paling saya ingat adalah:', 'memory formation'), (3, 15, 'ketika saya melihat suatu daerah untuk pertama kalinya, saya:', 'spatial processing'), (3, 16, 'ketika saya belajar bahasa asing, saya lebih suka:', 'language learning');

-- insert vark answer options (questionnaire_id = 3)
insert ignore into questionnaire_vark_options (question_id, option_letter, option_text, learning_style) values
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 1), 'a', 'menonton video atau melihat demonstrasi', 'Visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 1), 'b', 'mendengarkan penjelasan dari ahli', 'Auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 1), 'c', 'membaca buku atau artikel tentang topik tersebut', 'Reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 1), 'd', 'mencoba langsung dan mempraktikkannya', 'Kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 2), 'a', 'melihat diagram atau gambar instruksi', 'Visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 2), 'b', 'meminta seseorang menjelaskan secara lisan', 'Auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 2), 'c', 'membaca manual atau petunjuk tertulis', 'Reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 2), 'd', 'mencoba menggunakan peralatan sambil belajar', 'Kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 3), 'a', 'membayangkan angka-angka tersebut', 'Visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 3), 'b', 'mengucapkan angka tersebut berulang-ulang', 'Auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 3), 'c', 'menuliskannya beberapa kali', 'Reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 3), 'd', 'menekan tombol-tombol angka sambil mengingat', 'Kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 4), 'a', 'menggambar diagram atau membuat sketsa', 'Visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 4), 'b', 'menjelaskan secara verbal dengan detail', 'Auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 4), 'c', 'menulis poin-poin penting', 'Reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 4), 'd', 'memberikan contoh atau demonstrasi praktis', 'Kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 5), 'a', 'melihat foto-foto atau peta tempat tersebut', 'Visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 5), 'b', 'mendengar cerita orang tentang tempat itu', 'Auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 5), 'c', 'membaca panduan wisata atau artikel', 'Reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 5), 'd', 'langsung mengunjungi dan menjelajahi tempat itu', 'Kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 6), 'a', 'menonton video tutorial memasak', 'Visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 6), 'b', 'meminta seseorang menjelaskan langkah-langkahnya', 'Auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 6), 'c', 'mengikuti resep tertulis langkah demi langkah', 'Reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 6), 'd', 'mencoba memasak sambil mengira-ngira takaran', 'Kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 7), 'a', 'pemandangan yang indah untuk dilihat', 'Visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 7), 'b', 'tempat yang tenang untuk bersantai', 'Auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 7), 'c', 'tempat dengan sejarah menarik untuk dipelajari', 'Reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 7), 'd', 'aktivitas fisik dan petualangan', 'Kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 8), 'a', 'melihat produk secara langsung dan tampilannya', 'Visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 8), 'b', 'mendengar review atau rekomendasi orang lain', 'Auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 8), 'c', 'membaca spesifikasi dan review online', 'Reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 8), 'd', 'mencoba atau memegang produk terlebih dahulu', 'Kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 9), 'a', 'menonton demonstrasi video atau tutorial', 'Visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 9), 'b', 'mendengarkan instruksi verbal dari pelatih', 'Auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 9), 'c', 'membaca buku panduan atau manual', 'Reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 9), 'd', 'langsung praktik dengan trial and error', 'Kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 10), 'a', 'melihat foto makanan di menu', 'Visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 10), 'b', 'bertanya kepada pelayan tentang rekomendasi', 'Auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 10), 'c', 'membaca deskripsi menu dengan detail', 'Reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 10), 'd', 'memilih berdasarkan aroma atau makanan yang terlihat', 'Kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 11), 'a', 'video musik atau visualisasi', 'Visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 11), 'b', 'melodi dan harmoni musik', 'Auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 11), 'c', 'lirik atau kata-kata dalam lagu', 'Reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 11), 'd', 'ritme yang membuat saya ingin bergerak', 'Kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 12), 'a', 'gerakan atau objek yang bergerak di sekitar saya', 'Visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 12), 'b', 'suara atau kebisingan di sekitar', 'Auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 12), 'c', 'teks atau tulisan yang tidak rapi', 'Reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 12), 'd', 'posisi duduk yang tidak nyaman', 'Kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 13), 'a', 'diam dan memberikan tatapan tajam', 'Visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 13), 'b', 'mengungkapkan perasaan secara verbal', 'Auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 13), 'c', 'menulis perasaan saya di diary atau surat', 'Reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 13), 'd', 'pergi keluar atau melakukan aktivitas fisik', 'Kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 14), 'a', 'dekorasi dan penampilan tempat acara', 'Visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 14), 'b', 'musik atau percakapan yang saya dengar', 'Auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 14), 'c', 'nama-nama orang yang hadir atau detail acara', 'Reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 14), 'd', 'tarian atau aktivitas yang saya lakukan', 'Kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 15), 'a', 'mengingat landmark atau bangunan yang menonjol', 'Visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 15), 'b', 'mengingat nama jalan atau petunjuk arah verbal', 'Auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 15), 'c', 'mencatat atau mengingat alamat dan petunjuk tertulis', 'Reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 15), 'd', 'mengingat rute berdasarkan gerakan dan arah yang saya ambil', 'Kinesthetic'),
((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 16), 'a', 'melihat gambar dan flash cards visual', 'Visual'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 16), 'b', 'mendengarkan audio dan berbicara dengan native speaker', 'Auditory'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 16), 'c', 'membaca teks dan mempelajari tata bahasa', 'Reading'), ((select id from questionnaire_questions where questionnaire_id = 3 and question_number = 16), 'd', 'bermain games atau role-play dalam bahasa tersebut', 'Kinesthetic');

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


-- Additional demo questions for more variety
insert ignore into quiz_questions (quiz_id, question_text, question_type, answer_options, correct_answer) values
(@quiz_matematika_id, 'cos(60°) bernilai ...', 'multiple_choice', '{"a":"0.5","b":"0.866","c":"1","d":"0"}', 'a'),
(@quiz_bahasa_inggris_id, 'Choose the correct article: I saw ___ elephant.', 'multiple_choice', '{"a":"a","b":"an","c":"the","d":"-"}', 'b');
