<?php

/**
 * POST /admin/verify/companies/:id
 * Verify company registration (CDC only)
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_CDC);

$companyId = intval($id);

$conn = Database::getConnection();

// Get unverified company
$stmt = $conn->prepare("SELECT * FROM unverified_companies WHERE company_id = ?");
$stmt->bind_param('i', $companyId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('Unverified company not found', 404);
}

$company = $result->fetch_assoc();

// Move to verified_companies
$stmt = $conn->prepare("
    INSERT INTO verified_companies 
    (company_name, username, email, contact_num, address, website, password, company_logo, moa_document)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    'sssssssss',
    $company['company_name'],
    $company['username'],
    $company['email'],
    $company['contact_num'],
    $company['address'],
    $company['website'],
    $company['password'],
    $company['company_logo'],
    $company['moa_document']
);
$stmt->execute();
$newCompanyId = $conn->insert_id;

// Delete from unverified
$stmt = $conn->prepare("DELETE FROM unverified_companies WHERE company_id = ?");
$stmt->bind_param('i', $companyId);
$stmt->execute();

// Send verification email notification
Mailer::sendVerificationApproved(
    $company['email'],
    $company['company_name'],
    'company'
);

Response::success(['company_id' => $newCompanyId], 'Company verified successfully');

