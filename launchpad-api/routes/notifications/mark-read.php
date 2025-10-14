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

// Check if notification exists and belongs to student
$stmt = $conn->prepare("
    SELECT nr.id, nr.is_read
    FROM notification_recipients nr
    WHERE nr.notification_id = ? AND nr.student_id = ?
");
$stmt->bind_param('ii', $notificationId, $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('Notification not found', 404);
}

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

Response::success(['message' => 'Notification marked as read']);
