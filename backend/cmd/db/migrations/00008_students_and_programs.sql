-- +goose Up
-- +goose StatementBegin

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

CREATE TABLE students (
    user_id BIGINT PRIMARY KEY,
    student_id VARCHAR(32) NOT NULL UNIQUE, -- institutional identifier (formerly NIM)
    program_id BIGINT NOT NULL,             -- FK to programs
    cohort_year INT NULL,                   -- angkatan (e.g., 2023)
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
-- +goose StatementEnd

-- +goose Down
-- +goose StatementBegin
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS programs;
DROP TABLE IF EXISTS faculties;
-- +goose StatementEnd

