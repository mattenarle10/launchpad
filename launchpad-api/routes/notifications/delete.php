<?php

/**
 * DELETE /notifications/:id
 * Delete notification (CDC only)
 */

if ($method !== 'DELETE') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_CDC);

$conn = Database::getConnection();
$notificationId = intval($id);

// Check if notification exists
$stmt = $conn->prepare("SELECT notification_id FROM notifications WHERE notification_id = ?");
$stmt->bind_param('i', $notificationId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('Notification not found', 404);
}

// Delete notification (recipients will be deleted automatically due to CASCADE)
$stmt = $conn->prepare("DELETE FROM notifications WHERE notification_id = ?");
$stmt->bind_param('i', $notificationId);
$stmt->execute();

Response::success(['message' => 'Notification deleted successfully']);
