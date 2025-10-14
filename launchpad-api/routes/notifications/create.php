<?php

/**
 * POST /notifications
 * Create and send notification (CDC only)
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_CDC);

$conn = Database::getConnection();
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (empty($input['title']) || empty($input['message'])) {
    Response::error('Title and message are required', 400);
}

$title = trim($input['title']);
$message = trim($input['message']);
$recipientType = $input['recipient_type'] ?? 'all';
$studentIds = $input['student_ids'] ?? [];

// Validate recipient type
if (!in_array($recipientType, ['all', 'specific'])) {
    Response::error('Invalid recipient type. Must be "all" or "specific"', 400);
}

// If specific, validate student IDs
if ($recipientType === 'specific' && empty($studentIds)) {
    Response::error('Student IDs are required for specific notifications', 400);
}

$cdcUserId = Auth::getUserId();

// Start transaction
$conn->begin_transaction();

try {
    // Insert notification
    $stmt = $conn->prepare("
        INSERT INTO notifications (title, message, recipient_type, created_by)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param('sssi', $title, $message, $recipientType, $cdcUserId);
    $stmt->execute();
    $notificationId = $conn->insert_id();

    // If sending to specific students, insert recipients
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

    Response::success([
        'notification_id' => $notificationId,
        'title' => $title,
        'message' => $message,
        'recipient_type' => $recipientType,
        'recipients_count' => $recipientType === 'all' ? 'all' : count($studentIds)
    ], 'Notification sent successfully');

} catch (Exception $e) {
    $conn->rollback();
    Response::error('Failed to send notification: ' . $e->getMessage(), 500);
}
