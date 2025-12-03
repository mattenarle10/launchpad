-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 03, 2025 at 08:49 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `launchpad_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cdc_users`
--

CREATE TABLE `cdc_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_reports`
--

CREATE TABLE `daily_reports` (
  `report_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `report_date` date NOT NULL,
  `hours_requested` decimal(5,2) NOT NULL,
  `description` text NOT NULL,
  `activity_type` varchar(100) DEFAULT NULL,
  `report_file` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `evaluation_history`
--

CREATE TABLE `evaluation_history` (
  `evaluation_history_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `evaluation_rank` int(11) NOT NULL COMMENT 'Score 0-100',
  `performance_score` enum('Excellent','Good','Satisfactory','Needs Improvement','Poor') NOT NULL,
  `feedback` text DEFAULT NULL COMMENT 'Optional feedback from company',
  `evaluated_by` int(11) NOT NULL COMMENT 'Company user who submitted evaluation',
  `evaluation_date` timestamp NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `job_opportunities`
--

CREATE TABLE `job_opportunities` (
  `job_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `requirements` text DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `job_type` enum('Full-time','Part-time','Contract','Internship') DEFAULT 'Full-time',
  `salary_range` varchar(100) DEFAULT NULL,
  `application_url` varchar(500) DEFAULT NULL COMMENT 'External application URL',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tags` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `recipient_type` enum('all','specific') DEFAULT 'all',
  `sender_type` enum('cdc','company') NOT NULL DEFAULT 'cdc' COMMENT 'Type of sender',
  `created_by` int(11) DEFAULT NULL COMMENT 'CDC user ID if sent by CDC',
  `company_id` int(11) DEFAULT NULL COMMENT 'Company ID if sent by company',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_recipients`
--

CREATE TABLE `notification_recipients` (
  `id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ojt_progress`
--

CREATE TABLE `ojt_progress` (
  `progress_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `required_hours` int(11) NOT NULL DEFAULT 500,
  `completed_hours` decimal(6,2) NOT NULL DEFAULT 0.00,
  `status` enum('not_started','in_progress','completed') DEFAULT 'not_started',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_evaluations`
--

CREATE TABLE `student_evaluations` (
  `evaluation_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `evaluation_score` int(11) NOT NULL COMMENT 'Score from 0-100',
  `evaluation_period` enum('first_half','second_half') NOT NULL COMMENT 'First half (1-15) or second half (16-end) of month',
  `evaluation_month` int(11) NOT NULL COMMENT 'Month (1-12)',
  `evaluation_year` int(11) NOT NULL COMMENT 'Year (e.g., 2025)',
  `category` varchar(20) DEFAULT NULL COMMENT 'Excellent, Good, Enough, Poor, Very Poor based on score',
  `evaluated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `student_requirements`
--

CREATE TABLE `student_requirements` (
  `requirement_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `requirement_type` enum('pre_deployment','deployment','final_requirements') NOT NULL,
  `file_name` varchar(255) NOT NULL COMMENT 'Original filename uploaded by student',
  `file_path` varchar(255) NOT NULL COMMENT 'Stored filename on server',
  `file_size` int(11) NOT NULL COMMENT 'File size in bytes',
  `description` text DEFAULT NULL COMMENT 'Optional description of the requirement',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Student requirement submissions';

-- --------------------------------------------------------

--
-- Table structure for table `unverified_companies`
--

CREATE TABLE `unverified_companies` (
  `company_id` int(11) NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `contact_num` varchar(20) DEFAULT NULL,
  `address` text NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `moa_document` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `unverified_students`
--

CREATE TABLE `unverified_students` (
  `student_id` int(11) NOT NULL,
  `id_num` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `course` enum('IT','COMSCI','EMC') NOT NULL,
  `contact_num` varchar(20) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL COMMENT 'Student specialization/focus area',
  `password` varchar(255) NOT NULL,
  `cor` varchar(255) NOT NULL COMMENT 'Certificate of Registration',
  `company_name` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verified_companies`
--

CREATE TABLE `verified_companies` (
  `company_id` int(11) NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `contact_num` varchar(20) DEFAULT NULL,
  `address` text NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `moa_document` varchar(255) DEFAULT NULL,
  `verified_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verified_students`
--

CREATE TABLE `verified_students` (
  `student_id` int(11) NOT NULL,
  `id_num` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `course` enum('IT','COMSCI','EMC') NOT NULL,
  `contact_num` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `cor` varchar(255) NOT NULL COMMENT 'Certificate of Registration',
  `company_name` varchar(150) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `verified_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `evaluation_rank` int(11) DEFAULT NULL COMMENT 'Company evaluation rank (0-100)',
  `performance_score` enum('Excellent','Good','Satisfactory','Needs Improvement','Poor') DEFAULT NULL COMMENT 'Company performance assessment',
  `specialization` varchar(100) DEFAULT NULL COMMENT 'Student specialization/focus area'
) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cdc_users`
--
ALTER TABLE `cdc_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `daily_reports`
--
ALTER TABLE `daily_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `idx_student_status` (`student_id`,`status`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `evaluation_history`
--
ALTER TABLE `evaluation_history`
  ADD PRIMARY KEY (`evaluation_history_id`),
  ADD KEY `idx_student_evaluations` (`student_id`,`evaluation_date`),
  ADD KEY `idx_company_evaluations` (`company_id`,`evaluation_date`);

--
-- Indexes for table `job_opportunities`
--
ALTER TABLE `job_opportunities`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `idx_company_active` (`company_id`,`is_active`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_company_notifications` (`company_id`,`created_at`);

--
-- Indexes for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notification_id` (`notification_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `ojt_progress`
--
ALTER TABLE `ojt_progress`
  ADD PRIMARY KEY (`progress_id`),
  ADD UNIQUE KEY `unique_student_progress` (`student_id`);

--
-- Indexes for table `student_evaluations`
--
ALTER TABLE `student_evaluations`
  ADD PRIMARY KEY (`evaluation_id`),
  ADD UNIQUE KEY `unique_evaluation` (`student_id`,`company_id`,`evaluation_period`,`evaluation_month`,`evaluation_year`),
  ADD KEY `idx_student_evaluations` (`student_id`,`evaluation_year`,`evaluation_month`),
  ADD KEY `idx_company_evaluations` (`company_id`,`evaluation_year`,`evaluation_month`);

--
-- Indexes for table `student_requirements`
--
ALTER TABLE `student_requirements`
  ADD PRIMARY KEY (`requirement_id`),
  ADD KEY `idx_student_type` (`student_id`,`requirement_type`),
  ADD KEY `idx_type` (`requirement_type`),
  ADD KEY `idx_submitted` (`submitted_at`);

--
-- Indexes for table `unverified_companies`
--
ALTER TABLE `unverified_companies`
  ADD PRIMARY KEY (`company_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `unverified_students`
--
ALTER TABLE `unverified_students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `id_num` (`id_num`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `verified_companies`
--
ALTER TABLE `verified_companies`
  ADD PRIMARY KEY (`company_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `verified_students`
--
ALTER TABLE `verified_students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `id_num` (`id_num`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `company_id` (`company_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cdc_users`
--
ALTER TABLE `cdc_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_reports`
--
ALTER TABLE `daily_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `evaluation_history`
--
ALTER TABLE `evaluation_history`
  MODIFY `evaluation_history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_opportunities`
--
ALTER TABLE `job_opportunities`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ojt_progress`
--
ALTER TABLE `ojt_progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_evaluations`
--
ALTER TABLE `student_evaluations`
  MODIFY `evaluation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_requirements`
--
ALTER TABLE `student_requirements`
  MODIFY `requirement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `unverified_companies`
--
ALTER TABLE `unverified_companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `unverified_students`
--
ALTER TABLE `unverified_students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `verified_companies`
--
ALTER TABLE `verified_companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `verified_students`
--
ALTER TABLE `verified_students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `daily_reports`
--
ALTER TABLE `daily_reports`
  ADD CONSTRAINT `daily_reports_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `verified_students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `daily_reports_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `cdc_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `evaluation_history`
--
ALTER TABLE `evaluation_history`
  ADD CONSTRAINT `evaluation_history_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `verified_students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluation_history_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `verified_companies` (`company_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_opportunities`
--
ALTER TABLE `job_opportunities`
  ADD CONSTRAINT `job_opportunities_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `verified_companies` (`company_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_company` FOREIGN KEY (`company_id`) REFERENCES `verified_companies` (`company_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `cdc_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  ADD CONSTRAINT `notification_recipients_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`notification_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_recipients_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `verified_students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `ojt_progress`
--
ALTER TABLE `ojt_progress`
  ADD CONSTRAINT `ojt_progress_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `verified_students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_evaluations`
--
ALTER TABLE `student_evaluations`
  ADD CONSTRAINT `student_evaluations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `verified_students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_evaluations_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `verified_companies` (`company_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_requirements`
--
ALTER TABLE `student_requirements`
  ADD CONSTRAINT `student_requirements_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `verified_students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `verified_students`
--
ALTER TABLE `verified_students`
  ADD CONSTRAINT `verified_students_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `verified_companies` (`company_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
