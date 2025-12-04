<?php

/**
 * DELETE /admin/reject/cdc/:id
 * Reject CDC user registration (CDC only)
 */

if ($method !== 'DELETE') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_CDC);

$cdcId = intval($id);

$conn = Database::getConnection();

$stmt = $conn->prepare("DELETE FROM unverified_cdc_users WHERE id = ?");
$stmt->bind_param('i', $cdcId);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    Response::error('CDC user not found', 404);
}

Response::success(null, 'CDC registration rejected');
