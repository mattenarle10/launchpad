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
