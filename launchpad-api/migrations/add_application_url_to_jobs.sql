-- Migration: Add application_url to job_opportunities table
-- Date: 2025-10-26
-- Purpose: Allow companies to add external application links for mobile app

-- Add application_url column
ALTER TABLE job_opportunities 
ADD COLUMN application_url VARCHAR(500) DEFAULT NULL COMMENT 'External application URL' 
AFTER salary_range;

-- Note: Run this on your existing database to add the new field
-- For XAMPP local: Run in phpMyAdmin or MySQL console
-- For Hostinger: Run in phpMyAdmin on production database
