<?php

/**
 * GET /admin/completed/students
 * Get all students who have completed their OJT (CDC only)
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole([ROLE_CDC]);

$conn = Database::getConnection();
$page = intval($_GET['page'] ?? 1);
$pageSize = min(intval($_GET['pageSize'] ?? DEFAULT_PAGE_SIZE), MAX_PAGE_SIZE);
$offset = ($page - 1) * $pageSize;

// Filter parameters
$semester = $_GET['semester'] ?? '';
$academicYear = $_GET['academic_year'] ?? '';
$course = $_GET['course'] ?? '';
$companyId = $_GET['company_id'] ?? '';

// Build WHERE clause dynamically
$whereConditions = ["o.status = 'completed'"];
$params = [];
$types = '';

if (!empty($semester)) {
    $whereConditions[] = "s.semester = ?";
    $params[] = $semester;
    $types .= 's';
}

if (!empty($academicYear)) {
    $whereConditions[] = "s.academic_year = ?";
    $params[] = $academicYear;
    $types .= 's';
}

if (!empty($course)) {
    $whereConditions[] = "s.course = ?";
    $params[] = strtoupper($course);
    $types .= 's';
}

if (!empty($companyId)) {
    $whereConditions[] = "s.company_id = ?";
    $params[] = intval($companyId);
    $types .= 'i';
}

$whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

// Count total completed students with filters
$countQuery = "
    SELECT COUNT(*) as total 
    FROM verified_students s 
    INNER JOIN ojt_progress o ON s.student_id = o.student_id 
    $whereClause
";

if (!empty($params)) {
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];
} else {
    $result = $conn->query($countQuery);
    $total = $result->fetch_assoc()['total'];
}

// Main query
$query = "
    SELECT 
        s.student_id, s.id_num, s.first_name, s.last_name, s.email, 
        s.course, s.contact_num, s.specialization, s.semester, s.academic_year,
        s.company_id, s.company_name, s.profile_pic, s.verified_at,
        s.evaluation_rank, s.performance_score,
        o.required_hours, o.completed_hours, o.status as ojt_status,
        o.start_date, o.end_date,
        c.company_name as company_full_name
    FROM verified_students s
    INNER JOIN ojt_progress o ON s.student_id = o.student_id
    LEFT JOIN verified_companies c ON s.company_id = c.company_id
    $whereClause
    ORDER BY o.end_date DESC, s.last_name ASC
    LIMIT ? OFFSET ?
";

$params[] = $pageSize;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    unset($row['password']);
    
    // Calculate completion date info
    if ($row['end_date']) {
        $row['completion_date'] = $row['end_date'];
        $row['days_since_completion'] = floor((time() - strtotime($row['end_date'])) / 86400);
    }
    
    $students[] = $row;
}

// Get summary stats
$statsQuery = "
    SELECT 
        COUNT(*) as total_completed,
        AVG(o.completed_hours) as avg_hours,
        AVG(s.evaluation_rank) as avg_evaluation
    FROM verified_students s
    INNER JOIN ojt_progress o ON s.student_id = o.student_id
    WHERE o.status = 'completed'
";
$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();

Response::success([
    'students' => $students,
    'pagination' => [
        'page' => $page,
        'pageSize' => $pageSize,
        'total' => intval($total),
        'totalPages' => ceil($total / $pageSize)
    ],
    'summary' => [
        'total_completed' => intval($stats['total_completed']),
        'average_hours' => round(floatval($stats['avg_hours']), 2),
        'average_evaluation' => round(floatval($stats['avg_evaluation']), 2)
    ]
], 'Completed students retrieved successfully');
