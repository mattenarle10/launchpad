-- Migration: Add semester and academic year tracking to students
-- Run this migration to enable filtering students by semester/academic year

-- Add columns to verified_students
ALTER TABLE `verified_students` 
ADD COLUMN `semester` ENUM('1st', '2nd', 'summer') DEFAULT '1st' AFTER `specialization`,
ADD COLUMN `academic_year` VARCHAR(20) DEFAULT '2024-2025' AFTER `semester`;

-- Add columns to unverified_students  
ALTER TABLE `unverified_students`
ADD COLUMN `semester` ENUM('1st', '2nd', 'summer') DEFAULT '1st' AFTER `specialization`,
ADD COLUMN `academic_year` VARCHAR(20) DEFAULT '2024-2025' AFTER `semester`;

-- Create index for faster filtering
CREATE INDEX `idx_semester_year` ON `verified_students` (`semester`, `academic_year`);
