<?php

/**
 * POST /companies/dtr/:id/validate
 * Company validates/approves DTR hours (can adjust +/-)
 * :id is the report_id
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireRole([ROLE_COMPANY, ROLE_PC]);
$reportId = intval($id);

// Get JSON body
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$hoursApproved = isset($input['hours_approved']) ? floatval($input['hours_approved']) : null;
$remarks = $input['remarks'] ?? '';

// Validate hours
if ($hoursApproved === null) {
    Response::error('hours_approved is required', 400);
}

if ($hoursApproved < 0 || $hoursApproved > 24) {
    Response::error('Hours must be between 0 and 24', 400);
}

$conn = Database::getConnection();

// Get the report and verify it belongs to a student of this company
$stmt = $conn->prepare("
    SELECT dr.*, s.company_id, s.first_name, s.last_name
    FROM daily_reports dr
    INNER JOIN verified_students s ON dr.student_id = s.student_id
    WHERE dr.report_id = ?
");
$stmt->bind_param('i', $reportId);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();

if (!$report) {
    Response::error('Report not found', 404);
}

if ($report['company_id'] !== $user['id']) {
    Response::error('This student is not assigned to your company', 403);
}

// Check if report is approved by CDC first
if ($report['status'] !== 'approved') {
    Response::error('Report must be approved by CDC before company validation', 400);
}

// Check if already validated
if ($report['company_validated']) {
    Response::error('This report has already been validated', 400);
}

// Update the report with company validation
$stmt = $conn->prepare("
    UPDATE daily_reports 
    SET 
        hours_approved = ?,
        company_validated = 1,
        company_validated_at = NOW(),
        company_validated_by = ?,
        company_remarks = ?
    WHERE report_id = ?
");
$stmt->bind_param('disi', $hoursApproved, $user['id'], $remarks, $reportId);

if (!$stmt->execute()) {
    Response::error('Failed to validate DTR', 500);
}

// Update OJT progress with the approved hours
// First, recalculate total approved hours for this student
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(
        CASE 
            WHEN company_validated = 1 THEN hours_approved
            WHEN status = 'approved' AND company_validated = 0 THEN hours_requested
            ELSE 0
        END
    ), 0) as total_hours
    FROM daily_reports
    WHERE student_id = ? AND status = 'approved'
");
$stmt->bind_param('i', $report['student_id']);
$stmt->execute();
$totalHours = $stmt->get_result()->fetch_assoc()['total_hours'];

// Update OJT progress
$stmt = $conn->prepare("
    UPDATE ojt_progress 
    SET 
        completed_hours = ?,
        status = CASE 
            WHEN ? >= required_hours THEN 'completed'
            WHEN ? > 0 THEN 'in_progress'
            ELSE 'not_started'
        END,
        last_updated = NOW()
    WHERE student_id = ?
");
$stmt->bind_param('dddi', $totalHours, $totalHours, $totalHours, $report['student_id']);
$stmt->execute();

$hoursDiff = $hoursApproved - floatval($report['hours_requested']);

Response::success([
    'report_id' => $reportId,
    'hours_requested' => floatval($report['hours_requested']),
    'hours_approved' => $hoursApproved,
    'difference' => round($hoursDiff, 2),
    'remarks' => $remarks,
    'student' => $report['first_name'] . ' ' . $report['last_name'],
    'new_total_hours' => round(floatval($totalHours), 2)
], 'DTR validated successfully');
