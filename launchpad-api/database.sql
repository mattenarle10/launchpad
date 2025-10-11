-- LaunchPad Database Schema
-- Phase 1: Student Registration & Authentication

-- Create database
CREATE DATABASE IF NOT EXISTS launchpad_db;
USE launchpad_db;

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
    id_photo VARCHAR(255) NOT NULL,
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
    id_photo VARCHAR(255) NOT NULL,
    company_name VARCHAR(150) DEFAULT NULL,
    profile_pic VARCHAR(255) DEFAULT NULL,
    verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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

-- Insert test CDC admin (password: admin123)
INSERT INTO cdc_users (username, email, first_name, last_name, password) VALUES 
('cdc_admin', 'cdc@launchpad.com', 'CDC', 'Admin', '$2y$12$tb.YAahiGjhdso.2l8AEbuCmzuhUcIPj2ccuNXE9gZ5Oay3AU.Gve')
ON DUPLICATE KEY UPDATE password='$2y$12$tb.YAahiGjhdso.2l8AEbuCmzuhUcIPj2ccuNXE9gZ5Oay3AU.Gve';

-- Insert test verified student (password: student123)
INSERT INTO verified_students (id_num, first_name, last_name, email, contact_num, course, password, id_photo) VALUES 
('2021-00001', 'Juan', 'Dela Cruz', 'juan@student.com', '09123456789', 'IT', '$2y$12$tb.YAahiGjhdso.2l8AEbuCmzuhUcIPj2ccuNXE9gZ5Oay3AU.Gve', 'test_id.jpg')
ON DUPLICATE KEY UPDATE password='$2y$12$tb.YAahiGjhdso.2l8AEbuCmzuhUcIPj2ccuNXE9gZ5Oay3AU.Gve';

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

-- Insert test verified company (password: company123)
INSERT INTO verified_companies (company_name, username, email, contact_num, address, website, password) VALUES 
('Acme Corp', 'acme_corp', 'contact@acme.com', '09123456789', '123 Business St, Cebu City', 'https://acme.com', '$2y$12$tb.YAahiGjhdso.2l8AEbuCmzuhUcIPj2ccuNXE9gZ5Oay3AU.Gve')
ON DUPLICATE KEY UPDATE password='$2y$12$tb.YAahiGjhdso.2l8AEbuCmzuhUcIPj2ccuNXE9gZ5Oay3AU.Gve';

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

-- Create OJT progress for test student
INSERT INTO ojt_progress (student_id, required_hours, completed_hours, status, start_date) 
SELECT student_id, 500, 16, 'in_progress', '2025-01-15' FROM verified_students WHERE id_num = '2021-00001'
ON DUPLICATE KEY UPDATE student_id=student_id;

-- Add sample reports for test student
INSERT INTO daily_reports (student_id, report_date, hours_requested, description, activity_type, report_file, status, reviewed_at)
SELECT student_id, '2025-01-15', 8, 'Initial orientation and setup', 'Training', 'report_test_1.pdf', 'approved', NOW()
FROM verified_students WHERE id_num = '2021-00001';

INSERT INTO daily_reports (student_id, report_date, hours_requested, description, activity_type, report_file, status, reviewed_at)
SELECT student_id, '2025-01-16', 8, 'Frontend development tasks', 'Development', 'report_test_2.pdf', 'approved', NOW()
FROM verified_students WHERE id_num = '2021-00001';

-- Add pending report
INSERT INTO daily_reports (student_id, report_date, hours_requested, description, activity_type, report_file, status)
SELECT student_id, '2025-01-17', 7.5, 'Backend API development', 'Development', 'report_test_3.pdf', 'pending'
FROM verified_students WHERE id_num = '2021-00001';
