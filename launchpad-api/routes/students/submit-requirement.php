<?php

/**
 * POST /students/:id/requirements/submit
 * Student submits a requirement file (pre-deployment, deployment, or final)
 */

// Increase timeouts for file upload
set_time_limit(300); // 5 minutes
ini_set('max_execution_time', '300');
ini_set('memory_limit', '256M');
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');

// Debug logging - FIRST THING!
error_log("=== SUBMIT REQUIREMENT ENDPOINT HIT ===");
error_log("Timestamp: " . date('Y-m-d H:i:s'));
error_log("Method: " . ($method ?? 'undefined'));
error_log("Student ID from URL: " . ($id ?? 'null'));
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'none'));
error_log("Content-Length: " . ($_SERVER['CONTENT_LENGTH'] ?? 'none'));
error_log("Auth header: " . ($_SERVER['HTTP_AUTHORIZATION'] ?? 'none'));
error_log("========================================");

// Also output to response in case PHP dies
header('Content-Type: application/json');
ob_start();

if ($method !== 'POST') {
    error_log("ERROR: Method not POST");
    Response::error('Method not allowed', 405);
}

try {

$user = Auth::requireRole(ROLE_STUDENT);
$studentId = intval($id);

if ($user['id'] !== $studentId) {
    Response::error('Forbidden', 403);
}

// Get form data
$requirementType = $_POST['requirement_type'] ?? '';
$description = $_POST['description'] ?? '';

// Validate requirement type
$validTypes = ['pre_deployment', 'deployment', 'final_requirements'];
if (!in_array($requirementType, $validTypes)) {
    Response::error('Invalid requirement type. Must be one of: pre_deployment, deployment, final_requirements', 400);
}

// Handle file upload
if (!isset($_FILES['requirement_file'])) {
    Response::error('Requirement file is required', 400);
}

$file = $_FILES['requirement_file'];

// Allow documents and images
$allowedTypes = array_merge(ALLOWED_DOCUMENT_TYPES, ALLOWED_IMAGE_TYPES);
if (!in_array($file['type'], $allowedTypes)) {
    Response::error('Invalid file type. Allowed: PDF, Word, JPEG, PNG, WebP', 400);
}

if ($file['size'] > MAX_FILE_SIZE) {
    Response::error('File too large. Maximum size is 10MB', 400);
}

$conn = Database::getConnection();

// Store original filename
$originalFilename = $file['name'];

// Upload file with organized structure
$ext = pathinfo($originalFilename, PATHINFO_EXTENSION);
$storedFilename = "requirement_" . $studentId . "_" . $requirementType . "_" . time() . "." . $ext;
$uploadDir = UPLOAD_DIR . "requirements/" . $requirementType . "/";
$uploadPath = $uploadDir . $storedFilename;

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    Response::error('Failed to upload requirement file', 500);
}

// Insert requirement record
$stmt = $conn->prepare("
    INSERT INTO student_requirements (student_id, requirement_type, file_name, file_path, file_size, description)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param('isssis', $studentId, $requirementType, $originalFilename, $storedFilename, $file['size'], $description);
$stmt->execute();

Response::success([
    'requirement_id' => $conn->insert_id,
    'requirement_type' => $requirementType,
    'file_name' => $originalFilename,
    'message' => 'Requirement submitted successfully!'
], 'Requirement uploaded successfully', 201);

} catch (Exception $e) {
    error_log("=== EXCEPTION IN SUBMIT REQUIREMENT ===");
    error_log("Error: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
    error_log("=======================================");
    Response::error('Upload failed: ' . $e->getMessage(), 500);
}
