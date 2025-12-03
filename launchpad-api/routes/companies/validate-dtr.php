<?php

/**
 * POST /companies/dtr/:id/validate
 * Company approves/rejects DTR and sets final hours
 * PC is the PRIMARY approver for DTR submissions
 * :id is the report_id
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireRole([ROLE_COMPANY, ROLE_PC]);
$reportId = intval($id);

// Get JSON body
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$action = $input['action'] ?? 'approve'; // approve | reject
$hoursApproved = isset($input['hours_approved']) ? floatval($input['hours_approved']) : null;
$remarks = $input['remarks'] ?? '';
$rejectionReason = $input['rejection_reason'] ?? $remarks;

// Validate action
if (!in_array($action, ['approve', 'reject'])) {
    Response::error('Action must be either "approve" or "reject"', 400);
}

// Validate hours for approval
if ($action === 'approve') {
    if ($hoursApproved === null) {
        Response::error('hours_approved is required for approval', 400);
    }
    if ($hoursApproved < 0 || $hoursApproved > 24) {
        Response::error('Hours must be between 0 and 24', 400);
    }
}

// Validate rejection reason
if ($action === 'reject' && empty($rejectionReason)) {
    Response::error('Rejection reason is required', 400);
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

// Check if already processed
if ($report['status'] !== 'pending') {
    Response::error('This report has already been reviewed (status: ' . $report['status'] . ')', 400);
}

if ($action === 'approve') {
    // Approve and set final hours
    $stmt = $conn->prepare("
        UPDATE daily_reports 
        SET 
            status = 'approved',
            hours_approved = ?,
            company_validated = 1,
            company_validated_at = NOW(),
            company_validated_by = ?,
            company_remarks = ?,
            reviewed_by = ?,
            reviewed_at = NOW()
        WHERE report_id = ?
    ");
    $stmt->bind_param('disii', $hoursApproved, $user['id'], $remarks, $user['id'], $reportId);

    if (!$stmt->execute()) {
        Response::error('Failed to approve DTR', 500);
    }

    // Recalculate total approved hours for this student
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(hours_approved), 0) as total_hours
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
            start_date = CASE 
                WHEN start_date IS NULL THEN ?
                ELSE start_date
            END,
            end_date = CASE 
                WHEN ? >= required_hours THEN NOW()
                ELSE NULL
            END,
            last_updated = NOW()
        WHERE student_id = ?
    ");
    $stmt->bind_param('dddsdi', $totalHours, $totalHours, $totalHours, $report['report_date'], $totalHours, $report['student_id']);
    $stmt->execute();

    $hoursDiff = $hoursApproved - floatval($report['hours_requested']);

    Response::success([
        'report_id' => $reportId,
        'action' => 'approved',
        'hours_requested' => floatval($report['hours_requested']),
        'hours_approved' => $hoursApproved,
        'difference' => round($hoursDiff, 2),
        'remarks' => $remarks,
        'student' => $report['first_name'] . ' ' . $report['last_name'],
        'new_total_hours' => round(floatval($totalHours), 2)
    ], 'DTR approved successfully');

} else {
    // Reject the report
    $stmt = $conn->prepare("
        UPDATE daily_reports 
        SET 
            status = 'rejected',
            rejection_reason = ?,
            company_validated = 1,
            company_validated_at = NOW(),
            company_validated_by = ?,
            company_remarks = ?,
            reviewed_by = ?,
            reviewed_at = NOW()
        WHERE report_id = ?
    ");
    $stmt->bind_param('sisii', $rejectionReason, $user['id'], $remarks, $user['id'], $reportId);

    if (!$stmt->execute()) {
        Response::error('Failed to reject DTR', 500);
    }

    Response::success([
        'report_id' => $reportId,
        'action' => 'rejected',
        'reason' => $rejectionReason,
        'student' => $report['first_name'] . ' ' . $report['last_name']
    ], 'DTR rejected');
}
