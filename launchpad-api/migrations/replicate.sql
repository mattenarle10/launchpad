-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 17, 2025 at 09:30 PM
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

--
-- Dumping data for table `cdc_users`
--

INSERT INTO `cdc_users` (`id`, `username`, `email`, `first_name`, `last_name`, `password`, `role`, `created_at`) VALUES
(3, 'cdc_admin', 'anthony.gallego@cdc.edu', 'Anthony', 'Gallego', '$2y$12$ymCEOiCW0XCFmzBC0V3DvuykW0s0WjUINkXOreeGEsqGpsCdo/8w6', 'cdc', '2025-10-13 22:49:39');

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

--
-- Dumping data for table `daily_reports`
--

INSERT INTO `daily_reports` (`report_id`, `student_id`, `report_date`, `hours_requested`, `description`, `activity_type`, `report_file`, `status`, `reviewed_by`, `reviewed_at`, `rejection_reason`, `submitted_at`) VALUES
(1, 1, '2025-10-14', 8.00, 'Daily OJT Activities', 'Daily Activities', 'daily_report_1_2025-10-14_1760398171.jpg', 'approved', 3, '2025-10-13 23:44:07', NULL, '2025-10-13 23:29:31'),
(2, 2, '2025-10-14', 8.00, 'Daily OJT Activities', 'OJT Summary', 'daily_report_2_2025-10-14_1760412659.jpg', 'approved', 3, '2025-10-14 03:31:27', NULL, '2025-10-14 03:30:59'),
(3, 3, '2025-10-14', 8.00, 'hehe', 'wala lanh', 'daily_report_3_2025-10-14_1760416255.jpg', 'approved', 3, '2025-10-14 04:31:24', NULL, '2025-10-14 04:30:55'),
(4, 3, '2025-10-08', 8.00, 'jehehe', 'anotber', 'daily_report_3_2025-10-08_1760416480.jpg', 'approved', 3, '2025-10-14 04:35:42', NULL, '2025-10-14 04:34:40');

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
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tags` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_opportunities`
--

INSERT INTO `job_opportunities` (`job_id`, `company_id`, `title`, `description`, `requirements`, `location`, `job_type`, `salary_range`, `is_active`, `created_at`, `updated_at`, `tags`) VALUES
(2, 1, 'FULL STACK DEVELOPER', 'Proficient in frontend and backend development, knows mobile dev', 'CS Grad', 'Bacolod City', 'Part-time', '25000', 1, '2025-10-14 02:50:30', '2025-10-17 17:24:35', 'Database Administration, QA/Testing'),
(3, 2, 'Ethical Hacker', 'hehehe', 'gogogogo', 'Cebu', 'Full-time', '50000', 1, '2025-10-14 02:51:28', '2025-10-17 18:43:23', 'Backend Development, Frontend Development, Machine Learning, Cybersecurity, Embedded Systems'),
(5, 4, 'Mamama Maker', 'hehehe', 'damoooo', 'Cebu', 'Full-time', '50000', 1, '2025-10-17 16:29:55', '2025-10-17 16:29:59', 'UI/UX Design, Full Stack, Embedded Systems');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `recipient_type` enum('all','specific') DEFAULT 'all',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `title`, `message`, `recipient_type`, `created_by`, `created_at`) VALUES
(2, 'aaa', 'adsasdasdsa', 'all', 3, '2025-10-14 04:13:51'),
(3, 'heeelelele', 'hehehehehe', 'all', 3, '2025-10-14 04:16:14'),
(4, 'Hello everyon', 'tnagina', 'all', 3, '2025-10-14 04:24:58'),
(5, 'aaa', 'bbbb', 'all', 3, '2025-10-14 04:31:41'),
(6, 'HEllo', 'jjajaa', 'specific', 3, '2025-10-17 18:04:47'),
(7, 'Heeeyy', 'hahaha', 'specific', 3, '2025-10-17 18:14:50'),
(8, 'Hello nao', 'naonaonaoanaonoa', 'specific', 3, '2025-10-17 18:45:56');

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

--
-- Dumping data for table `notification_recipients`
--

INSERT INTO `notification_recipients` (`id`, `notification_id`, `student_id`, `is_read`, `read_at`) VALUES
(1, 6, 3, 1, '2025-10-17 18:34:59'),
(2, 7, 3, 1, '2025-10-17 18:34:56'),
(3, 8, 2, 0, NULL);

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

--
-- Dumping data for table `ojt_progress`
--

INSERT INTO `ojt_progress` (`progress_id`, `student_id`, `required_hours`, `completed_hours`, `status`, `start_date`, `end_date`, `last_updated`) VALUES
(1, 1, 500, 8.50, 'in_progress', '2025-10-14', NULL, '2025-10-14 01:39:23'),
(2, 2, 500, 108.00, 'in_progress', '2025-10-14', NULL, '2025-10-14 03:31:27'),
(3, 3, 500, 22.50, 'in_progress', '2025-10-14', NULL, '2025-10-14 04:35:42');

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

--
-- Dumping data for table `student_evaluations`
--

