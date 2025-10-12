<?php

/**
 * POST /students/:id/reports/daily
 * Student submits daily report with hours (pending CDC approval)
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireRole(ROLE_STUDENT);
$studentId = intval($id);

if ($user['id'] !== $studentId) {
    Response::error('Forbidden', 403);
}

// Get form data
$reportDate = $_POST['report_date'] ?? date('Y-m-d');
$hoursRequested = floatval($_POST['hours_requested'] ?? 0);
$description = $_POST['description'] ?? '';
$activityType = $_POST['activity_type'] ?? '';

// Validate required fields
if (empty($description)) {
    Response::error('Description is required', 400);
}

// Validate hours (must be greater than 0 and up to 24)
if ($hoursRequested <= 0 || $hoursRequested > 24) {
    Response::error('Hours must be between 0.1 and 24. Received: ' . $hoursRequested, 400);
}

// Validate date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $reportDate)) {
    Response::error('Invalid date format. Use YYYY-MM-DD', 400);
}

// Check if date is not in the future
if (strtotime($reportDate) > time()) {
    Response::error('Cannot submit report for future dates', 400);
}

// Handle report file upload
if (!isset($_FILES['report_file'])) {
    Response::error('Report file is required', 400);
}

$file = $_FILES['report_file'];

// Allow documents and images
$allowedTypes = array_merge(ALLOWED_DOCUMENT_TYPES, ALLOWED_IMAGE_TYPES);
if (!in_array($file['type'], $allowedTypes)) {
    Response::error('Invalid file type. Allowed: PDF, Word, JPEG, PNG, WebP', 400);
}

if ($file['size'] > MAX_FILE_SIZE) {
    Response::error('File too large. Maximum size is 10MB', 400);
}

$conn = Database::getConnection();

// Check if report already submitted for this date
$stmt = $conn->prepare("
    SELECT report_id, status FROM daily_reports 
    WHERE student_id = ? AND report_date = ?
");
$stmt->bind_param('is', $studentId, $reportDate);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if ($existing) {
    if ($existing['status'] === 'pending') {
        Response::error('You already have a pending report for this date', 400);
    } else if ($existing['status'] === 'approved') {
        Response::error('Report for this date has already been approved', 400);
    } else {
        Response::error('You can resubmit after CDC reviews your previous report for this date', 400);
    }
}

// Upload file
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = "daily_report_" . $studentId . "_" . $reportDate . "_" . time() . "." . $ext;
$uploadDir = UPLOAD_DIR . "daily_reports/";
$uploadPath = $uploadDir . $filename;

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    Response::error('Failed to upload report file', 500);
}

// Insert daily report
$stmt = $conn->prepare("
    INSERT INTO daily_reports (student_id, report_date, hours_requested, description, activity_type, report_file, status)
    VALUES (?, ?, ?, ?, ?, ?, 'pending')
");
$stmt->bind_param('isdsss', $studentId, $reportDate, $hoursRequested, $description, $activityType, $filename);
$stmt->execute();

Response::success([
    'report_id' => $conn->insert_id,
    'status' => 'pending',
    'message' => 'Report submitted! Waiting for CDC approval.'
], 'Report submitted successfully', 201);

