<?php

/**
 * GET /companies/students/:id/dtr
 * Get DTR (Daily Time Records) for a student assigned to this company
 * PC can view all submitted reports and validate hours
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireRole([ROLE_COMPANY, ROLE_PC]);
$studentId = intval($id);

$conn = Database::getConnection();

// Verify student belongs to this company
$stmt = $conn->prepare("
    SELECT student_id, first_name, last_name, id_num, company_id 
    FROM verified_students 
    WHERE student_id = ? AND company_id = ?
");
$stmt->bind_param('ii', $studentId, $user['id']);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    Response::error('Student not found or not assigned to your company', 404);
}

// Get filter parameters
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$status = $_GET['status'] ?? ''; // pending, approved, rejected
$validated = $_GET['validated'] ?? ''; // 0, 1

// Build query
$whereConditions = ["dr.student_id = ?"];
$params = [$studentId];
$types = 'i';

// Filter by month/year
$whereConditions[] = "MONTH(dr.report_date) = ?";
$whereConditions[] = "YEAR(dr.report_date) = ?";
$params[] = intval($month);
$params[] = intval($year);
$types .= 'ii';

if (!empty($status)) {
    $whereConditions[] = "dr.status = ?";
    $params[] = $status;
    $types .= 's';
}

if ($validated !== '') {
    $whereConditions[] = "dr.company_validated = ?";
    $params[] = intval($validated);
    $types .= 'i';
}

$whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

$query = "
    SELECT 
        dr.report_id,
        dr.report_date,
        dr.hours_requested,
        dr.hours_approved,
        dr.description,
        dr.activity_type,
        dr.report_file,
        dr.status,
        dr.company_validated,
        dr.company_validated_at,
        dr.company_remarks,
        dr.submitted_at
    FROM daily_reports dr
    $whereClause
    ORDER BY dr.report_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$reports = [];
$totalRequested = 0;
$totalApproved = 0;
$pendingValidation = 0;

while ($row = $result->fetch_assoc()) {
    $totalRequested += floatval($row['hours_requested']);
    
    if ($row['hours_approved'] !== null) {
        $totalApproved += floatval($row['hours_approved']);
    } else if ($row['status'] === 'approved') {
        // If approved by CDC but not yet validated by company, use requested hours
        $totalApproved += floatval($row['hours_requested']);
    }
    
    if ($row['status'] === 'approved' && !$row['company_validated']) {
        $pendingValidation++;
    }
    
    $reports[] = $row;
}

// Get OJT progress
$stmt = $conn->prepare("
    SELECT required_hours, completed_hours, status 
    FROM ojt_progress 
    WHERE student_id = ?
");
$stmt->bind_param('i', $studentId);
$stmt->execute();
$progress = $stmt->get_result()->fetch_assoc();

Response::success([
    'student' => [
        'student_id' => $student['student_id'],
        'name' => $student['first_name'] . ' ' . $student['last_name'],
        'id_num' => $student['id_num']
    ],
    'reports' => $reports,
    'summary' => [
        'month' => intval($month),
        'year' => intval($year),
        'total_requested' => round($totalRequested, 2),
        'total_approved' => round($totalApproved, 2),
        'difference' => round($totalApproved - $totalRequested, 2),
        'pending_validation' => $pendingValidation,
        'report_count' => count($reports)
    ],
    'ojt_progress' => $progress
], 'Student DTR retrieved successfully');
