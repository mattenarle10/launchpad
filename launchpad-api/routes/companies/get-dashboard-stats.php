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

// Get evaluation statistics
$evaluationStats = $conn->query("
    SELECT 
        COUNT(CASE WHEN evaluation_rank IS NOT NULL THEN 1 END) as evaluated_count,
        ROUND(AVG(evaluation_rank), 2) as average_rank,
        COUNT(CASE WHEN evaluation_rank >= 80 THEN 1 END) as excellent_count,
        COUNT(CASE WHEN evaluation_rank >= 60 AND evaluation_rank < 80 THEN 1 END) as good_count,
        COUNT(CASE WHEN evaluation_rank < 60 THEN 1 END) as needs_improvement_count
    FROM verified_students
    WHERE company_id = $companyId
")->fetch_assoc();

// Get performance score distribution
$performanceResult = $conn->query("
    SELECT performance_score, COUNT(*) as count
    FROM verified_students
    WHERE company_id = $companyId AND performance_score IS NOT NULL
    GROUP BY performance_score
");

$performanceBreakdown = [
    'Excellent' => 0,
    'Good' => 0,
    'Satisfactory' => 0,
    'Needs Improvement' => 0,
    'Poor' => 0
];

while ($row = $performanceResult->fetch_assoc()) {
    $performanceBreakdown[$row['performance_score']] = intval($row['count']);
}

$performanceAssessed = array_sum($performanceBreakdown);

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
    'job_postings' => $jobPostings,
    'evaluation' => [
        'evaluated_count' => intval($evaluationStats['evaluated_count']),
        'average_rank' => floatval($evaluationStats['average_rank'] ?? 0),
        'excellent_count' => intval($evaluationStats['excellent_count']),
        'good_count' => intval($evaluationStats['good_count']),
        'needs_improvement_count' => intval($evaluationStats['needs_improvement_count'])
    ],
    'performance' => [
        'assessed_count' => $performanceAssessed,
        'breakdown' => $performanceBreakdown
    ]
];

Response::success($stats);
