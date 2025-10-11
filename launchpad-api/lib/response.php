<?php

/**
 * Standardized API Response Utility
 */

class Response
{
    public static function success(mixed $data = null, string $message = 'Success', int $code = 200): void
    {
        self::json([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'timestamp' => date('c')
        ], $code);
    }

    public static function error(string $message = 'Error', int $code = 400, mixed $errors = null): void
    {
        self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('c')
        ], $code);
    }

    public static function paginated(array $data, int $page, int $pageSize, int $total): void
    {
        self::json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'pageSize' => $pageSize,
                'total' => $total,
                'totalPages' => ceil($total / $pageSize)
            ],
            'timestamp' => date('c')
        ]);
    }

    private static function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    public static function handleException(Throwable $e): void
    {
        error_log($e->getMessage());
        error_log($e->getTraceAsString());

        if (DEBUG_MODE) {
            self::error($e->getMessage(), 500, [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ]);
        } else {
            self::error('Internal server error', 500);
        }
    }
}

