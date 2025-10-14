<?php

/**
 * GET /notifications/student
 * Get notifications for logged-in student
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_STUDENT);

$conn = Database::getConnection();
$studentId = Auth::getUserId();

// Get all notifications for this student (both 'all' and 'specific')
$stmt = $conn->prepare("
    SELECT 
        n.notification_id,
        n.title,
        n.message,
        n.created_at,
        n.recipient_type,
        COALESCE(nr.is_read, FALSE) as is_read,
        nr.read_at,
        nr.id as recipient_id,
        CONCAT(c.first_name, ' ', c.last_name) as sent_by,
        c.first_name as sender_first_name,
        c.last_name as sender_last_name
    FROM notifications n
    LEFT JOIN notification_recipients nr ON n.notification_id = nr.notification_id AND nr.student_id = ?
    LEFT JOIN cdc_users c ON n.created_by = c.id
    WHERE n.recipient_type = 'all' 
       OR (n.recipient_type = 'specific' AND nr.student_id = ?)
    ORDER BY n.created_at DESC
");

$stmt->bind_param('ii', $studentId, $studentId);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

Response::success($notifications);
