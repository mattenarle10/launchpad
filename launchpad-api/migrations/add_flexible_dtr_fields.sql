-- Migration: Add company validation for DTR (PC approves hours instead of CDC)
-- Students can request flexible hours, PC validates and can adjust (+/-)

-- Add company validation fields to daily_reports
-- PC (Partner Company) will validate DTR hours instead of CDC
ALTER TABLE `daily_reports`
ADD COLUMN `hours_approved` DECIMAL(5,2) NULL AFTER `hours_requested`,
ADD COLUMN `company_validated` TINYINT(1) DEFAULT 0 AFTER `rejection_reason`,
ADD COLUMN `company_validated_at` TIMESTAMP NULL AFTER `company_validated`,
ADD COLUMN `company_validated_by` INT NULL AFTER `company_validated_at`,
ADD COLUMN `company_remarks` TEXT NULL AFTER `company_validated_by`;

-- Add index for company validation queries
CREATE INDEX `idx_company_validation` ON `daily_reports` (`student_id`, `company_validated`);

-- Add foreign key for company validator
ALTER TABLE `daily_reports`
ADD CONSTRAINT `fk_company_validator` FOREIGN KEY (`company_validated_by`) 
REFERENCES `verified_companies` (`company_id`) ON DELETE SET NULL;
