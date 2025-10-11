<?php

/**
 * POST /students/:id/reports
 * Submit report (student only)
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireRole(ROLE_STUDENT);
$studentId = intval($id);

if ($user['id'] !== $studentId) {
    Response::error('Forbidden', 403);
}

if (!isset($_FILES['report'])) {
    Response::error('Report file is required', 400);
}

$file = $_FILES['report'];

if (!in_array($file['type'], ALLOWED_DOCUMENT_TYPES)) {
    Response::error('Invalid file type. Only PDF and Word documents allowed', 400);
}

if ($file['size'] > MAX_FILE_SIZE) {
    Response::error('File too large. Maximum size is 10MB', 400);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = "report_" . $studentId . "_" . time() . "." . $ext;
$uploadPath = UPLOAD_DIR . "reports/" . $filename;

if (!file_exists(UPLOAD_DIR . "reports/")) {
    mkdir(UPLOAD_DIR . "reports/", 0777, true);
}

if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    Response::error('Failed to upload file', 500);
}

$conn = Database::getConnection();
$stmt = $conn->prepare("INSERT INTO submission_reports (student_id, report_file) VALUES (?, ?)");
$stmt->bind_param('is', $studentId, $filename);
$stmt->execute();

Response::success([
    'report_id' => $conn->insert_id,
    'filename' => $filename
], 'Report submitted successfully', 201);

