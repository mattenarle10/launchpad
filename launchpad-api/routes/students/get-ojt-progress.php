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
$progress['completion_percentage'] = round(($progress['completed_hours'] / $progress['required_hours']) * 100, 2);
$progress['remaining_hours'] = $progress['required_hours'] - $progress['completed_hours'];

// Get approved daily reports (these are the hours that were added)
$stmt = $conn->prepare("
    SELECT report_id, report_date, hours_requested, description, activity_type, 
           status, reviewed_at, submitted_at
    FROM daily_reports 
    WHERE student_id = ? AND status = 'approved'
    ORDER BY report_date DESC
");
$stmt->bind_param('i', $studentId);
$stmt->execute();
$result = $stmt->get_result();

$approvedReports = [];
while ($row = $result->fetch_assoc()) {
    $approvedReports[] = $row;
}

Response::success([
    'progress' => $progress,
    'approved_reports' => $approvedReports,
    'total_approved' => count($approvedReports)
]);

