<?php

/**
 * GET /admin/reports/pending
 * CDC views all pending daily reports
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_CDC);

$conn = Database::getConnection();

$page = intval($_GET['page'] ?? 1);
$pageSize = min(intval($_GET['pageSize'] ?? DEFAULT_PAGE_SIZE), MAX_PAGE_SIZE);
$offset = ($page - 1) * $pageSize;

// Get total count
$total = $conn->query("SELECT COUNT(*) as count FROM daily_reports WHERE status = 'pending'")->fetch_assoc()['count'];

// Get pending reports with student info
$stmt = $conn->prepare("
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
    WHERE r.status = 'pending'
    ORDER BY r.submitted_at ASC
    LIMIT ? OFFSET ?
");
$stmt->bind_param('ii', $pageSize, $offset);
$stmt->execute();
$result = $stmt->get_result();

$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}

Response::paginated($reports, $page, $pageSize, $total);

