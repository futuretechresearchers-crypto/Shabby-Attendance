CREATE DATABASE IF NOT EXISTS school_attendance
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE school_attendance;

DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    UNIQUE INDEX unique_username (username)
) ENGINE=InnoDB;

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_number VARCHAR(30) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    course VARCHAR(100) NOT NULL,
    UNIQUE INDEX unique_student_number (student_number),
    INDEX idx_student_number (student_number),
    INDEX idx_student_name (last_name, first_name)
) ENGINE=InnoDB;

CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('Present', 'Absent', 'Late') NOT NULL,
    UNIQUE INDEX unique_student_date (student_id, attendance_date),
    INDEX idx_attendance_date (attendance_date),
    INDEX idx_attendance_student (student_id),
    CONSTRAINT fk_attendance_student
        FOREIGN KEY (student_id) REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Password is the PHP password_hash() value for: admin123
INSERT INTO users (username, password)
VALUES (
    'admin',
    '$2y$12$YI3OnYJkq1DqABaA0IXhBOAkY06qdbInkYEVu/vFqVP72Mpzn.CT.'
);

INSERT INTO students
(student_number, first_name, last_name, course)
VALUES
('2026-001', 'Juan', 'Dela Cruz', 'BS Information Technology'),
('2026-002', 'Maria', 'Santos', 'BS Information Technology'),
('2026-003', 'Pedro', 'Garcia', 'BS Computer Science'),
('2026-004', 'Ana', 'Reyes', 'BS Information Technology'),
('2026-005', 'Mark', 'Torres', 'BS Computer Science');

INSERT INTO attendance
(student_id, attendance_date, status)
VALUES
(1, '2026-08-20', 'Present'),
(2, '2026-08-20', 'Absent'),
(3, '2026-08-20', 'Present');
