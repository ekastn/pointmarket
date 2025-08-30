-- +goose Up
-- +goose StatementBegin
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'siswa', 'guru') NOT NULL DEFAULT 'siswa',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE user_profiles (
    user_id BIGINT PRIMARY KEY,
    avatar_url VARCHAR(255),
    bio TEXT,
    metadata JSON,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Faculties/Programs/Students moved earlier to support FKs in later migrations
CREATE TABLE faculties (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE programs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    faculty_id BIGINT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_programs_faculty FOREIGN KEY (faculty_id) REFERENCES faculties(id)
);

CREATE INDEX idx_programs_faculty ON programs (faculty_id);

-- Students: student_id (VARCHAR) is the PK; user_id is unique FK to users
CREATE TABLE students (
    student_id VARCHAR(32) PRIMARY KEY,
    user_id BIGINT NOT NULL UNIQUE,
    program_id BIGINT NOT NULL,
    cohort_year INT NULL,
    status ENUM('active','leave','graduated','dropped') NOT NULL DEFAULT 'active',
    birth_date DATE NULL,
    gender ENUM('M','F','Other') NULL,
    phone VARCHAR(32) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_students_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_students_program FOREIGN KEY (program_id) REFERENCES programs(id)
);

CREATE INDEX idx_students_program ON students (program_id);
CREATE INDEX idx_students_status  ON students (status);
CREATE INDEX idx_students_cohort  ON students (cohort_year);

-- Learning styles now tied to students(student_id)
CREATE TABLE student_learning_styles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(32) NOT NULL,
    type ENUM('dominant', 'multimodal') NOT NULL,
    label VARCHAR(50) NOT NULL,
    score_visual DECIMAL(5, 4),
    score_auditory DECIMAL(5, 4),
    score_reading DECIMAL(5, 4),
    score_kinesthetic DECIMAL(5, 4),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_student_learning_styles_student FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);
-- +goose StatementEnd

-- +goose Down
-- +goose StatementBegin
DROP TABLE IF EXISTS student_learning_styles;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS programs;
DROP TABLE IF EXISTS faculties;
DROP TABLE IF EXISTS user_profiles;
DROP TABLE IF EXISTS users;
-- +goose StatementEnd
