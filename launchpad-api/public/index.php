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
        
        // /students/evaluation (GET - student's evaluation from company)
        if (count($pathParts) === 2 && $pathParts[1] === 'evaluation') {
            require __DIR__ . '/../routes/students/get-evaluation.php';
            exit;
        }
        
        // /students/performance (GET - student's performance score from company)
        if (count($pathParts) === 2 && $pathParts[1] === 'performance') {
            require __DIR__ . '/../routes/students/get-performance.php';
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

    // Route: /profile
    if ($pathParts[0] === 'profile' && count($pathParts) === 1) {
        if ($method === 'GET') {
            require __DIR__ . '/../routes/profile/get-profile.php';
        } elseif ($method === 'PUT') {
            require __DIR__ . '/../routes/profile/update-profile.php';
        }
        exit;
    }

    // Route: /profile/logo (POST - upload company logo)
    if ($pathParts[0] === 'profile' && count($pathParts) === 2 && $pathParts[1] === 'logo') {
        if ($method === 'POST') {
            require __DIR__ . '/../routes/profile/upload-logo.php';
        }
        exit;
    }

    // Route: /companies
    if ($pathParts[0] === 'companies') {
        // /companies/register (POST - no auth)
        if (count($pathParts) === 2 && $pathParts[1] === 'register') {
            require __DIR__ . '/../routes/companies/register.php';
            exit;
        }
        
        // /companies/dashboard/stats (GET - company dashboard stats)
        if (count($pathParts) === 3 && $pathParts[1] === 'dashboard' && $pathParts[2] === 'stats') {
            require __DIR__ . '/../routes/companies/get-dashboard-stats.php';
            exit;
        }
        
        // /companies/students (GET - company's assigned students)
        if (count($pathParts) === 2 && $pathParts[1] === 'students') {
            require __DIR__ . '/../routes/companies/get-students.php';
            exit;
        }
        
        // /companies/students/:id/evaluation (PUT - update student evaluation)
        if (count($pathParts) === 4 && $pathParts[1] === 'students' && $pathParts[3] === 'evaluation') {
            $id = $pathParts[2];
            require __DIR__ . '/../routes/companies/update-student-evaluation.php';
            exit;
        }
        
        // /companies/students/:id/performance (PUT - update student performance score)
        if (count($pathParts) === 4 && $pathParts[1] === 'students' && $pathParts[3] === 'performance') {
            $id = $pathParts[2];
            require __DIR__ . '/../routes/companies/update-student-performance.php';
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
        
        // /admin/companies/:id (PUT - edit company, DELETE - delete company)
        if (count($pathParts) === 3 && $pathParts[1] === 'companies' && is_numeric($pathParts[2])) {
            $id = $pathParts[2];
            if ($method === 'PUT') {
                require __DIR__ . '/../routes/admin/edit-company.php';
            } elseif ($method === 'DELETE') {
                require __DIR__ . '/../routes/admin/delete-company.php';
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
        
        // /admin/ojt/:id/hours
        if (count($pathParts) === 4 && $pathParts[1] === 'ojt' && $pathParts[3] === 'hours') {
            $id = $pathParts[2];
            require __DIR__ . '/../routes/admin/update-ojt-hours.php';
            exit;
        }
        
        // /admin/ojt/sync-status
        if (count($pathParts) === 3 && $pathParts[1] === 'ojt' && $pathParts[2] === 'sync-status') {
            require __DIR__ . '/../routes/admin/sync-ojt-status.php';
            exit;
        }
        
        // /admin/reports/pending
        if (count($pathParts) === 3 && $pathParts[1] === 'reports' && $pathParts[2] === 'pending') {
            require __DIR__ . '/../routes/admin/get-pending-reports.php';
            exit;
        }
        
        // /admin/reports/approved
        if (count($pathParts) === 3 && $pathParts[1] === 'reports' && $pathParts[2] === 'approved') {
            require __DIR__ . '/../routes/admin/get-approved-reports.php';
            exit;
        }
        
        // /admin/reports/:id/review
        if (count($pathParts) === 4 && $pathParts[1] === 'reports' && $pathParts[3] === 'review') {
            $id = $pathParts[2];
            require __DIR__ . '/../routes/admin/review-report.php';
            exit;
        }
        
        // /admin/jobs/:id (DELETE - CDC deletes any job)
        if (count($pathParts) === 3 && $pathParts[1] === 'jobs' && is_numeric($pathParts[2])) {
            $id = $pathParts[2];
            if ($method === 'DELETE') {
                require __DIR__ . '/../routes/admin/delete-job.php';
            }
            exit;
        }
        
        // /admin/companies (GET - CDC gets all companies)
        if (count($pathParts) === 2 && $pathParts[1] === 'companies') {
            require __DIR__ . '/../routes/admin/get-companies.php';
            exit;
        }
    }

    // Route: /jobs
    if ($pathParts[0] === 'jobs') {
        // /jobs/company (GET - company's own jobs)
        if (count($pathParts) === 2 && $pathParts[1] === 'company') {
            require __DIR__ . '/../routes/jobs/get-company-jobs.php';
            exit;
        }
        
        // /jobs (GET - all active jobs, POST - create job)
        if (count($pathParts) === 1) {
            if ($method === 'GET') {
                require __DIR__ . '/../routes/jobs/get-all-jobs.php';
            } elseif ($method === 'POST') {
                require __DIR__ . '/../routes/jobs/create-job.php';
            }
            exit;
        }
        
        // /jobs/:id (PUT - update, DELETE - delete)
        if (count($pathParts) === 2 && is_numeric($pathParts[1])) {
            $id = $pathParts[1];
            if ($method === 'PUT') {
                require __DIR__ . '/../routes/jobs/update-job.php';
            } elseif ($method === 'DELETE') {
                require __DIR__ . '/../routes/jobs/delete-job.php';
            }
            exit;
        }
    }

    // Route: /notifications
    if ($pathParts[0] === 'notifications') {
        // /notifications/student (GET - student's notifications)
        if (count($pathParts) === 2 && $pathParts[1] === 'student') {
            require __DIR__ . '/../routes/notifications/get-student-notifications.php';
            exit;
        }
        
        // /notifications/:id/read (PUT - mark as read)
        if (count($pathParts) === 3 && $pathParts[2] === 'read') {
            $id = $pathParts[1];
            require __DIR__ . '/../routes/notifications/mark-read.php';
            exit;
        }
        
        // /notifications/:id (DELETE - delete notification)
        if (count($pathParts) === 2 && is_numeric($pathParts[1])) {
            $id = $pathParts[1];
            if ($method === 'DELETE') {
                require __DIR__ . '/../routes/notifications/delete.php';
            }
            exit;
        }
        
        // /notifications (GET - all notifications, POST - create)
        if (count($pathParts) === 1) {
            if ($method === 'GET') {
                require __DIR__ . '/../routes/notifications/get-all.php';
            } elseif ($method === 'POST') {
                require __DIR__ . '/../routes/notifications/create.php';
            }
            exit;
        }
    }

    // 404 Not Found
    Response::error('Endpoint not found: ' . $path, 404);

} catch (Throwable $e) {
    Response::handleException($e);
}
