<?php

/**
 * GET /students/:id/reports/daily
 * Get student's daily report submissions
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireAuth();
$studentId = intval($id);

// Students can only view their own reports unless admin
if ($user['role'] === ROLE_STUDENT && $user['id'] !== $studentId) {
    Response::error('Forbidden', 403);
}

$conn = Database::getConnection();

$statusFilter = $_GET['status'] ?? 'all'; // all, pending, approved, rejected

$whereClause = $statusFilter !== 'all' ? "AND status = ?" : "";

$query = "
    SELECT * FROM daily_reports 
    WHERE student_id = ? $whereClause
    ORDER BY report_date DESC, submitted_at DESC
";

if ($statusFilter !== 'all') {
    $stmt = $conn->prepare($query);
    $stmt->bind_param('is', $studentId, $statusFilter);
} else {
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $studentId);
}

$stmt->execute();
$result = $stmt->get_result();

$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}

// Get summary
$summary = [
    'total' => count($reports),
    'pending' => $conn->query("SELECT COUNT(*) as count FROM daily_reports WHERE student_id = $studentId AND status = 'pending'")->fetch_assoc()['count'],
    'approved' => $conn->query("SELECT COUNT(*) as count FROM daily_reports WHERE student_id = $studentId AND status = 'approved'")->fetch_assoc()['count'],
    'rejected' => $conn->query("SELECT COUNT(*) as count FROM daily_reports WHERE student_id = $studentId AND status = 'rejected'")->fetch_assoc()['count'],
];

Response::success([
    'reports' => $reports,
    'summary' => $summary
]);

