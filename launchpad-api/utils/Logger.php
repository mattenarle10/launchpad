<?php

/**
 * Logger Utility
 * Centralized error and activity logging
 */

class Logger {
    private static $logDir = __DIR__ . '/../logs';
    private static $errorLogFile = 'error.log';
    private static $activityLogFile = 'activity.log';
    private static $debugLogFile = 'debug.log';

    /**
     * Initialize log directory
     */
    private static function init() {
        if (!file_exists(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
    }

    /**
     * Write to log file
     */
    private static function writeLog($filename, $message, $level = 'INFO') {
        self::init();
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        $filepath = self::$logDir . '/' . $filename;
        @file_put_contents($filepath, $logMessage, FILE_APPEND);
    }

    /**
     * Log error messages
     */
    public static function error($message, $context = []) {
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        self::writeLog(self::$errorLogFile, $message . $contextStr, 'ERROR');
    }

    /**
     * Log warning messages
     */
    public static function warning($message, $context = []) {
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        self::writeLog(self::$errorLogFile, $message . $contextStr, 'WARNING');
    }

    /**
     * Log info/activity messages
     */
    public static function info($message, $context = []) {
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        self::writeLog(self::$activityLogFile, $message . $contextStr, 'INFO');
    }

    /**
     * Log debug messages
     */
    public static function debug($message, $context = []) {
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        self::writeLog(self::$debugLogFile, $message . $contextStr, 'DEBUG');
    }

    /**
     * Log API requests
     */
    public static function logRequest($method, $path, $userId = null, $statusCode = null) {
        $userInfo = $userId ? "User: {$userId}" : "User: Guest";
        $status = $statusCode ? "Status: {$statusCode}" : "";
        $message = "{$method} {$path} | {$userInfo} {$status}";
        self::info($message);
    }

    /**
     * Log database errors
     */
    public static function dbError($query, $error) {
        $message = "Database Error: {$error} | Query: {$query}";
        self::error($message);
    }

    /**
     * Log authentication attempts
     */
    public static function authAttempt($email, $success = false, $reason = '') {
        $status = $success ? 'SUCCESS' : 'FAILED';
        $message = "Auth {$status}: {$email}";
        if ($reason) {
            $message .= " | Reason: {$reason}";
        }
        self::info($message);
    }

    /**
     * Log exceptions
     */
    public static function exception($exception, $context = []) {
        $message = "Exception: " . $exception->getMessage() . 
                   " | File: " . $exception->getFile() . 
                   " | Line: " . $exception->getLine();
        
        if (!empty($context)) {
            $message .= " | Context: " . json_encode($context);
        }
        
        self::error($message);
        self::error("Stack Trace: " . $exception->getTraceAsString());
    }

    /**
     * Clear old logs (older than specified days)
     */
    public static function clearOldLogs($days = 30) {
        self::init();
        
        $files = glob(self::$logDir . '/*.log');
        $cutoffTime = time() - ($days * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
            }
        }
    }

    /**
     * Get log file path
     */
    public static function getLogPath($type = 'error') {
        self::init();
        
        switch ($type) {
            case 'activity':
                return self::$logDir . '/' . self::$activityLogFile;
            case 'debug':
                return self::$logDir . '/' . self::$debugLogFile;
            default:
                return self::$logDir . '/' . self::$errorLogFile;
        }
    }

    /**
     * Read recent log entries
     */
    public static function getRecentLogs($type = 'error', $lines = 100) {
        $filepath = self::getLogPath($type);
        
        if (!file_exists($filepath)) {
            return [];
        }
        
        $file = new SplFileObject($filepath);
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key() + 1;
        
        $startLine = max(0, $totalLines - $lines);
        $file->seek($startLine);
        
        $logs = [];
        while (!$file->eof()) {
            $line = trim($file->fgets());
            if ($line) {
                $logs[] = $line;
            }
        }
        
        return $logs;
    }
}
