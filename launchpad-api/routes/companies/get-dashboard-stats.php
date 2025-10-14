<?php

/**
 * GET /companies/dashboard/stats
 * Partner Company gets their dashboard statistics
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireAuth();
if ($user['role'] !== ROLE_COMPANY) {
    Response::error('Only partner companies can access this endpoint', 403);
}

$conn = Database::getConnection();
$companyId = intval($user['id']);

// Get total students assigned to this company
$totalStudents = intval($conn->query("
    SELECT COUNT(*) as count 
    FROM verified_students 
    WHERE company_id = $companyId
")->fetch_assoc()['count']);

// Get students with OJT progress breakdown by status
$statusResult = $conn->query("
    SELECT 
        p.status,
        COUNT(*) as count
    FROM ojt_progress p
    JOIN verified_students s ON p.student_id = s.student_id
    WHERE s.company_id = $companyId
    GROUP BY p.status
");

$statusBreakdown = [
    'not_started' => 0,
    'in_progress' => 0,
    'completed' => 0
];

while ($row = $statusResult->fetch_assoc()) {
    $statusBreakdown[$row['status']] = intval($row['count']);
}

// Get course breakdown for students in this company
$courseResult = $conn->query("
    SELECT 
        s.course,
        COUNT(*) as count
    FROM verified_students s
    WHERE s.company_id = $companyId
    GROUP BY s.course
");

$courseBreakdown = [];
while ($row = $courseResult->fetch_assoc()) {
    $courseBreakdown[$row['course']] = intval($row['count']);
}

// Get pending daily reports count (reports submitted by company's students)
$pendingReports = intval($conn->query("
    SELECT COUNT(*) as count
    FROM daily_reports dr
    JOIN verified_students s ON dr.student_id = s.student_id
    WHERE s.company_id = $companyId
    AND dr.status = 'pending'
")->fetch_assoc()['count']);

// Get today's submitted reports count
$reportsToday = intval($conn->query("
    SELECT COUNT(*) as count
    FROM daily_reports dr
    JOIN verified_students s ON dr.student_id = s.student_id
    WHERE s.company_id = $companyId
    AND DATE(dr.submitted_at) = CURDATE()
")->fetch_assoc()['count']);

// Get job postings count (if you have a job_postings table)
// For now, set to 0 as placeholder
$jobPostings = 0;

// Assemble stats
$stats = [
    'total_students' => $totalStudents,
    'active_students' => $statusBreakdown['in_progress'],
    'completed_students' => $statusBreakdown['completed'],
    'not_started_students' => $statusBreakdown['not_started'],
    'status_breakdown' => $statusBreakdown,
    'course_breakdown' => $courseBreakdown,
    'pending_reports' => $pendingReports,
    'reports_today' => $reportsToday,
    'job_postings' => $jobPostings
];

Response::success($stats);
