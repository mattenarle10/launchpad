-- Migration: Rename id_photo to cor (Certificate of Registration)
-- Run this if you already have existing tables

USE launchpad_db;

-- Rename column in unverified_students
ALTER TABLE unverified_students 
CHANGE COLUMN id_photo cor VARCHAR(255) NOT NULL COMMENT 'Certificate of Registration';

-- Rename column in verified_students
ALTER TABLE verified_students 
CHANGE COLUMN id_photo cor VARCHAR(255) NOT NULL COMMENT 'Certificate of Registration';

-- Note: You may also want to rename the upload directory
-- From: uploads/student_ids/
-- To: uploads/student_cors/

