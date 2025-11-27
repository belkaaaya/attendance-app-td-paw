-- Create database
CREATE DATABASE IF NOT EXISTS attendance_sessions;
USE attendance_sessions;

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    matricule VARCHAR(20) NOT NULL UNIQUE,
    group_id VARCHAR(20) NOT NULL DEFAULT 'CS101',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_matricule (matricule),
    INDEX idx_group (group_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attendance sessions table
CREATE TABLE IF NOT EXISTS attendance_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id VARCHAR(20) NOT NULL,
    group_id VARCHAR(20) NOT NULL,
    date DATE NOT NULL,
    opened_by VARCHAR(50) NOT NULL,
    status ENUM('active', 'closed', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_session (course_id, group_id, date),
    INDEX idx_course (course_id),
    INDEX idx_group (group_id),
    INDEX idx_date (date),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample students
INSERT INTO students (fullname, matricule, group_id) VALUES
('Rania Houcine', 'STD001', 'CS101'),
('Ahmed Sara', 'STD002', 'CS101'),
('Yacine Ali', 'STD003', 'CS102');

-- Insert sample attendance sessions
INSERT INTO attendance_sessions (course_id, group_id, date, opened_by, status) VALUES
('AWP', 'WEB3', CURDATE(), 'professor_john', 'active'),
('PHP', 'ISIL', CURDATE(), 'professor_smith', 'active'),
('JavaScript', 'Master', CURDATE(), 'assistant_mary', 'closed');
