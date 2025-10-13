<?php

/**
 * POST /students/register
 * Student registration (no auth required)
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

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

// Validate password length
if (strlen($password) < PASSWORD_MIN_LENGTH) {
    Response::error('Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters', 400);
}

// Handle COR (Certificate of Registration) upload
if (!isset($_FILES['cor'])) {
    Response::error('Certificate of Registration (COR) is required', 400);
}

$file = $_FILES['cor'];

// Validate file type (allow images and PDFs)
$allowedTypes = array_merge(ALLOWED_IMAGE_TYPES, ['application/pdf']);
if (!in_array($file['type'], $allowedTypes)) {
    Response::error('Invalid file type. Only JPEG, PNG, WebP images and PDF files allowed', 400);
}

// Validate file size
if ($file['size'] > MAX_FILE_SIZE) {
    Response::error('File too large. Maximum size is 10MB', 400);
}

$conn = Database::getConnection();

// Check if email already exists
$stmt = $conn->prepare("
    SELECT email FROM verified_students WHERE email = ?
    UNION
    SELECT email FROM unverified_students WHERE email = ?
");
$stmt->bind_param('ss', $email, $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    Response::error('Email already registered', 400);
}

// Check if ID number already exists
$stmt = $conn->prepare("
    SELECT id_num FROM verified_students WHERE id_num = ?
    UNION
    SELECT id_num FROM unverified_students WHERE id_num = ?
");
$stmt->bind_param('ss', $idNumber, $idNumber);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    Response::error('ID number already registered', 400);
}

// Upload COR
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = "cor_" . $idNumber . "_" . time() . "." . $ext;
$uploadDir = UPLOAD_DIR . "student_cors/";
$uploadPath = $uploadDir . $filename;

// Ensure directory exists
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        Response::error('Failed to create upload directory', 500);
    }
}

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    Response::error('Failed to upload COR. Check directory permissions.', 500);
}

// Hash password
$hashedPassword = Auth::hashPassword($password);

// Insert into unverified_students (pending CDC approval)
$stmt = $conn->prepare("
    INSERT INTO unverified_students 
    (id_num, first_name, last_name, email, course, contact_num, password, company_name, cor)
    VALUES (?, ?, ?, ?, ?, ?, ?, NULL, ?)
");
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
$stmt->execute();

Response::success([
    'student_id' => $conn->insert_id,
    'status' => 'pending',
    'message' => 'Registration submitted! Waiting for admin approval.'
], 'Registration successful', 201);

