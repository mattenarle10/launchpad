<?php

/**
 * Database Configuration
 * Centralized database connection management
 */

class Database
{
    // Local Development (commented for production)
    // private const HOST = '127.0.0.1';
    // private const DB_NAME = 'launchpad_db';
    // private const USERNAME = 'root';
    // private const PASSWORD = '';
    // private const CHARSET = 'utf8mb4';

    // Production (Hostinger) - ACTIVE
    private const HOST = 'localhost';
    private const DB_NAME = 'u153905861_launchpad_db';
    private const USERNAME = 'u153905861_launchpad';
    private const PASSWORD = 'Naomi.123!';
    private const CHARSET = 'utf8mb4';

    private static ?mysqli $connection = null;

    /**
     * Get singleton database connection
     */
    public static function getConnection(): mysqli
    {
        if (self::$connection === null) {
            self::$connection = new mysqli(
                self::HOST,
                self::USERNAME,
                self::PASSWORD,
                self::DB_NAME
            );

            if (self::$connection->connect_error) {
                error_log("Database connection failed: " . self::$connection->connect_error);
                http_response_code(500);
                die(json_encode([
                    'success' => false,
                    'message' => 'Database connection failed',
                    'error' => self::$connection->connect_error
                ]));
            }

            // Set charset
            self::$connection->set_charset(self::CHARSET);
        }

        return self::$connection;
    }

    /**
     * Close database connection
     */
    public static function closeConnection(): void
    {
        if (self::$connection !== null) {
            self::$connection->close();
            self::$connection = null;
        }
    }

    /**
     * Execute prepared statement
     */
    public static function query(string $sql, array $params = [], string $types = ''): mysqli_result|bool
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $conn->error);
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }
}

