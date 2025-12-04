<?php

/**
 * GET /companies/students/completed
 * Get list of students who have completed their OJT at this company
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

// Get query parameters for filtering
$academicYear = isset($_GET['academic_year']) ? $_GET['academic_year'] : null;
$semester = isset($_GET['semester']) ? $_GET['semester'] : null;
$course = isset($_GET['course']) ? $_GET['course'] : null;

// Build query
$query = "
    SELECT 
        s.student_id,
        s.id_num,
        s.first_name,
        s.last_name,
        s.email,
        s.course,
        s.specialization,
        s.semester,
        s.academic_year,
        s.evaluation_rank,
        s.performance_score,
        p.required_hours,
        p.completed_hours,
        p.status as ojt_status,
        p.end_date AS completion_date,
        (SELECT COUNT(*) FROM daily_reports dr WHERE dr.student_id = s.student_id AND dr.status = 'approved') as total_reports,
        (SELECT AVG(se.evaluation_score) FROM student_evaluations se WHERE se.student_id = s.student_id) as avg_evaluation
    FROM verified_students s
    LEFT JOIN ojt_progress p ON s.student_id = p.student_id
    WHERE s.company_id = ?
    AND p.status = 'completed'
";

$params = [$companyId];
$types = 'i';

// Add filters
if ($academicYear) {
    $query .= " AND s.academic_year = ?";
    $params[] = $academicYear;
    $types .= 's';
}

if ($semester) {
    $query .= " AND s.semester = ?";
    $params[] = $semester;
    $types .= 's';
}

if ($course) {
    $query .= " AND s.course = ?";
    $params[] = $course;
    $types .= 's';
}

$query .= " ORDER BY p.end_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = [
        'student_id' => intval($row['student_id']),
        'id_num' => $row['id_num'],
        'first_name' => $row['first_name'],
        'last_name' => $row['last_name'],
        'full_name' => $row['first_name'] . ' ' . $row['last_name'],
        'email' => $row['email'],
        'course' => $row['course'],
        'specialization' => $row['specialization'],
        'semester' => $row['semester'],
        'academic_year' => $row['academic_year'],
        'required_hours' => intval($row['required_hours']),
        'completed_hours' => floatval($row['completed_hours']),
        'completion_date' => $row['completion_date'],
        'total_reports' => intval($row['total_reports']),
        'evaluation_rank' => $row['evaluation_rank'] ? intval($row['evaluation_rank']) : null,
        'performance_score' => $row['performance_score'],
        'avg_evaluation' => $row['avg_evaluation'] ? round(floatval($row['avg_evaluation']), 1) : null
    ];
}

// Get summary stats
$summaryQuery = "
    SELECT 
        COUNT(*) as total_completed,
        AVG(p.completed_hours) as avg_hours,
        AVG(s.evaluation_rank) as avg_evaluation
    FROM verified_students s
    LEFT JOIN ojt_progress p ON s.student_id = p.student_id
    WHERE s.company_id = ?
    AND p.status = 'completed'
";

$stmt = $conn->prepare($summaryQuery);
$stmt->bind_param('i', $companyId);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();

Response::success([
    'students' => $students,
    'summary' => [
        'total_completed' => intval($summary['total_completed']),
        'avg_hours' => $summary['avg_hours'] ? round(floatval($summary['avg_hours']), 1) : 0,
        'avg_evaluation' => $summary['avg_evaluation'] ? round(floatval($summary['avg_evaluation']), 1) : null
    ],
    'filters' => [
        'academic_year' => $academicYear,
        'semester' => $semester,
        'course' => $course
    ]
], 'Completed students retrieved successfully');
