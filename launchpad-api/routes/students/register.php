<?php

/**
 * POST /students/register
 * Student registration (no auth required)
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

// Enable error logging for debugging
error_log("=== Student Registration Attempt ===");

try {

// Get form data
$email = $_POST['email'] ?? '';
$idNumber = $_POST['id_number'] ?? '';
$firstName = $_POST['first_name'] ?? '';
$lastName = $_POST['last_name'] ?? '';
$course = $_POST['course'] ?? '';
$contactNum = $_POST['contact_num'] ?? '';
$password = $_POST['password'] ?? '';
// Ignore any provided company at signup; company is attached during verification
$companyName = '';

// Validate required fields
if (empty($email) || empty($idNumber) || empty($firstName) || empty($lastName) || empty($course) || empty($password)) {
    Response::error('Missing required fields: email, id_number, first_name, last_name, course, password', 400);
}

// Validate course
$allowedCourses = ['IT', 'COMSCI', 'EMC'];
if (!in_array(strtoupper($course), $allowedCourses)) {
    Response::error('Invalid course. Choose: IT, COMSCI, or EMC', 400);
}

// Validate password complexity
$passwordValidation = Auth::validatePasswordComplexity($password);
if (!$passwordValidation['valid']) {
    Response::error('Password requirements not met: ' . implode(', ', $passwordValidation['errors']), 400);
}

// Handle COR (Certificate of Registration) upload
if (!isset($_FILES['cor']) || $_FILES['cor']['error'] === UPLOAD_ERR_NO_FILE) {
    Response::error('Certificate of Registration (COR) is required', 400);
}

$file = $_FILES['cor'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    error_log("File upload error code: " . $file['error']);
    Response::error('File upload failed. Error code: ' . $file['error'], 400);
}

// Validate file size first (before type checking)
if ($file['size'] > MAX_FILE_SIZE) {
    Response::error('File too large. Maximum size is 10MB', 400);
}

if ($file['size'] == 0) {
    Response::error('Uploaded file is empty', 400);
}

// Validate file type - Be more lenient with MIME types
$allowedMimeTypes = [
    'image/jpeg',
    'image/jpg', 
    'image/png',
    'image/webp',
    'application/pdf',
    'application/octet-stream' // Mobile apps sometimes send this
];

// Also check by file extension as fallback
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

$isValidMime = in_array($file['type'], $allowedMimeTypes);
$isValidExt = in_array($ext, $allowedExtensions);

if (!$isValidMime && !$isValidExt) {
    error_log("Invalid file type. MIME: {$file['type']}, Extension: {$ext}");
    Response::error('Invalid file type. Only JPG, PNG, WebP images and PDF files allowed', 400);
}

// Get database connection
$conn = Database::getConnection();

// Check if email already exists
try {
    $stmt = $conn->prepare("
        SELECT email FROM verified_students WHERE email = ?
        UNION
        SELECT email FROM unverified_students WHERE email = ?
    ");
    
    if (!$stmt) {
        error_log("Failed to prepare email check query: " . $conn->error);
        Response::error('Database error: ' . $conn->error, 500);
    }
    
    $stmt->bind_param('ss', $email, $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        Response::error('Email already registered', 400);
    }
} catch (Exception $e) {
    error_log("Email check error: " . $e->getMessage());
    // Continue anyway - maybe tables don't exist yet
}

// Check if ID number already exists
try {
    $stmt = $conn->prepare("
        SELECT id_num FROM verified_students WHERE id_num = ?
        UNION
        SELECT id_num FROM unverified_students WHERE id_num = ?
    ");
    
    if (!$stmt) {
        error_log("Failed to prepare ID check query: " . $conn->error);
        Response::error('Database error: ' . $conn->error, 500);
    }
    
    $stmt->bind_param('ss', $idNumber, $idNumber);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        Response::error('ID number already registered', 400);
    }
} catch (Exception $e) {
    error_log("ID check error: " . $e->getMessage());
    // Continue anyway - maybe tables don't exist yet
}

// Upload COR
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = "cor_" . $idNumber . "_" . time() . "." . $ext;
$uploadDir = UPLOAD_DIR . "student_cors/";
$uploadPath = $uploadDir . $filename;

// Ensure directory exists
if (!is_dir($uploadDir)) {
    error_log("Creating upload directory: " . $uploadDir);
    if (!mkdir($uploadDir, 0755, true)) {
        error_log("Failed to create directory: " . $uploadDir . " - " . error_get_last()['message']);
        Response::error('Failed to create upload directory. Please contact administrator.', 500);
    }
    error_log("Directory created successfully");
}

// Move uploaded file
error_log("Attempting to upload file to: " . $uploadPath);
error_log("Temp file: " . $file['tmp_name']);
error_log("Upload directory writable: " . (is_writable($uploadDir) ? 'yes' : 'no'));

if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    $error = error_get_last();
    error_log("File upload failed: " . ($error ? $error['message'] : 'Unknown error'));
    Response::error('Failed to upload COR. Directory permissions issue.', 500);
}

error_log("File uploaded successfully: " . $filename);

try {
    // Hash password
    $hashedPassword = Auth::hashPassword($password);
} catch (Exception $e) {
    error_log("Password hashing failed: " . $e->getMessage());
    Response::error('Registration failed. Please try again.', 500);
}

// Insert into unverified_students (pending CDC approval)
// Try with specialization column first (newer schema), fall back to old schema
$insertSuccess = false;

// Try newer schema with specialization
$stmt = $conn->prepare("
    INSERT INTO unverified_students 
    (id_num, first_name, last_name, email, course, contact_num, specialization, password, company_name, cor)
    VALUES (?, ?, ?, ?, ?, ?, NULL, ?, NULL, ?)
");

if ($stmt) {
    $stmt->bind_param(
        'ssssssss',
        $idNumber,
        $firstName,
        $lastName,
        $email,
        $course,
        $contactNum,
        $hashedPassword,
        $filename
    );
    
    if ($stmt->execute()) {
        $insertSuccess = true;
        error_log("Student registered successfully (with specialization): " . $email);
    }
}

// If that failed, try old schema without specialization
if (!$insertSuccess) {
    $stmt = $conn->prepare("
        INSERT INTO unverified_students 
        (id_num, first_name, last_name, email, course, contact_num, password, company_name, cor)
        VALUES (?, ?, ?, ?, ?, ?, ?, NULL, ?)
    ");
    
    if (!$stmt) {
        error_log("Database prepare failed: " . $conn->error);
        Response::error('Database error: ' . $conn->error, 500);
    }
    
    $stmt->bind_param(
        'ssssssss',
        $idNumber,
        $firstName,
        $lastName,
        $email,
        $course,
        $contactNum,
        $hashedPassword,
        $filename
    );
    
    if (!$stmt->execute()) {
        error_log("Database insert failed: " . $stmt->error);
        Response::error('Failed to register: ' . $stmt->error, 500);
    }
    
    error_log("Student registered successfully (without specialization): " . $email);
}

Response::success([
    'student_id' => $conn->insert_id,
    'status' => 'pending',
    'message' => 'Registration submitted! Waiting for admin approval.'
], 'Registration successful', 201);

} catch (Throwable $e) {
    error_log("REGISTRATION ERROR: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    Response::error('Registration failed: ' . $e->getMessage(), 500);
}

