<?php

/**
 * DELETE /admin/reject/companies/:id
 * Reject company registration (CDC only)
 */

if ($method !== 'DELETE') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_CDC);

$companyId = intval($id);

$conn = Database::getConnection();

$stmt = $conn->prepare("DELETE FROM unverified_companies WHERE company_id = ?");
$stmt->bind_param('i', $companyId);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    Response::error('Company not found', 404);
}

Response::success(null, 'Company registration rejected');

