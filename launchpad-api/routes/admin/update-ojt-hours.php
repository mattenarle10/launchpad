<?php

/**
 * PUT /admin/ojt/:id/hours
 * CDC updates student's OJT hours
 */

if ($method !== 'PUT') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_CDC);

$progressId = intval($id);
$data = json_decode(file_get_contents('php://input'), true);

$completedHours = isset($data['completed_hours']) ? floatval($data['completed_hours']) : null;

if ($completedHours === null || $completedHours < 0) {
    Response::error('Valid completed_hours is required', 400);
}

$conn = Database::getConnection();

// Get current progress
$stmt = $conn->prepare("SELECT * FROM ojt_progress WHERE progress_id = ?");
$stmt->bind_param('i', $progressId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('OJT progress not found', 404);
}

$progress = $result->fetch_assoc();
$requiredHours = $progress['required_hours'];

// Determine new status based on hours
$newStatus = 'not_started';
if ($completedHours > 0 && $completedHours < $requiredHours) {
    $newStatus = 'in_progress';
} elseif ($completedHours >= $requiredHours) {
    $newStatus = 'completed';
}

// Update progress
$stmt = $conn->prepare("
    UPDATE ojt_progress 
    SET completed_hours = ?,
        status = ?,
        start_date = CASE 
            WHEN start_date IS NULL AND ? > 0 THEN NOW()
            ELSE start_date
        END,
        end_date = CASE 
            WHEN ? >= required_hours THEN NOW()
            ELSE NULL
        END,
        last_updated = NOW()
    WHERE progress_id = ?
");
$stmt->bind_param('dsddi', $completedHours, $newStatus, $completedHours, $completedHours, $progressId);
$stmt->execute();

// Get updated progress
$stmt = $conn->prepare("
    SELECT 
        p.*,
        s.id_num,
        s.first_name,
        s.last_name,
        s.course,
        ROUND((p.completed_hours / p.required_hours) * 100, 2) as completion_percentage
    FROM ojt_progress p
    JOIN verified_students s ON p.student_id = s.student_id
    WHERE p.progress_id = ?
");
$stmt->bind_param('i', $progressId);
$stmt->execute();
$updated = $stmt->get_result()->fetch_assoc();

Response::success($updated, 'OJT hours updated successfully');

