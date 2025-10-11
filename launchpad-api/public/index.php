<?php

/**
 * LaunchPad API Entry Point
 * Modern RESTful API for OJT Tracking System
 */

// Load configuration
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

// Load utilities
require_once __DIR__ . '/../src/Utils/Response.php';

// Load middleware
require_once __DIR__ . '/../src/Middleware/CORS.php';
require_once __DIR__ . '/../src/Middleware/Auth.php';

// Handle CORS
CORS::handle();

// Global exception handler
set_exception_handler([Response::class, 'handleException']);

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/LaunchPad/launchpad-api/public', '', $path);
$path = trim($path, '/');

// Route handling
try {
    // Health check
    if ($path === 'health' || $path === '') {
        Response::success([
            'status' => 'healthy',
            'version' => API_VERSION,
            'timestamp' => date('c')
        ], 'API is running');
    }

    // Load route files
    $routes = [
        'auth' => __DIR__ . '/../routes/auth.php',
        'students' => __DIR__ . '/../routes/students.php',
        'companies' => __DIR__ . '/../routes/companies.php',
        'jobs' => __DIR__ . '/../routes/jobs.php',
        'admin' => __DIR__ . '/../routes/admin.php',
    ];

    foreach ($routes as $prefix => $file) {
        if (str_starts_with($path, $prefix) && file_exists($file)) {
            require_once $file;
            exit;
        }
    }

    // 404 Not Found
    Response::error('Endpoint not found', 404);

} catch (Throwable $e) {
    Response::handleException($e);
}

