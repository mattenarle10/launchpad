<?php

/**
 * GET /companies/:id
 * Get company details
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

$companyId = intval($id);

$conn = Database::getConnection();
$stmt = $conn->prepare("
    SELECT company_id, name, email, contact_num, address, website, profile_pic, verified_at
    FROM verified_companies
    WHERE company_id = ?
");
$stmt->bind_param('i', $companyId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('Company not found', 404);
}

$company = $result->fetch_assoc();
Response::success($company);

