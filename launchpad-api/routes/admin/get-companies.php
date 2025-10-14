<?php

/**
 * GET /admin/companies
 * Get all partner companies (for CDC)
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_CDC);

$conn = Database::getConnection();

// Get all verified companies
$stmt = $conn->prepare("
    SELECT 
        company_id,
        company_name,
        username,
        email,
        contact_num,
        address,
        website,
        company_logo,
        verified_at
    FROM verified_companies
    ORDER BY company_name ASC
");

$stmt->execute();
$result = $stmt->get_result();
$companies = [];

while ($row = $result->fetch_assoc()) {
    $companies[] = $row;
}

Response::success(['data' => $companies]);
