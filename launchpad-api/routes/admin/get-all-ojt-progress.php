<?php

/**
 * GET /admin/ojt/progress
 * CDC views all students' OJT progress (dashboard)
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_CDC);

$conn = Database::getConnection();

$statusFilter = $_GET['status'] ?? 'all'; // all, not_started, in_progress, completed
$page = intval($_GET['page'] ?? 1);
$pageSize = min(intval($_GET['pageSize'] ?? DEFAULT_PAGE_SIZE), MAX_PAGE_SIZE);
$offset = ($page - 1) * $pageSize;

// Build query with optional status filter
$whereClause = $statusFilter !== 'all' ? "WHERE p.status = ?" : "";

$countQuery = "
    SELECT COUNT(*) as total 
    FROM ojt_progress p
    $whereClause
";

if ($statusFilter !== 'all') {
    $stmt = $conn->prepare($countQuery);
    $stmt->bind_param('s', $statusFilter);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $total = $conn->query($countQuery)->fetch_assoc()['total'];
}

// Get progress data with student info
$query = "
    SELECT 
        p.*,
        s.id_num,
        s.first_name,
        s.last_name,
        s.email,
        s.course,
        s.company_name,
        ROUND((p.completed_hours / p.required_hours) * 100, 2) as completion_percentage,
        (p.required_hours - p.completed_hours) as remaining_hours
    FROM ojt_progress p
    JOIN verified_students s ON p.student_id = s.student_id
    $whereClause
    ORDER BY p.last_updated DESC
    LIMIT ? OFFSET ?
";

if ($statusFilter !== 'all') {
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sii', $statusFilter, $pageSize, $offset);
} else {
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $pageSize, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

$progressList = [];
while ($row = $result->fetch_assoc()) {
    $progressList[] = $row;
}

// Get summary stats
$stats = [
    'total_students' => $conn->query("SELECT COUNT(*) as count FROM ojt_progress")->fetch_assoc()['count'],
    'not_started' => $conn->query("SELECT COUNT(*) as count FROM ojt_progress WHERE status = 'not_started'")->fetch_assoc()['count'],
    'in_progress' => $conn->query("SELECT COUNT(*) as count FROM ojt_progress WHERE status = 'in_progress'")->fetch_assoc()['count'],
    'completed' => $conn->query("SELECT COUNT(*) as count FROM ojt_progress WHERE status = 'completed'")->fetch_assoc()['count'],
];

Response::paginated($progressList, $page, $pageSize, $total);

