<?php

/**
 * NotificationHelper
 * Utility class for creating and sending notifications
 */
class NotificationHelper {
    
    /**
     * Create a notification from a company to students
     * 
     * @param mysqli $conn Database connection
     * @param int $companyId Company ID sending the notification
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $recipientType 'all' or 'specific'
     * @param array $studentIds Array of student IDs (required if recipientType is 'specific')
     * @return int Notification ID
     * @throws Exception on error
     */
    public static function createCompanyNotification($conn, $companyId, $title, $message, $recipientType = 'all', $studentIds = []) {
        // Validate inputs
        if (empty($title) || empty($message)) {
            throw new Exception('Title and message are required');
        }
        
        if (!in_array($recipientType, ['all', 'specific'])) {
            throw new Exception('Invalid recipient type');
        }
        
        if ($recipientType === 'specific' && empty($studentIds)) {
            throw new Exception('Student IDs required for specific notifications');
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert notification with sender_type='company'
            $stmt = $conn->prepare("
                INSERT INTO notifications (title, message, recipient_type, sender_type, company_id, created_by)
                VALUES (?, ?, ?, 'company', ?, NULL)
            ");
            $stmt->bind_param('sssi', $title, $message, $recipientType, $companyId);
            $stmt->execute();
            $notificationId = $conn->insert_id;
            
            // Insert recipients
            if ($recipientType === 'specific') {
                // Insert specific recipients
                $stmt = $conn->prepare("
                    INSERT INTO notification_recipients (notification_id, student_id)
                    VALUES (?, ?)
                ");
                
                foreach ($studentIds as $studentId) {
                    $stmt->bind_param('ii', $notificationId, $studentId);
                    $stmt->execute();
                }
            } else {
                // Insert all verified students as recipients
                $stmt = $conn->prepare("
                    INSERT INTO notification_recipients (notification_id, student_id)
                    SELECT ?, student_id FROM verified_students
                ");
                $stmt->bind_param('i', $notificationId);
                $stmt->execute();
            }
            
            $conn->commit();
            return $notificationId;
            
        } catch (Exception $e) {
            $conn->rollback();
            throw new Exception('Failed to create notification: ' . $e->getMessage());
        }
    }
    
    /**
     * Create a notification from CDC to students
     * 
     * @param mysqli $conn Database connection
     * @param int $cdcUserId CDC user ID sending the notification
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $recipientType 'all' or 'specific'
     * @param array $studentIds Array of student IDs (required if recipientType is 'specific')
     * @return int Notification ID
     * @throws Exception on error
     */
    public static function createCdcNotification($conn, $cdcUserId, $title, $message, $recipientType = 'all', $studentIds = []) {
        // Validate inputs
        if (empty($title) || empty($message)) {
            throw new Exception('Title and message are required');
        }
        
        if (!in_array($recipientType, ['all', 'specific'])) {
            throw new Exception('Invalid recipient type');
        }
        
        if ($recipientType === 'specific' && empty($studentIds)) {
            throw new Exception('Student IDs required for specific notifications');
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert notification with sender_type='cdc'
            $stmt = $conn->prepare("
                INSERT INTO notifications (title, message, recipient_type, sender_type, created_by, company_id)
                VALUES (?, ?, ?, 'cdc', ?, NULL)
            ");
            $stmt->bind_param('sssi', $title, $message, $recipientType, $cdcUserId);
            $stmt->execute();
            $notificationId = $conn->insert_id;
            
            // Insert recipients (only for specific notifications)
            if ($recipientType === 'specific') {
                $stmt = $conn->prepare("
                    INSERT INTO notification_recipients (notification_id, student_id)
                    VALUES (?, ?)
                ");
                
                foreach ($studentIds as $studentId) {
                    $stmt->bind_param('ii', $notificationId, $studentId);
                    $stmt->execute();
                }
            }
            
            $conn->commit();
            return $notificationId;
            
        } catch (Exception $e) {
            $conn->rollback();
            throw new Exception('Failed to create notification: ' . $e->getMessage());
        }
    }
}

