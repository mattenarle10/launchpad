<?php

/**
 * GET /companies/dtr/pending
 * Get all pending DTR reports for company's students
 * PC uses this to see what needs approval
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireRole([ROLE_COMPANY, ROLE_PC]);
$companyId = $user['id'];

$conn = Database::getConnection();

// Get query params for filtering
$month = isset($_GET['month']) ? intval($_GET['month']) : null;
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$studentId = isset($_GET['student_id']) ? intval($_GET['student_id']) : null;

// Build query
$sql = "
    SELECT 
        dr.report_id,
        dr.student_id,
        dr.report_date,
        dr.hours_requested,
        dr.accomplishments,
        dr.status,
        dr.file_path,
        dr.created_at,
        s.first_name,
        s.last_name,
        s.id_num,
        s.course
    FROM daily_reports dr
    INNER JOIN verified_students s ON dr.student_id = s.student_id
    WHERE s.company_id = ? AND dr.status = 'pending'
";

$params = [$companyId];
$types = 'i';

if ($month !== null) {
    $sql .= " AND MONTH(dr.report_date) = ?";
    $params[] = $month;
    $types .= 'i';
}

if ($year !== null) {
    $sql .= " AND YEAR(dr.report_date) = ?";
    $params[] = $year;
    $types .= 'i';
}

if ($studentId !== null) {
    $sql .= " AND dr.student_id = ?";
    $params[] = $studentId;
    $types .= 'i';
}

$sql .= " ORDER BY dr.report_date DESC, dr.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = [
        'report_id' => intval($row['report_id']),
        'student_id' => intval($row['student_id']),
        'student_name' => $row['first_name'] . ' ' . $row['last_name'],
        'id_num' => $row['id_num'],
        'course' => $row['course'],
        'report_date' => $row['report_date'],
        'hours_requested' => floatval($row['hours_requested']),
        'accomplishments' => $row['accomplishments'],
        'file_path' => $row['file_path'],
        'created_at' => $row['created_at']
    ];
}

// Get summary stats
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as pending_count,
        COALESCE(SUM(hours_requested), 0) as total_pending_hours
    FROM daily_reports dr
    INNER JOIN verified_students s ON dr.student_id = s.student_id
    WHERE s.company_id = ? AND dr.status = 'pending'
");
$stmt->bind_param('i', $companyId);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

Response::success([
    'reports' => $reports,
    'summary' => [
        'pending_count' => intval($stats['pending_count']),
        'total_pending_hours' => floatval($stats['total_pending_hours'])
    ]
], 'Pending DTR reports retrieved');
