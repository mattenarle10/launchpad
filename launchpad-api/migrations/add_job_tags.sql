-- Migration: Add tags/specialization to job_opportunities table
-- Date: 2025-10-15

-- Add tags column to store comma-separated specialization tags
ALTER TABLE job_opportunities 
ADD COLUMN tags VARCHAR(500) DEFAULT NULL 
COMMENT 'Comma-separated tech specialization tags (e.g., UI/UX, Web Development, Mobile Development)';

-- Add index for better search performance
ALTER TABLE job_opportunities 
ADD INDEX idx_tags (tags);

-- Sample tags that can be used:
-- UI/UX Design, Web Development, Mobile Development, Backend Development, 
-- Frontend Development, Full Stack, DevOps, Data Science, Machine Learning,
-- Cybersecurity, Cloud Computing, Database Administration, QA/Testing,
-- Game Development, Embedded Systems, Network Engineering
