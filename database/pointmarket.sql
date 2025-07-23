-- POINTMARKET Database Schema
-- Untuk import ke phpMyAdmin

CREATE DATABASE IF NOT EXISTS `pointmarket` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pointmarket`;

-- Tabel Users
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('siswa','guru','admin') NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Assignments
CREATE TABLE `assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text,
  `subject` varchar(100) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `points` int(11) DEFAULT 0,
  `due_date` datetime DEFAULT NULL,
  `status` enum('active','inactive','completed') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `teacher_id` (`teacher_id`),
  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Quiz
CREATE TABLE `quiz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text,
  `subject` varchar(100) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `points` int(11) DEFAULT 0,
  `duration` int(11) DEFAULT NULL, -- dalam menit
  `status` enum('active','inactive','completed') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `teacher_id` (`teacher_id`),
  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Student Assignments (untuk tracking penyelesaian)
CREATE TABLE `student_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `score` int(11) DEFAULT NULL,
  `status` enum('not_started','in_progress','completed','submitted') DEFAULT 'not_started',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `graded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_assignment` (`student_id`,`assignment_id`),
  KEY `student_id` (`student_id`),
  KEY `assignment_id` (`assignment_id`),
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assignment_id`) REFERENCES `assignments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Student Quiz (untuk tracking penyelesaian quiz)
CREATE TABLE `student_quiz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `score` int(11) DEFAULT NULL,
  `status` enum('not_started','in_progress','completed','submitted') DEFAULT 'not_started',
  `started_at` timestamp NULL DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_quiz` (`student_id`,`quiz_id`),
  KEY `student_id` (`student_id`),
  KEY `quiz_id` (`quiz_id`),
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`quiz_id`) REFERENCES `quiz`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Questionnaires
CREATE TABLE `questionnaires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('mslq','ams') NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `total_questions` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Questionnaire Results
CREATE TABLE `questionnaire_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `questionnaire_id` int(11) NOT NULL,
  `answers` json NOT NULL,
  `total_score` decimal(5,2) DEFAULT NULL,
  `subscale_scores` json DEFAULT NULL,
  `completed_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `questionnaire_id` (`questionnaire_id`),
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`questionnaire_id`) REFERENCES `questionnaires`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Materials (untuk guru upload materi)
CREATE TABLE `materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text,
  `subject` varchar(100) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `teacher_id` (`teacher_id`),
  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Data Demo
INSERT INTO `users` (`username`, `password`, `name`, `email`, `role`) VALUES
('andi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Andi Pratama', 'andi@student.com', 'siswa'),
('budi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso', 'budi@student.com', 'siswa'),
('citra', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Citra Dewi', 'citra@student.com', 'siswa'),
('sarah', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Wulandari', 'sarah@teacher.com', 'guru'),
('ahmad', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ahmad Rahman', 'ahmad@teacher.com', 'guru'),
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@pointmarket.com', 'admin');

-- Insert Questionnaires
INSERT INTO `questionnaires` (`type`, `name`, `description`, `total_questions`) VALUES
('mslq', 'Motivated Strategies for Learning Questionnaire', 'Kuesioner untuk mengukur motivasi dan strategi belajar siswa', 81),
('ams', 'Academic Motivation Scale', 'Skala untuk mengukur motivasi akademik siswa', 28);

-- Insert Sample Assignments
INSERT INTO `assignments` (`title`, `description`, `subject`, `teacher_id`, `points`, `due_date`) VALUES
('Tugas Matematika - Aljabar Linear', 'Selesaikan soal-soal aljabar linear pada buku halaman 45-50', 'Matematika', 4, 100, '2025-07-15 23:59:59'),
('Essay Bahasa Indonesia', 'Tulis essay tentang "Pentingnya Literasi Digital" minimal 500 kata', 'Bahasa Indonesia', 4, 150, '2025-07-20 23:59:59'),
('Laporan Praktikum Fisika', 'Buat laporan hasil praktikum tentang Hukum Newton', 'Fisika', 5, 200, '2025-07-25 23:59:59');

-- Insert Sample Quiz
INSERT INTO `quiz` (`title`, `description`, `subject`, `teacher_id`, `points`, `duration`) VALUES
('Quiz Matematika - Trigonometri', 'Quiz pilihan ganda tentang konsep dasar trigonometri', 'Matematika', 4, 50, 30),
('Quiz Bahasa Inggris - Grammar', 'Quiz tentang tenses dan struktur kalimat', 'Bahasa Inggris', 5, 75, 45);

-- Insert Sample Materials
INSERT INTO `materials` (`title`, `description`, `subject`, `teacher_id`, `file_type`) VALUES
('Modul Matematika - Kalkulus', 'Materi pembelajaran kalkulus dasar', 'Matematika', 4, 'pdf'),
('Video Tutorial - Grammar', 'Video pembelajaran tentang grammar bahasa Inggris', 'Bahasa Inggris', 5, 'video'),
('Slide Presentasi - Fisika Quantum', 'Presentasi tentang konsep dasar fisika quantum', 'Fisika', 5, 'presentation');

-- Insert Sample Student Assignments (untuk demo)
INSERT INTO `student_assignments` (`student_id`, `assignment_id`, `score`, `status`, `submitted_at`) VALUES
(1, 1, 85, 'completed', '2025-07-10 14:30:00'),
(1, 2, 90, 'completed', '2025-07-12 16:45:00'),
(2, 1, 75, 'completed', '2025-07-11 10:20:00'),
(3, 1, 95, 'completed', '2025-07-09 20:15:00');

-- Insert Sample Student Quiz
INSERT INTO `student_quiz` (`student_id`, `quiz_id`, `score`, `status`, `submitted_at`) VALUES
(1, 1, 80, 'completed', '2025-07-08 09:30:00'),
(1, 2, 85, 'completed', '2025-07-09 11:15:00'),
(2, 1, 70, 'completed', '2025-07-08 10:45:00');

-- View untuk statistik siswa (total points dan completed assignments)
CREATE VIEW `student_stats` AS
SELECT 
    u.id as student_id,
    u.name as student_name,
    COALESCE(
        (SELECT SUM(sa.score) FROM student_assignments sa WHERE sa.student_id = u.id AND sa.status = 'completed') +
        (SELECT SUM(sq.score) FROM student_quiz sq WHERE sq.student_id = u.id AND sq.status = 'completed'),
        0
    ) as total_points,
    COALESCE(
        (SELECT COUNT(*) FROM student_assignments sa WHERE sa.student_id = u.id AND sa.status = 'completed') +
        (SELECT COUNT(*) FROM student_quiz sq WHERE sq.student_id = u.id AND sq.status = 'completed'),
        0
    ) as completed_assignments
FROM users u 
WHERE u.role = 'siswa';
