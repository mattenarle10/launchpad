-- Migration: Add performance_score column to verified_students table
-- Date: 2025-10-14
-- Description: Adds performance_score column for partner companies to assess students with qualitative ratings

-- Add performance_score column if it doesn't exist
ALTER TABLE verified_students 
ADD COLUMN IF NOT EXISTS performance_score ENUM('Excellent', 'Good', 'Satisfactory', 'Needs Improvement', 'Poor') DEFAULT NULL COMMENT 'Company performance assessment';
