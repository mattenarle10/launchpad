<?php

/**
 * LaunchPad API Entry Point
 */

// Load configuration
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

// Load lib
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../lib/cors.php';
require_once __DIR__ . '/../lib/auth.php';

// Handle CORS
CORS::handle();

// Global exception handler
set_exception_handler([Response::class, 'handleException']);

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/LaunchPad/launchpad-api/public', '', $path);
$path = trim($path, '/');
$pathParts = explode('/', $path);

// Extract ID if present
$id = null;

try {
    // Health check
    if ($path === 'health' || $path === '') {
        Response::success([
            'status' => 'healthy',
            'version' => API_VERSION,
            'timestamp' => date('c')
        ], 'LaunchPad API is running! 🚀');
    }

    // Route: /auth/*
    if ($pathParts[0] === 'auth') {
        $action = $pathParts[1] ?? '';
        $routeFile = __DIR__ . "/../routes/auth/$action.php";
        
        if (file_exists($routeFile)) {
            require $routeFile;
            exit;
        }
    }

    // Route: /students or /students/:id or /students/:id/action
    if ($pathParts[0] === 'students') {
        // /students/register (POST - no auth)
        if (count($pathParts) === 2 && $pathParts[1] === 'register') {
            require __DIR__ . '/../routes/students/register.php';
            exit;
        }
        
        // /students
        if (count($pathParts) === 1) {
            require __DIR__ . '/../routes/students/get-all.php';
            exit;
        }
        
        // /students/:id
        if (count($pathParts) === 2 && is_numeric($pathParts[1])) {
            $id = $pathParts[1];
            require __DIR__ . '/../routes/students/get-one.php';
            exit;
        }
        
        // /students/:id/ojt
        if (count($pathParts) === 3 && $pathParts[2] === 'ojt') {
            $id = $pathParts[1];
            require __DIR__ . '/../routes/students/get-ojt-progress.php';
            exit;
        }
        
        // /students/:id/reports/daily
        if (count($pathParts) === 4 && $pathParts[2] === 'reports' && $pathParts[3] === 'daily') {
            $id = $pathParts[1];
            if ($method === 'GET') {
                require __DIR__ . '/../routes/students/get-daily-reports.php';
            } else if ($method === 'POST') {
                require __DIR__ . '/../routes/students/submit-daily-report.php';
            }
            exit;
        }
    }

    // Route: /companies
    if ($pathParts[0] === 'companies') {
        // /companies/register (POST - no auth)
        if (count($pathParts) === 2 && $pathParts[1] === 'register') {
            require __DIR__ . '/../routes/companies/register.php';
            exit;
        }
        
        // /companies
        if (count($pathParts) === 1) {
            require __DIR__ . '/../routes/companies/get-all.php';
            exit;
        }
        
        // /companies/:id
        if (count($pathParts) === 2 && is_numeric($pathParts[1])) {
            $id = $pathParts[1];
            require __DIR__ . '/../routes/companies/get-one.php';
            exit;
        }
    }

    // Route: /admin/*
    if ($pathParts[0] === 'admin') {
        // /admin/unverified/students
        if (count($pathParts) === 3 && $pathParts[1] === 'unverified' && $pathParts[2] === 'students') {
            require __DIR__ . '/../routes/admin/get-unverified-students.php';
            exit;
        }
        
        // /admin/unverified/companies
        if (count($pathParts) === 3 && $pathParts[1] === 'unverified' && $pathParts[2] === 'companies') {
            require __DIR__ . '/../routes/admin/get-unverified-companies.php';
            exit;
        }
        
        // /admin/verify/students/:id
        if (count($pathParts) === 4 && $pathParts[1] === 'verify' && $pathParts[2] === 'students') {
            $id = $pathParts[3];
            require __DIR__ . '/../routes/admin/verify-student.php';
            exit;
        }
        
        // /admin/verify/companies/:id
        if (count($pathParts) === 4 && $pathParts[1] === 'verify' && $pathParts[2] === 'companies') {
            $id = $pathParts[3];
            require __DIR__ . '/../routes/admin/verify-company.php';
            exit;
        }
        
        // /admin/reject/students/:id
        if (count($pathParts) === 4 && $pathParts[1] === 'reject' && $pathParts[2] === 'students') {
            $id = $pathParts[3];
            require __DIR__ . '/../routes/admin/reject-student.php';
            exit;
        }
        
        // /admin/reject/companies/:id
        if (count($pathParts) === 4 && $pathParts[1] === 'reject' && $pathParts[2] === 'companies') {
            $id = $pathParts[3];
            require __DIR__ . '/../routes/admin/reject-company.php';
            exit;
        }
        
        // /admin/students/:id (PUT - edit student, DELETE - delete student)
        if (count($pathParts) === 3 && $pathParts[1] === 'students' && is_numeric($pathParts[2])) {
            $id = $pathParts[2];
            if ($method === 'PUT') {
                require __DIR__ . '/../routes/admin/edit-student.php';
            } elseif ($method === 'DELETE') {
                require __DIR__ . '/../routes/admin/delete-student.php';
            }
            exit;
        }
        
        // /admin/ojt/progress
        if (count($pathParts) === 3 && $pathParts[1] === 'ojt' && $pathParts[2] === 'progress') {
            require __DIR__ . '/../routes/admin/get-all-ojt-progress.php';
            exit;
        }
        
        // /admin/ojt/stats
        if (count($pathParts) === 3 && $pathParts[1] === 'ojt' && $pathParts[2] === 'stats') {
            require __DIR__ . '/../routes/admin/get-ojt-stats.php';
            exit;
        }
        
        // /admin/reports/pending
        if (count($pathParts) === 3 && $pathParts[1] === 'reports' && $pathParts[2] === 'pending') {
            require __DIR__ . '/../routes/admin/get-pending-reports.php';
            exit;
        }
        
        // /admin/reports/:id/review
        if (count($pathParts) === 4 && $pathParts[1] === 'reports' && $pathParts[3] === 'review') {
            $id = $pathParts[2];
            require __DIR__ . '/../routes/admin/review-report.php';
            exit;
        }
    }

    // 404 Not Found
    Response::error('Endpoint not found: ' . $path, 404);

} catch (Throwable $e) {
    Response::handleException($e);
}
