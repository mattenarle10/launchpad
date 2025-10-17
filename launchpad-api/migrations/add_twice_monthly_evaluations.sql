-- Migration: Add twice-monthly evaluation system
-- Date: 2025-10-18
-- Description: Allow companies to evaluate students twice per month (1st-15th and 16th-end)

-- Create student_evaluations table for tracking twice-monthly evaluations
CREATE TABLE IF NOT EXISTS student_evaluations (
    evaluation_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    company_id INT NOT NULL,
    evaluation_score INT NOT NULL COMMENT 'Score from 0-100',
    evaluation_period ENUM('first_half', 'second_half') NOT NULL COMMENT 'First half (1-15) or second half (16-end) of month',
    evaluation_month INT NOT NULL COMMENT 'Month (1-12)',
    evaluation_year INT NOT NULL COMMENT 'Year (e.g., 2025)',
    category VARCHAR(20) DEFAULT NULL COMMENT 'Excellent, Good, Enough, Poor, Very Poor based on score',
    evaluated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES verified_students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES verified_companies(company_id) ON DELETE CASCADE,
    UNIQUE KEY unique_evaluation (student_id, company_id, evaluation_period, evaluation_month, evaluation_year),
    CHECK (evaluation_score >= 0 AND evaluation_score <= 100),
    CHECK (evaluation_month >= 1 AND evaluation_month <= 12),
    INDEX idx_student_evaluations (student_id, evaluation_year, evaluation_month),
    INDEX idx_company_evaluations (company_id, evaluation_year, evaluation_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Keep evaluation_rank in verified_students for backward compatibility
-- It will now store the average of all evaluations

-- Evaluation Categories Reference (calculated in PHP):
-- 81-100: Excellent
-- 61-80:  Good
-- 41-60:  Enough
-- 21-40:  Poor
-- 0-20:   Very Poor
