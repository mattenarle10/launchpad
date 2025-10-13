<?php

/**
 * PUT /admin/companies/:id
 * Edit company information (CDC admin only)
 */

if ($method !== 'PUT') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole([ROLE_CDC]);

$conn = Database::getConnection();
$companyId = intval($id);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate company exists
$stmt = $conn->prepare("SELECT company_id FROM verified_companies WHERE company_id = ?");
$stmt->bind_param('i', $companyId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('Company not found', 404);
}

// Build dynamic update query based on provided fields
$updates = [];
$params = [];
$types = '';

$allowedFields = [
    'company_name' => 's',
    'email' => 's',
    'contact_num' => 's',
    'address' => 's',
    'website' => 's'
];

foreach ($allowedFields as $field => $type) {
    if (isset($input[$field])) {
        $updates[] = "$field = ?";
        $params[] = $input[$field];
        $types .= $type;
    }
}

if (empty($updates)) {
    Response::error('No valid fields to update', 400);
}

// Validate email if provided
if (isset($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    Response::error('Invalid email format', 400);
}

// Check if email is already taken by another company
if (isset($input['email'])) {
    $stmt = $conn->prepare("SELECT company_id FROM verified_companies WHERE email = ? AND company_id != ?");
    $stmt->bind_param('si', $input['email'], $companyId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        Response::error('Email already exists', 409);
    }
}

// Execute update
$sql = "UPDATE verified_companies SET " . implode(', ', $updates) . " WHERE company_id = ?";
$params[] = $companyId;
$types .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if (!$stmt->execute()) {
    Response::error('Failed to update company', 500);
}

// Get updated company data
$stmt = $conn->prepare("
    SELECT company_id, company_name, username, email, contact_num, address, 
           website, company_logo, moa_document, verified_at
    FROM verified_companies 
    WHERE company_id = ?
");
$stmt->bind_param('i', $companyId);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();

Response::success($company, 'Company updated successfully');

