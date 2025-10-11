<?php

/**
 * CORS Middleware
 */

class CORS
{
    private static array $allowedOrigins = [
        'http://localhost:3000',
        'http://localhost:5173',
        'http://localhost:8081',
        'http://localhost:19006',
    ];

    public static function handle(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (DEBUG_MODE || in_array($origin, self::$allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}

