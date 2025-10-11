<?php

/**
 * Application Constants
 */

// Environment
define('ENV', 'development'); // development | production
define('DEBUG_MODE', ENV === 'development');

// API Configuration
define('API_VERSION', 'v1');
define('BASE_URL', 'http://localhost/LaunchPad/launchpad-api/public');

// Security
define('JWT_SECRET', 'your-secret-key-change-in-production'); // CHANGE THIS!
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRATION', 3600 * 24); // 24 hours
define('PASSWORD_MIN_LENGTH', 8);

// File Uploads
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// Pagination
define('DEFAULT_PAGE_SIZE', 20);
define('MAX_PAGE_SIZE', 100);

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_CDC', 'cdc');
define('ROLE_PC', 'pc');
define('ROLE_STUDENT', 'student');
define('ROLE_COMPANY', 'company');

// OJT Configuration
define('DEFAULT_OJT_HOURS', 500);

// Error Reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Timezone
date_default_timezone_set('Asia/Manila');

