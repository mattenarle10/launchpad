<?php

/**
 * GET /admin/reports/approved
 * CDC views all approved daily reports
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_CDC);

$conn = Database::getConnection();

$page = intval($_GET['page'] ?? 1);
$pageSize = min(intval($_GET['pageSize'] ?? DEFAULT_PAGE_SIZE), MAX_PAGE_SIZE);
$offset = ($page - 1) * $pageSize;

// Optional filters
$studentId = isset($_GET['student_id']) ? intval($_GET['student_id']) : null;

// Build WHERE clause dynamically
$whereClauses = ["r.status = 'approved'"];
$params = [];
$types = '';

if (!empty($studentId)) {
    $whereClauses[] = 'r.student_id = ?';
    $params[] = $studentId;
    $types .= 'i';
}

$whereSql = 'WHERE ' . implode(' AND ', $whereClauses);

// Get total count with filters
$countSql = "
    SELECT COUNT(*) as count
    FROM daily_reports r
    JOIN verified_students s ON r.student_id = s.student_id
    $whereSql
";

if (!empty($params)) {
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['count'];
} else {
    $total = $conn->query($countSql)->fetch_assoc()['count'];
}

// Get approved reports with student info
$query = "
    SELECT 
        r.*,
        s.id_num,
        s.first_name,
        s.last_name,
        s.email,
        s.course,
        s.company_name
    FROM daily_reports r
    JOIN verified_students s ON r.student_id = s.student_id
    $whereSql
    ORDER BY r.reviewed_at DESC
    LIMIT ? OFFSET ?
";

$params[] = $pageSize;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}

Response::paginated($reports, $page, $pageSize, $total);

