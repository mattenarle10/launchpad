<?php

/**
 * GET /students/:id/ojt
 * Get student OJT progress and hours log
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireAuth();
$studentId = intval($id);

// Students can only view their own progress unless admin
if ($user['role'] === ROLE_STUDENT && $user['id'] !== $studentId) {
    Response::error('Forbidden', 403);
}

$conn = Database::getConnection();

// Get OJT progress summary
$stmt = $conn->prepare("
    SELECT * FROM ojt_progress WHERE student_id = ?
");
$stmt->bind_param('i', $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('OJT progress not found for this student', 404);
}

$progress = $result->fetch_assoc();

// Calculate percentage
$progress['completion_percentage'] = ($progress['completed_hours'] / $progress['required_hours']) * 100;
$progress['remaining_hours'] = $progress['required_hours'] - $progress['completed_hours'];

// Get hours log history
$stmt = $conn->prepare("
    SELECT * FROM ojt_hours_log 
    WHERE student_id = ? 
    ORDER BY log_date DESC, created_at DESC
");
$stmt->bind_param('i', $studentId);
$stmt->execute();
$result = $stmt->get_result();

$hoursLog = [];
while ($row = $result->fetch_assoc()) {
    $hoursLog[] = $row;
}

Response::success([
    'progress' => $progress,
    'hours_log' => $hoursLog,
    'total_logs' => count($hoursLog)
]);

