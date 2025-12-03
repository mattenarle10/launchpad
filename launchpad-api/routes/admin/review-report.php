<?php

/**
 * POST /admin/reports/:id/review
 * CDC approves or rejects a daily report
 * 
 * NOTE: Primary DTR approval is now handled by Partner Companies (PC).
 * CDC can still approve/reject for monitoring purposes, but PC is the main approver.
 * Use /companies/dtr/:id/validate for PC approval flow.
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireRole(ROLE_CDC);
$reportId = intval($id);

$data = json_decode(file_get_contents('php://input'), true);

$action = $data['action'] ?? ''; // approve | reject
$rejectionReason = $data['rejection_reason'] ?? '';
$approvedHours = isset($data['approved_hours']) ? floatval($data['approved_hours']) : null;

if (!in_array($action, ['approve', 'reject'])) {
    Response::error('Action must be either "approve" or "reject"', 400);
}

if ($action === 'reject' && empty($rejectionReason)) {
    Response::error('Rejection reason is required when rejecting', 400);
}

if ($action === 'approve' && $approvedHours !== null) {
    if ($approvedHours <= 0 || $approvedHours > 24) {
        Response::error('Approved hours must be between 0.1 and 24', 400);
    }
}

$conn = Database::getConnection();

// Get the report
$stmt = $conn->prepare("SELECT * FROM daily_reports WHERE report_id = ?");
$stmt->bind_param('i', $reportId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('Report not found', 404);
}

$report = $result->fetch_assoc();

if ($report['status'] !== 'pending') {
    Response::error('This report has already been reviewed', 400);
}

if ($action === 'approve') {
    // Use CDC-approved hours if provided, otherwise use student's requested hours
    $hoursToAdd = $approvedHours !== null ? $approvedHours : $report['hours_requested'];
    
    // Update report status
    $stmt = $conn->prepare("
        UPDATE daily_reports 
        SET status = 'approved', reviewed_by = ?, reviewed_at = NOW()
        WHERE report_id = ?
    ");
    $stmt->bind_param('ii', $user['id'], $reportId);
    $stmt->execute();
    
    // Check if OJT progress exists (should have been created during verification)
    $stmt = $conn->prepare("SELECT progress_id FROM ojt_progress WHERE student_id = ?");
    $stmt->bind_param('i', $report['student_id']);
    $stmt->execute();
    $progressExists = $stmt->get_result()->fetch_assoc();
    
    if (!$progressExists) {
        Response::error('OJT progress not found for this student. Student may need to be re-verified.', 500);
    }
    
    // Add hours to OJT progress
    $stmt = $conn->prepare("
        UPDATE ojt_progress 
        SET completed_hours = completed_hours + ?,
            status = CASE 
                WHEN completed_hours + ? >= required_hours THEN 'completed'
                WHEN completed_hours + ? > 0 THEN 'in_progress'
                ELSE 'not_started'
            END,
            start_date = CASE 
                WHEN start_date IS NULL THEN ?
                ELSE start_date
            END,
            end_date = CASE 
                WHEN completed_hours + ? >= required_hours THEN NOW()
                ELSE NULL
            END
        WHERE student_id = ?
    ");
    $stmt->bind_param('dddsdi', 
        $hoursToAdd, 
        $hoursToAdd, 
        $hoursToAdd, 
        $report['report_date'],
        $hoursToAdd,
        $report['student_id']
    );
    $stmt->execute();
    
    // Get updated progress
    $stmt = $conn->prepare("SELECT * FROM ojt_progress WHERE student_id = ?");
    $stmt->bind_param('i', $report['student_id']);
    $stmt->execute();
    $progress = $stmt->get_result()->fetch_assoc();
    
    Response::success([
        'report_id' => $reportId,
        'action' => 'approved',
        'hours_requested' => $report['hours_requested'],
        'hours_added' => $hoursToAdd,
        'new_total_hours' => $progress['completed_hours'],
        'completion_percentage' => ($progress['completed_hours'] / $progress['required_hours']) * 100,
        'status' => $progress['status']
    ], 'Report approved and hours added to student progress');
    
} else {
    // Reject report
    $stmt = $conn->prepare("
        UPDATE daily_reports 
        SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW(), rejection_reason = ?
        WHERE report_id = ?
    ");
    $stmt->bind_param('isi', $user['id'], $rejectionReason, $reportId);
    $stmt->execute();
    
    Response::success([
        'report_id' => $reportId,
        'action' => 'rejected',
        'reason' => $rejectionReason
    ], 'Report rejected');
}

