<?php

/**
 * GET /students
 * List all students (admin only)
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole([ROLE_CDC, ROLE_PC, ROLE_COMPANY]);

$conn = Database::getConnection();
$page = intval($_GET['page'] ?? 1);
$pageSize = min(intval($_GET['pageSize'] ?? DEFAULT_PAGE_SIZE), MAX_PAGE_SIZE);
$offset = ($page - 1) * $pageSize;

// Filter parameters
$semester = $_GET['semester'] ?? '';
$academicYear = $_GET['academic_year'] ?? '';
$course = $_GET['course'] ?? '';
$status = $_GET['status'] ?? ''; // 'completed', 'in_progress', 'not_started'

// Build WHERE clause dynamically
$whereConditions = [];
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

if (!empty($status)) {
    $whereConditions[] = "o.status = ?";
    $params[] = $status;
    $types .= 's';
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Count total with filters
$countQuery = "SELECT COUNT(*) as total FROM verified_students s LEFT JOIN ojt_progress o ON s.student_id = o.student_id $whereClause";
if (!empty($params)) {
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];
} else {
    $result = $conn->query($countQuery);
    $total = $result->fetch_assoc()['total'];
}

// Main query with filters
$query = "
    SELECT 
        s.student_id, s.id_num, s.first_name, s.last_name, s.email, 
        s.course, s.contact_num, s.specialization, s.semester, s.academic_year,
        s.company_id, s.company_name, s.profile_pic, s.verified_at,
        s.evaluation_rank, s.performance_score,
        o.required_hours, o.completed_hours, o.status as ojt_status
    FROM verified_students s
    LEFT JOIN ojt_progress o ON s.student_id = o.student_id
    $whereClause
    ORDER BY s.verified_at DESC
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
    $students[] = $row;
}

Response::paginated($students, $page, $pageSize, $total);

