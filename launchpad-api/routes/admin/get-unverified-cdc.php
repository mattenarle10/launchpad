<?php

/**
 * GET /admin/unverified/cdc
 * List all unverified CDC users (CDC only)
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_CDC);

$conn = Database::getConnection();

$result = $conn->query("\n    SELECT id, username, email, first_name, last_name, created_at\n    FROM unverified_cdc_users\n    ORDER BY created_at DESC\n");

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

Response::success($users);
