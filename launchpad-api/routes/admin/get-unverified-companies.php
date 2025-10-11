<?php

/**
 * GET /admin/unverified/companies
 * List all unverified companies (CDC only)
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_CDC);

$conn = Database::getConnection();

$result = $conn->query("
    SELECT company_id, company_name, username, email, contact_num, address, website,
           company_logo, moa_document, created_at
    FROM unverified_companies
    ORDER BY created_at DESC
");

$companies = [];
while ($row = $result->fetch_assoc()) {
    $companies[] = $row;
}

Response::success($companies);

