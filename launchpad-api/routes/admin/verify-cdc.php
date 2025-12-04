<?php

/**
 * POST /admin/verify/cdc/:id
 * Verify CDC user registration (CDC only)
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_CDC);

$cdcId = intval($id);

$conn = Database::getConnection();

// Get unverified CDC user
$stmt = $conn->prepare("SELECT * FROM unverified_cdc_users WHERE id = ?");
$stmt->bind_param('i', $cdcId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('Unverified CDC user not found', 404);
}

$user = $result->fetch_assoc();

// Move to cdc_users (role defaults to 'staff')
$stmt = $conn->prepare("\n    INSERT INTO cdc_users \n    (username, email, first_name, last_name, password)\n    VALUES (?, ?, ?, ?, ?)\n");
$stmt->bind_param(
    'sssss',
    $user['username'],
    $user['email'],
    $user['first_name'],
    $user['last_name'],
    $user['password']
);
$stmt->execute();
$newId = $conn->insert_id;

// Delete from unverified table
$stmt = $conn->prepare("DELETE FROM unverified_cdc_users WHERE id = ?");
$stmt->bind_param('i', $cdcId);
$stmt->execute();

// Send verification email notification
$cdcName = $user['first_name'] . ' ' . $user['last_name'];
Mailer::sendVerificationApproved(
    $user['email'],
    $cdcName,
    'cdc'
);

Response::success(['id' => $newId], 'CDC user verified successfully');
