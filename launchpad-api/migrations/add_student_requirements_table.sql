-- Migration: Add student_requirements table
-- Purpose: Track student requirement submissions for pre-deployment, deployment, and final requirements
-- Date: 2025-10-26

-- Create student_requirements table
CREATE TABLE IF NOT EXISTS student_requirements (
    requirement_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    requirement_type ENUM('pre_deployment', 'deployment', 'final_requirements') NOT NULL,
    file_name VARCHAR(255) NOT NULL COMMENT 'Original filename uploaded by student',
    file_path VARCHAR(255) NOT NULL COMMENT 'Stored filename on server',
    file_size INT NOT NULL COMMENT 'File size in bytes',
    description TEXT DEFAULT NULL COMMENT 'Optional description of the requirement',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES verified_students(student_id) ON DELETE CASCADE,
    INDEX idx_student_type (student_id, requirement_type),
    INDEX idx_type (requirement_type),
    INDEX idx_submitted (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Student requirement submissions';
