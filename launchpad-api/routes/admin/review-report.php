<?php

/**
 * POST /admin/reports/:id/review
 * CDC approves or rejects a daily report
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireRole(ROLE_CDC);
$reportId = intval($id);

$data = json_decode(file_get_contents('php://input'), true);

$action = $data['action'] ?? ''; // approve | reject
$rejectionReason = $data['rejection_reason'] ?? '';

if (!in_array($action, ['approve', 'reject'])) {
    Response::error('Action must be either "approve" or "reject"', 400);
}

if ($action === 'reject' && empty($rejectionReason)) {
    Response::error('Rejection reason is required when rejecting', 400);
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
    // Update report status
    $stmt = $conn->prepare("
        UPDATE daily_reports 
        SET status = 'approved', reviewed_by = ?, reviewed_at = NOW()
        WHERE report_id = ?
    ");
    $stmt->bind_param('ii', $user['id'], $reportId);
    $stmt->execute();
    
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
    $stmt->bind_param('dddsd', 
        $report['hours_requested'], 
        $report['hours_requested'], 
        $report['hours_requested'], 
        $report['report_date'],
        $report['hours_requested'],
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
        'hours_added' => $report['hours_requested'],
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

