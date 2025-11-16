-- Migration: Add company notification support
-- Date: 2025-11-16
-- Purpose: Allow companies to send notifications (job postings, evaluations) to students

-- Add sender_type column to distinguish between CDC and company notifications
ALTER TABLE notifications 
ADD COLUMN sender_type ENUM('cdc', 'company') NOT NULL DEFAULT 'cdc' COMMENT 'Type of sender' 
AFTER recipient_type;

-- Add company_id column for company-sent notifications
ALTER TABLE notifications 
ADD COLUMN company_id INT DEFAULT NULL COMMENT 'Company ID if sent by company' 
AFTER created_by;

-- Add foreign key for company_id
ALTER TABLE notifications 
ADD CONSTRAINT fk_notifications_company 
FOREIGN KEY (company_id) REFERENCES verified_companies(company_id) ON DELETE CASCADE;

-- Add index for company notifications
ALTER TABLE notifications 
ADD INDEX idx_company_notifications (company_id, created_at);

-- Update existing notifications to have sender_type='cdc'
UPDATE notifications SET sender_type = 'cdc' WHERE created_by IS NOT NULL;

-- Make created_by nullable since company notifications won't have a CDC user
ALTER TABLE notifications 
MODIFY COLUMN created_by INT DEFAULT NULL COMMENT 'CDC user ID if sent by CDC';

-- Note: Run this on your existing database
-- For XAMPP local: Run in phpMyAdmin or MySQL console
-- For Hostinger: Run in phpMyAdmin on production database

