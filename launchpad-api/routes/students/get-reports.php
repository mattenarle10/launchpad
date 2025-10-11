<?php

/**
 * GET /students/:id/reports
 * Get student submitted reports
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
    SELECT * FROM submission_reports 
    WHERE student_id = ? 
    ORDER BY date_sent DESC
");
$stmt->bind_param('i', $studentId);
$stmt->execute();
$result = $stmt->get_result();

$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}

Response::success($reports);

