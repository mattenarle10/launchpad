<?php

/**
 * POST /profile/logo
 * Upload company logo for the authenticated company user
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireAuth();
if ($user['role'] !== ROLE_COMPANY) {
    Response::error('Only partner companies can upload a logo', 403);
}

if (!isset($_FILES['logo'])) {
    Response::error('No logo file uploaded', 400);
}

$file = $_FILES['logo'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    Response::error('Upload failed: ' . $file['error'], 400);
}

// Basic validation on file type and size (5MB limit)
$allowedMime = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp', 'image/gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if (!in_array($mime, $allowedMime)) {
    Response::error('Invalid image type', 400);
}

$maxSize = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $maxSize) {
    Response::error('File is too large (max 5MB)', 400);
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$companyId = intval($user['id']);
$filename = 'company_' . $companyId . '_' . time() . '.' . $ext;

$uploadDir = dirname(__DIR__, 2) . '/uploads/company_logos';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}
$destPath = $uploadDir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    Response::error('Failed to save uploaded file', 500);
}

// Update DB
$conn = Database::getConnection();
$stmt = $conn->prepare("UPDATE verified_companies SET company_logo = ? WHERE company_id = ?");
$stmt->bind_param('si', $filename, $companyId);
$stmt->execute();

Response::success(['company_logo' => $filename], 'Logo uploaded successfully');
