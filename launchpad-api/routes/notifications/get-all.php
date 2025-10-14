<?php

/**
 * GET /notifications
 * Get all notifications (CDC only)
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_CDC);

$conn = Database::getConnection();

// Get all notifications with creator info
$stmt = $conn->prepare("
    SELECT 
        n.notification_id,
        n.title,
        n.message,
        n.recipient_type,
        n.created_at,
        CONCAT(c.first_name, ' ', c.last_name) as created_by_name,
        CASE 
            WHEN n.recipient_type = 'all' THEN (SELECT COUNT(*) FROM verified_students)
            ELSE (SELECT COUNT(*) FROM notification_recipients WHERE notification_id = n.notification_id)
        END as recipients_count,
        CASE 
            WHEN n.recipient_type = 'specific' THEN (
                SELECT COUNT(*) FROM notification_recipients 
                WHERE notification_id = n.notification_id AND is_read = TRUE
            )
            ELSE NULL
        END as read_count
    FROM notifications n
    JOIN cdc_users c ON n.created_by = c.id
    ORDER BY n.created_at DESC
");

$stmt->execute();
$result = $stmt->get_result();
$notifications = [];

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

Response::success(['data' => $notifications]);
