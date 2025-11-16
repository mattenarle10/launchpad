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
        n.sender_type,
        COALESCE(nr.is_read, FALSE) as is_read,
        nr.read_at,
        nr.id as recipient_id,
        CASE 
            WHEN n.sender_type = 'cdc' THEN CONCAT(c.first_name, ' ', c.last_name)
            WHEN n.sender_type = 'company' THEN vc.company_name
            ELSE 'System'
        END as sent_by,
        c.first_name as sender_first_name,
        c.last_name as sender_last_name,
        vc.company_name as company_name
    FROM notifications n
    LEFT JOIN notification_recipients nr ON n.notification_id = nr.notification_id AND nr.student_id = ?
    LEFT JOIN cdc_users c ON n.created_by = c.id AND n.sender_type = 'cdc'
    LEFT JOIN verified_companies vc ON n.company_id = vc.company_id AND n.sender_type = 'company'
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
