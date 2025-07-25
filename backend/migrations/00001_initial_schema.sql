-- +goose Up
-- SQL in section 'Up' is executed when this migration is applied
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    role ENUM('admin', 'teacher', 'siswa', 'guru') NOT NULL,
    avatar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

CREATE TABLE assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    subject VARCHAR(255) NOT NULL,
    teacher_id INT NOT NULL,
    points INT NOT NULL,
    due_date DATETIME,
    status ENUM('Draft', 'Published', 'Archived') DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE student_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    assignment_id INT NOT NULL,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    score DECIMAL(5,2),
    submission TEXT,
    submitted_at DATETIME,
    graded_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (student_id, assignment_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE
);

CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    subject VARCHAR(255) NOT NULL,
    teacher_id INT NOT NULL,
    points INT NOT NULL,
    duration INT, -- in minutes
    status ENUM('Draft', 'Published', 'Archived') DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE student_quiz (
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
  FOREIGN KEY (`quiz_id`) REFERENCES `quizzes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE questionnaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('mslq', 'ams', 'vark') NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    total_questions INT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE questionnaire_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    questionnaire_id INT NOT NULL,
    question_number INT NOT NULL,
    question_text TEXT NOT NULL,
    subscale VARCHAR(255),
    reverse_scored BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (questionnaire_id) REFERENCES questionnaires(id) ON DELETE CASCADE
);

CREATE TABLE questionnaire_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    questionnaire_id INT NOT NULL,
    answers JSON NOT NULL,
    total_score DECIMAL(5,2),
    subscale_scores JSON,
    completed_at DATETIME NOT NULL,
    week_number INT,
    year INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (questionnaire_id) REFERENCES questionnaires(id) ON DELETE CASCADE
);

CREATE TABLE materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    subject VARCHAR(255) NOT NULL,
    teacher_id INT NOT NULL,
    file_path VARCHAR(255),
    file_type VARCHAR(50),
    status ENUM('Draft', 'Published', 'Archived') DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE nlp_feedback_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(255) NOT NULL UNIQUE,
    score_range_min INT NOT NULL,
    score_range_max INT NOT NULL,
    component VARCHAR(255) NOT NULL,
    vark_style VARCHAR(255),
    mslq_profile VARCHAR(255),
    feedback_text TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE nlp_analysis_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    assignment_id INT,
    quiz_id INT,
    original_text TEXT NOT NULL,
    clean_text TEXT,
    word_count INT,
    sentence_count INT,
    total_score DECIMAL(5,2),
    grammar_score DECIMAL(5,2),
    keyword_score DECIMAL(5,2),
    structure_score DECIMAL(5,2),
    readability_score DECIMAL(5,2),
    sentiment_score DECIMAL(5,2),
    complexity_score DECIMAL(5,2),
    feedback JSON,
    personalized_feedback JSON,
    context_type VARCHAR(255),
    analysis_version VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE SET NULL,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE SET NULL
);

CREATE TABLE nlp_keywords (
    id INT AUTO_INCREMENT PRIMARY KEY,
    context VARCHAR(255) NOT NULL,
    keyword VARCHAR(255) NOT NULL,
    weight DECIMAL(5,2) DEFAULT 1.0,
    category VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (context, keyword)
);

CREATE TABLE nlp_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    month INT NOT NULL,
    year INT NOT NULL,
    total_analyses INT DEFAULT 0,
    average_score DECIMAL(5,2) DEFAULT 0.0,
    best_score DECIMAL(5,2) DEFAULT 0.0,
    improvement_rate DECIMAL(5,2) DEFAULT 0.0,
    grammar_improvement DECIMAL(5,2) DEFAULT 0.0,
    keyword_improvement DECIMAL(5,2) DEFAULT 0.0,
    structure_improvement DECIMAL(5,2) DEFAULT 0.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (student_id, month, year),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE weekly_evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    questionnaire_id INT NOT NULL,
    week_number INT NOT NULL,
    year INT NOT NULL,
    status ENUM('pending', 'completed', 'overdue') NOT NULL,
    due_date DATETIME NOT NULL,
    completed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (student_id, questionnaire_id, week_number, year),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (questionnaire_id) REFERENCES questionnaires(id) ON DELETE CASCADE
);

CREATE TABLE vark_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    visual_score INT NOT NULL,
    auditory_score INT NOT NULL,
    reading_score INT NOT NULL,
    kinesthetic_score INT NOT NULL,
    dominant_style VARCHAR(255) NOT NULL,
    learning_preference TEXT,
    answers JSON,
    completed_at DATETIME NOT NULL,
    week_number INT,
    year INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE vark_answer_options (
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

-- +goose Down
-- SQL in section 'Down' is executed when this migration is rolled back
DROP TABLE IF EXISTS vark_answer_options;
DROP TABLE IF EXISTS vark_results;
DROP TABLE IF EXISTS weekly_evaluations;
DROP TABLE IF EXISTS activity_log;
DROP TABLE IF EXISTS nlp_progress;
DROP TABLE IF EXISTS nlp_keywords;
DROP TABLE IF EXISTS nlp_analysis_results;
DROP TABLE IF EXISTS nlp_feedback_templates;
DROP TABLE IF EXISTS materials;
DROP TABLE IF EXISTS questionnaire_results;
DROP TABLE IF EXISTS questionnaire_questions;
DROP TABLE IF EXISTS questionnaires;
DROP TABLE IF EXISTS student_quiz;
DROP TABLE IF EXISTS quizzes;
DROP TABLE IF EXISTS student_assignments;
DROP TABLE IF EXISTS assignments;
DROP TABLE IF EXISTS users;
