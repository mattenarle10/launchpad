<?php

/**
 * GET /admin/ojt/stats
 * CDC gets overall OJT statistics for dashboard
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_CDC);

$conn = Database::getConnection();

// Overall stats
$stats = [
    'total_students' => $conn->query("SELECT COUNT(*) as count FROM verified_students")->fetch_assoc()['count'],
    'students_with_progress' => $conn->query("SELECT COUNT(*) as count FROM ojt_progress")->fetch_assoc()['count'],
    'total_hours_completed' => floatval($conn->query("SELECT COALESCE(SUM(completed_hours), 0) as total FROM ojt_progress")->fetch_assoc()['total']),
    'average_completion_percentage' => round(floatval($conn->query("
        SELECT COALESCE(AVG((completed_hours / required_hours) * 100), 0) as avg 
        FROM ojt_progress
    ")->fetch_assoc()['avg']), 2),
];

// Status breakdown
$stats['status_breakdown'] = [
    'not_started' => intval($conn->query("SELECT COUNT(*) as count FROM ojt_progress WHERE status = 'not_started'")->fetch_assoc()['count']),
    'in_progress' => intval($conn->query("SELECT COUNT(*) as count FROM ojt_progress WHERE status = 'in_progress'")->fetch_assoc()['count']),
    'completed' => intval($conn->query("SELECT COUNT(*) as count FROM ojt_progress WHERE status = 'completed'")->fetch_assoc()['count']),
];

// Pending reports
$stats['pending_reports'] = intval($conn->query("
    SELECT COUNT(*) as count 
    FROM daily_reports 
    WHERE status = 'pending'
")->fetch_assoc()['count']);

// Recent approved reports (last 7 days)
$stats['recent_approved_reports'] = intval($conn->query("
    SELECT COUNT(*) as count 
    FROM daily_reports 
    WHERE status = 'approved' 
    AND reviewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
")->fetch_assoc()['count']);

// Top performers (students with most hours)
$stmt = $conn->query("
    SELECT 
        s.id_num,
        s.first_name,
        s.last_name,
        p.completed_hours,
        p.status
    FROM ojt_progress p
    JOIN verified_students s ON p.student_id = s.student_id
    ORDER BY p.completed_hours DESC
    LIMIT 5
");

$stats['top_performers'] = [];
while ($row = $stmt->fetch_assoc()) {
    $stats['top_performers'][] = $row;
}

Response::success($stats);

