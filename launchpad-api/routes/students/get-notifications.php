<?php

/**
 * GET /students/:id/notifications
 * Get student notifications
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireAuth();
$studentId = intval($id);

if ($user['role'] === ROLE_STUDENT && $user['id'] !== $studentId) {
    Response::error('Forbidden', 403);
}

$conn = Database::getConnection();
$stmt = $conn->prepare("
    SELECT * FROM notifications 
    WHERE student_id = ? 
    ORDER BY date_sent DESC
");
$stmt->bind_param('i', $studentId);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

Response::success($notifications);

