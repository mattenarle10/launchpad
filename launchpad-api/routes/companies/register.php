<?php

/**
 * POST /companies/register
 * Company registration (no auth required)
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

// Get form data
$companyName = $_POST['company_name'] ?? '';
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$contactNum = $_POST['contact_num'] ?? '';
$address = $_POST['address'] ?? '';
$website = $_POST['website'] ?? '';
$password = $_POST['password'] ?? '';

// Validate required fields
if (empty($companyName) || empty($username) || empty($email) || empty($address) || empty($password)) {
    Response::error('Missing required fields: company_name, username, email, address, password', 400);
}

// Validate password length
if (strlen($password) < PASSWORD_MIN_LENGTH) {
    Response::error('Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters', 400);
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Response::error('Invalid email format', 400);
}

$conn = Database::getConnection();

// Check if email already exists
$stmt = $conn->prepare("
    SELECT email FROM verified_companies WHERE email = ?
    UNION
    SELECT email FROM unverified_companies WHERE email = ?
");
$stmt->bind_param('ss', $email, $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    Response::error('Email already registered', 400);
}

// Check if username already exists
$stmt = $conn->prepare("
    SELECT username FROM verified_companies WHERE username = ?
    UNION
    SELECT username FROM unverified_companies WHERE username = ?
");
$stmt->bind_param('ss', $username, $username);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    Response::error('Username already taken', 400);
}

// Handle optional company logo upload
$logoFilename = null;
if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['company_logo'];
    
    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
        Response::error('Invalid logo file type. Only JPEG, PNG, and WebP images allowed', 400);
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        Response::error('Logo file too large. Maximum size is 10MB', 400);
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $logoFilename = "logo_" . $username . "_" . time() . "." . $ext;
    $uploadDir = UPLOAD_DIR . "company_logos/";
    $uploadPath = $uploadDir . $logoFilename;
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        Response::error('Failed to upload company logo', 500);
    }
}

// Handle optional MOA document upload
$moaFilename = null;
if (isset($_FILES['moa_document']) && $_FILES['moa_document']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['moa_document'];
    
    // Allow documents and images for MOA
    $allowedMoaTypes = array_merge(ALLOWED_DOCUMENT_TYPES, ALLOWED_IMAGE_TYPES);
    if (!in_array($file['type'], $allowedMoaTypes)) {
        Response::error('Invalid MOA file type. Allowed: PDF, Word, JPEG, PNG, WebP', 400);
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        Response::error('MOA file too large. Maximum size is 10MB', 400);
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $moaFilename = "moa_" . $username . "_" . time() . "." . $ext;
    $uploadDir = UPLOAD_DIR . "company_moas/";
    $uploadPath = $uploadDir . $moaFilename;
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        Response::error('Failed to upload MOA document', 500);
    }
}

// Hash password
$hashedPassword = Auth::hashPassword($password);

// Insert into unverified_companies (pending CDC approval)
$stmt = $conn->prepare("
    INSERT INTO unverified_companies 
    (company_name, username, email, contact_num, address, website, password, company_logo, moa_document)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    'sssssssss',
    $companyName,
    $username,
    $email,
    $contactNum,
    $address,
    $website,
    $hashedPassword,
    $logoFilename,
    $moaFilename
);
$stmt->execute();

Response::success([
    'company_id' => $conn->insert_id,
    'status' => 'pending',
    'message' => 'Company registration submitted! Waiting for CDC approval.'
], 'Registration successful', 201);

