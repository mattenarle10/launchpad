<?php

/**
 * PUT /profile
 * Update current user's profile
 */

if ($method !== 'PUT') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireAuth();
$conn = Database::getConnection();
$data = json_decode(file_get_contents('php://input'), true);

$role = $user['role'];
$userId = $user['id'];

if ($role === ROLE_CDC) {
    // Update CDC user profile (only email)
    $email = $data['email'] ?? null;
    
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        Response::error('Valid email is required', 400);
    }
    
    // Check if email is already taken by another user
    $stmt = $conn->prepare("SELECT id FROM cdc_users WHERE email = ? AND id != ?");
    $stmt->bind_param('si', $email, $userId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        Response::error('Email already in use', 400);
    }
    
    $stmt = $conn->prepare("UPDATE cdc_users SET email = ? WHERE id = ?");
    $stmt->bind_param('si', $email, $userId);
    $stmt->execute();
    
    Response::success(['message' => 'Profile updated successfully']);
    
} elseif ($role === ROLE_COMPANY) {
    // Update company profile
    $companyName = $data['company_name'] ?? null;
    $companyAddress = $data['company_address'] ?? null;
    $companyWebsite = $data['company_website'] ?? null;
    $contactPerson = $data['contact_person'] ?? null;
    $contactEmail = $data['contact_email'] ?? null;
    $contactPhone = $data['contact_phone'] ?? null;
    $industry = $data['industry'] ?? null;
    $companySize = $data['company_size'] ?? null;
    $description = $data['description'] ?? null;
    
    // Validate required fields
    if (!$companyName || !$contactPerson || !$contactEmail) {
        Response::error('Company name, contact person, and contact email are required', 400);
    }
    
    if (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        Response::error('Valid contact email is required', 400);
    }
    
    // Check if email is already taken by another company
    $stmt = $conn->prepare("SELECT company_id FROM verified_companies WHERE contact_email = ? AND company_id != ?");
    $stmt->bind_param('si', $contactEmail, $userId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        Response::error('Contact email already in use', 400);
    }
    
    $stmt = $conn->prepare("
        UPDATE verified_companies 
        SET 
            company_name = ?,
            company_address = ?,
            company_website = ?,
            contact_person = ?,
            contact_email = ?,
            contact_phone = ?,
            industry = ?,
            company_size = ?,
            description = ?
        WHERE company_id = ?
    ");
    $stmt->bind_param(
        'sssssssssi',
        $companyName,
        $companyAddress,
        $companyWebsite,
        $contactPerson,
        $contactEmail,
        $contactPhone,
        $industry,
        $companySize,
        $description,
        $userId
    );
    $stmt->execute();
    
    Response::success(['message' => 'Profile updated successfully']);
    
} else {
    Response::error('Invalid user role', 400);
}