INSERT INTO `student_evaluations` (`evaluation_id`, `student_id`, `company_id`, `evaluation_score`, `evaluation_period`, `evaluation_month`, `evaluation_year`, `category`, `evaluated_at`) VALUES
(1, 1, 1, 44, 'second_half', 10, 2025, 'Enough', '2025-10-17 17:26:40'),
(2, 1, 1, 88, 'first_half', 10, 2025, 'Excellent', '2025-10-17 17:21:21'),
(3, 2, 1, 99, 'first_half', 10, 2025, 'Excellent', '2025-10-17 17:21:55'),
(4, 3, 2, 77, 'second_half', 10, 2025, 'Good', '2025-10-17 18:31:34'),
(5, 3, 2, 88, 'first_half', 10, 2025, 'Excellent', '2025-10-17 18:42:13');

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

--
-- Dumping data for table `unverified_students`
--

INSERT INTO `unverified_students` (`student_id`, `id_num`, `first_name`, `last_name`, `email`, `course`, `contact_num`, `specialization`, `password`, `cor`, `company_name`, `created_at`) VALUES
(5, '2121123', 'Adiel', 'Mari', 's2121123@usls.edu.ph', 'IT', '12345678901', NULL, '$2y$10$McIlK/M7jxFhTKtg2wwWveDjfQ/bcGMwoP3wxRPN9297ALr.U5qGq', 'cor_2121123_1760426652.jpeg', NULL, '2025-10-14 07:24:12');

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

--
-- Dumping data for table `verified_companies`
--

INSERT INTO `verified_companies` (`company_id`, `company_name`, `username`, `email`, `contact_num`, `address`, `website`, `password`, `company_logo`, `moa_document`, `verified_at`) VALUES
(1, 'Ubiquity', 'ubiquityph', 'ubiquity@gmail.com', '09886667777', 'Bacolod City PH', 'ubiquity.com', '$2y$10$.KhW.8zBgiCgzGAfezUrjOkOPa/Q5YkP58dkFIv0pnL8SdIbKE/y.', 'company_1_1760405169.png', 'moa_ubiquityph_1760396320.png', '2025-10-13 23:00:23'),
(2, 'concentrix', 'concentrixph', 'concentrix@gmail.com', '09773189440', 'bacolod mandalagan  ph', 'concentrix.com', '$2y$10$CeccWLormVMSdy.HjK45su/BbC7EZjgHYNCX4jOQUxkrP5NQ/iofq', 'logo_concentrixph_1760396987.png', 'moa_concentrixph_1760396987.png', '2025-10-13 23:11:48'),
(3, 'IReply', 'ireply.bcd', 'ireply@gmail.com', '09782221321', 'Bacolod City', 'ireply.com', '$2y$10$BFK76N7gJgiAFXNCMZCH9.qXi5E7Q87v.2LyDcCqX9nlyo2NqGRma', 'logo_ireply.bcd_1760412918.png', 'moa_ireply.bcd_1760412918.png', '2025-10-14 03:35:40'),
(4, 'convergeph', 'convergeph', 'converge@gmail.com', '98123245678', 'enenenene', 'converge.com', '$2y$10$Ufpw57gfFMVagzo/yWW3.e4p0ENEu3moFtjvAw1ssJzeyAyJ8LhFa', 'logo_convergeph_1760718502.png', 'moa_convergeph_1760718502.png', '2025-10-17 16:28:53');

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
-- Dumping data for table `verified_students`
--

INSERT INTO `verified_students` (`student_id`, `id_num`, `first_name`, `last_name`, `email`, `course`, `contact_num`, `password`, `cor`, `company_name`, `company_id`, `profile_pic`, `verified_at`, `evaluation_rank`, `performance_score`, `specialization`) VALUES
(1, '0800999', 'Hehe', 'Cruz', 'student@gmail.com', 'IT', '09112223455', '$2y$10$rmAvnHbScfEVg0DpNdtgUObGof8TpogiHTfncKEBwPTxb0pDWib8.', 'cor_0800999_1760394628.jpg', 'Ubiquity', 1, NULL, '2025-10-13 23:06:53', 66, 'Good', NULL),
(2, '2010551', 'Naomi', 'Liu', 'nao@gmail.com', 'IT', '09682587931', '$2y$10$Dihw656Y38dcec8tlOKwW.Fbobq4hTFSWOeCaOHBDnS7GyOLy2Fzu', 'cor_2010551_1760412430.jpg', 'IReply', 1, NULL, '2025-10-14 03:27:45', 99, 'Excellent', NULL),
(3, '0800466', 'matt', 'enarle', 'enarlem10@gmail.com', 'EMC', '09773189440', '$2y$10$NpaSIuaKZIPMr11k6ADz7u4itaPSWMhFychzR8FHT7ByQsM0S0PVi', 'cor_0800466_1760416166.jpg', 'concentrix', 2, NULL, '2025-10-14 04:29:41', 83, 'Excellent', 'Game Development, Data Science, UI/UX Design, Cybersecurity');

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
  ADD KEY `created_by` (`created_by`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `daily_reports`
--
ALTER TABLE `daily_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `evaluation_history`
--
ALTER TABLE `evaluation_history`
  MODIFY `evaluation_history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_opportunities`
--
ALTER TABLE `job_opportunities`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ojt_progress`
--
ALTER TABLE `ojt_progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_evaluations`
--
ALTER TABLE `student_evaluations`
  MODIFY `evaluation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `unverified_companies`
--
ALTER TABLE `unverified_companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `unverified_students`
--
ALTER TABLE `unverified_students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `verified_companies`
--
ALTER TABLE `verified_companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- Constraints for table `verified_students`
--
ALTER TABLE `verified_students`
  ADD CONSTRAINT `verified_students_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `verified_companies` (`company_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
