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

$result = $conn->query("SELECT COUNT(*) as total FROM verified_students");
$total = $result->fetch_assoc()['total'];

$stmt = $conn->prepare("
    SELECT 
        s.student_id, s.id_num, s.first_name, s.last_name, s.email, 
        s.course, s.contact_num, s.specialization, s.company_id, s.company_name, s.profile_pic, s.verified_at,
        s.evaluation_rank, s.performance_score,
        o.required_hours, o.completed_hours, o.status as ojt_status
    FROM verified_students s
    LEFT JOIN ojt_progress o ON s.student_id = o.student_id
    ORDER BY s.verified_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param('ii', $pageSize, $offset);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    unset($row['password']);
    $students[] = $row;
}

Response::paginated($students, $page, $pageSize, $total);

