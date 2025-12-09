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

// Get filter parameters
$semester = $_GET['semester'] ?? '';
$academicYear = $_GET['academic_year'] ?? '';

// Build WHERE clause for filtering
$whereConditions = [];
$studentWhereConditions = [];

if (!empty($semester)) {
    $studentWhereConditions[] = "semester = '" . $conn->real_escape_string($semester) . "'";
}

if (!empty($academicYear)) {
    $studentWhereConditions[] = "academic_year = '" . $conn->real_escape_string($academicYear) . "'";
}

$studentWhere = !empty($studentWhereConditions) ? ' WHERE ' . implode(' AND ', $studentWhereConditions) : '';

// Overall stats
$stats = [
    'total_students' => $conn->query("SELECT COUNT(*) as count FROM verified_students{$studentWhere}")->fetch_assoc()['count'],
    'total_companies' => $conn->query("SELECT COUNT(*) as count FROM verified_companies")->fetch_assoc()['count'],
    'students_with_progress' => $conn->query("
        SELECT COUNT(*) as count 
        FROM ojt_progress p
        JOIN verified_students s ON p.student_id = s.student_id
        {$studentWhere}
    ")->fetch_assoc()['count'],
    'total_hours_completed' => floatval($conn->query("
        SELECT COALESCE(SUM(p.completed_hours), 0) as total 
        FROM ojt_progress p
        JOIN verified_students s ON p.student_id = s.student_id
        {$studentWhere}
    ")->fetch_assoc()['total']),
    'average_completion_percentage' => round(floatval($conn->query("
        SELECT COALESCE(AVG((p.completed_hours / p.required_hours) * 100), 0) as avg 
        FROM ojt_progress p
        JOIN verified_students s ON p.student_id = s.student_id
        {$studentWhere}
    ")->fetch_assoc()['avg']), 2),
];

// Status breakdown
$stats['status_breakdown'] = [
    'not_started' => intval($conn->query("
        SELECT COUNT(*) as count 
        FROM ojt_progress p
        JOIN verified_students s ON p.student_id = s.student_id
        {$studentWhere}" . (!empty($studentWhere) ? ' AND' : ' WHERE') . " p.status = 'not_started'
    ")->fetch_assoc()['count']),
    'in_progress' => intval($conn->query("
        SELECT COUNT(*) as count 
        FROM ojt_progress p
        JOIN verified_students s ON p.student_id = s.student_id
        {$studentWhere}" . (!empty($studentWhere) ? ' AND' : ' WHERE') . " p.status = 'in_progress'
    ")->fetch_assoc()['count']),
    'completed' => intval($conn->query("
        SELECT COUNT(*) as count 
        FROM ojt_progress p
        JOIN verified_students s ON p.student_id = s.student_id
        {$studentWhere}" . (!empty($studentWhere) ? ' AND' : ' WHERE') . " p.status = 'completed'
    ")->fetch_assoc()['count']),
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

// Unverified users (students + companies)
$unverifiedStudents = intval($conn->query("SELECT COUNT(*) as count FROM unverified_students")->fetch_assoc()['count']);
$unverifiedCompanies = intval($conn->query("SELECT COUNT(*) as count FROM unverified_companies")->fetch_assoc()['count']);
$stats['unverified_users'] = $unverifiedStudents + $unverifiedCompanies;

// Course breakdown for OJT students (those with progress)
$courseResult = $conn->query("
    SELECT s.course, COUNT(*) as count
    FROM ojt_progress p
    JOIN verified_students s ON p.student_id = s.student_id
    {$studentWhere}
    GROUP BY s.course
");
$courseBreakdown = [];
while ($row = $courseResult->fetch_assoc()) {
    $courseBreakdown[$row['course']] = intval($row['count']);
}
$stats['course_breakdown'] = $courseBreakdown;

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
    {$studentWhere}
    ORDER BY p.completed_hours DESC
    LIMIT 5
");

$stats['top_performers'] = [];
while ($row = $stmt->fetch_assoc()) {
    $stats['top_performers'][] = $row;
}

// Evaluation statistics
$evaluationStats = $conn->query("
    SELECT 
        COUNT(CASE WHEN evaluation_rank IS NOT NULL THEN 1 END) as evaluated_count,
        ROUND(AVG(evaluation_rank), 2) as average_rank,
        COUNT(CASE WHEN evaluation_rank >= 80 THEN 1 END) as excellent_count,
        COUNT(CASE WHEN evaluation_rank >= 60 AND evaluation_rank < 80 THEN 1 END) as good_count,
        COUNT(CASE WHEN evaluation_rank < 60 THEN 1 END) as needs_improvement_count
    FROM verified_students
    {$studentWhere}
")->fetch_assoc();

$stats['evaluation'] = [
    'evaluated_count' => intval($evaluationStats['evaluated_count']),
    'average_rank' => floatval($evaluationStats['average_rank'] ?? 0),
    'excellent_count' => intval($evaluationStats['excellent_count']),
    'good_count' => intval($evaluationStats['good_count']),
    'needs_improvement_count' => intval($evaluationStats['needs_improvement_count'])
];

// Performance score distribution
$performanceResult = $conn->query("
    SELECT performance_score, COUNT(*) as count
    FROM verified_students
    " . (!empty($studentWhere) ? str_replace('WHERE', 'WHERE', $studentWhere) . ' AND' : 'WHERE') . " performance_score IS NOT NULL
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

$stats['performance'] = [
    'assessed_count' => $performanceAssessed,
    'breakdown' => $performanceBreakdown
];

Response::success($stats);

