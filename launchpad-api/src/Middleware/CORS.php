<?php

/**
 * CORS Middleware
 * Handles Cross-Origin Resource Sharing for frontend apps
 */

class CORS
{
    private static array $allowedOrigins = [
        'http://localhost:3000',  // React/Next.js dev
        'http://localhost:5173',  // Vite dev
        'http://localhost:8081',  // Expo dev
        'http://localhost:19006', // Expo web
    ];

    public static function handle(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Allow specific origins or all in development
        if (DEBUG_MODE || in_array($origin, self::$allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400'); // 24 hours

        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}

