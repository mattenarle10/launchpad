-- Migration: Add Specialization Tag and Evaluation History
-- Date: 2025-10-18

USE launchpad_db;

-- Add specialization field to verified_students
ALTER TABLE verified_students 
ADD COLUMN specialization VARCHAR(100) DEFAULT NULL COMMENT 'Student specialization/focus area' 
AFTER performance_score;

-- Add specialization field to unverified_students
ALTER TABLE unverified_students 
ADD COLUMN specialization VARCHAR(100) DEFAULT NULL COMMENT 'Student specialization/focus area' 
AFTER contact_num;

-- Create evaluation_history table to track all evaluations
CREATE TABLE IF NOT EXISTS evaluation_history (
    evaluation_history_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    company_id INT NOT NULL,
    evaluation_rank INT NOT NULL COMMENT 'Score 0-100',
    performance_score ENUM('Excellent', 'Good', 'Satisfactory', 'Needs Improvement', 'Poor') NOT NULL,
    feedback TEXT DEFAULT NULL COMMENT 'Optional feedback from company',
    evaluated_by INT NOT NULL COMMENT 'Company user who submitted evaluation',
    evaluation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES verified_students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES verified_companies(company_id) ON DELETE CASCADE,
    INDEX idx_student_evaluations (student_id, evaluation_date DESC),
    INDEX idx_company_evaluations (company_id, evaluation_date DESC),
    CHECK (evaluation_rank >= 0 AND evaluation_rank <= 100)
);
