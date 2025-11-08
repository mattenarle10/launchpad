<?php

/**
 * PUT /notifications/:id/read
 * Mark notification as read (Student only)
 */

if ($method !== 'PUT') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_STUDENT);

$conn = Database::getConnection();
$studentId = Auth::getUserId();
$notificationId = intval($id);

// Check if notification exists
$stmt = $conn->prepare("SELECT notification_id, recipient_type FROM notifications WHERE notification_id = ?");
$stmt->bind_param('i', $notificationId);
$stmt->execute();
$notifResult = $stmt->get_result();

if ($notifResult->num_rows === 0) {
    Response::error('Notification not found', 404);
}

$notification = $notifResult->fetch_assoc();

// Check if recipient record exists
$stmt = $conn->prepare("
    SELECT id, is_read
    FROM notification_recipients
    WHERE notification_id = ? AND student_id = ?
");
$stmt->bind_param('ii', $notificationId, $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Insert recipient record for 'all' type notifications
    $stmt = $conn->prepare("
        INSERT INTO notification_recipients (notification_id, student_id, is_read, read_at)
        VALUES (?, ?, TRUE, CURRENT_TIMESTAMP)
    ");
    $stmt->bind_param('ii', $notificationId, $studentId);
    $stmt->execute();
} else {
    $recipient = $result->fetch_assoc();
    
    if ($recipient['is_read']) {
        Response::success(['message' => 'Notification already marked as read']);
    }
    
    // Mark as read
    $stmt = $conn->prepare("
        UPDATE notification_recipients 
        SET is_read = TRUE, read_at = CURRENT_TIMESTAMP
        WHERE notification_id = ? AND student_id = ?
    ");
    $stmt->bind_param('ii', $notificationId, $studentId);
    $stmt->execute();
}

Response::success(['message' => 'Notification marked as read']);
