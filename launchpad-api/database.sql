-- LaunchPad Database Schema
-- Phase 1: Student Registration & Authentication

-- Create database
CREATE DATABASE IF NOT EXISTS launchpad_db;
USE launchpad_db;

-- CDC Users (Career Development Center staff)
CREATE TABLE IF NOT EXISTS cdc_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Unverified Companies (pending CDC approval)
CREATE TABLE IF NOT EXISTS unverified_companies (
    company_id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(150) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    contact_num VARCHAR(20),
    address TEXT NOT NULL,
    website VARCHAR(255),
    password VARCHAR(255) NOT NULL,
    company_logo VARCHAR(255) DEFAULT NULL,
    moa_document VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Verified Companies (approved by CDC)
CREATE TABLE IF NOT EXISTS verified_companies (
    company_id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(150) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    contact_num VARCHAR(20),
    address TEXT NOT NULL,
    website VARCHAR(255),
    password VARCHAR(255) NOT NULL,
    company_logo VARCHAR(255) DEFAULT NULL,
    moa_document VARCHAR(255) DEFAULT NULL,
    verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Unverified Students (pending CDC approval)
CREATE TABLE IF NOT EXISTS unverified_students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    id_num VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    course ENUM('IT', 'COMSCI', 'EMC') NOT NULL,
    contact_num VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    cor VARCHAR(255) NOT NULL COMMENT 'Certificate of Registration',
    company_name VARCHAR(150) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Verified Students (approved by CDC)
CREATE TABLE IF NOT EXISTS verified_students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    id_num VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    course ENUM('IT', 'COMSCI', 'EMC') NOT NULL,
    contact_num VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    cor VARCHAR(255) NOT NULL COMMENT 'Certificate of Registration',
    company_name VARCHAR(150) DEFAULT NULL,
    company_id INT DEFAULT NULL,
    profile_pic VARCHAR(255) DEFAULT NULL,
    evaluation_rank INT DEFAULT NULL COMMENT 'Company evaluation rank (0-100)',
    performance_score ENUM('Excellent', 'Good', 'Satisfactory', 'Needs Improvement', 'Poor') DEFAULT NULL COMMENT 'Company performance assessment',
    verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES verified_companies(company_id) ON DELETE SET NULL,
    CHECK (evaluation_rank IS NULL OR (evaluation_rank >= 0 AND evaluation_rank <= 100))
);

-- OJT Progress Tracking (Phase 3)
CREATE TABLE IF NOT EXISTS ojt_progress (
    progress_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    required_hours INT NOT NULL DEFAULT 500,
    completed_hours DECIMAL(6,2) NOT NULL DEFAULT 0,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES verified_students(student_id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_progress (student_id)
);

-- Daily Reports Submission (with hours request)
CREATE TABLE IF NOT EXISTS daily_reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    report_date DATE NOT NULL,
    hours_requested DECIMAL(5,2) NOT NULL,
    description TEXT NOT NULL,
    activity_type VARCHAR(100),
    report_file VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reviewed_by INT DEFAULT NULL,
    reviewed_at TIMESTAMP NULL,
    rejection_reason TEXT DEFAULT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES verified_students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES cdc_users(id) ON DELETE SET NULL,
    INDEX idx_student_status (student_id, status),
    INDEX idx_status (status)
);

-- Job Opportunities posted by partner companies
CREATE TABLE IF NOT EXISTS job_opportunities (
    job_id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT,
    location VARCHAR(200),
    job_type ENUM('Full-time', 'Part-time', 'Contract', 'Internship') DEFAULT 'Full-time',
    salary_range VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES verified_companies(company_id) ON DELETE CASCADE,
    INDEX idx_company_active (company_id, is_active),
    INDEX idx_active (is_active)
);

-- Insert default CDC admin account (Anthony Gallego)
-- Default password: admin123 
INSERT INTO cdc_users (username, email, first_name, last_name, password, role)
VALUES (
    'cdc_admin',
    'anthony.gallego@cdc.edu',
    'Anthony',
    'Gallego',
    '$2y$10$3euPcmQFCiblsZFlK4NzZOuohxdNf9Y1yLCEKVkfRNjSQTKKW8lmu',
    'cdc'
);
