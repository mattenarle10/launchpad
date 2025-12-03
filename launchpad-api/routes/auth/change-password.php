<?php

/**
 * POST /auth/change-password
 * Change password for authenticated user (student, company, or CDC)
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

// Require authentication
$user = Auth::requireAuth();

// Get JSON body or form data
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$currentPassword = $input['current_password'] ?? '';
$newPassword = $input['new_password'] ?? '';
$confirmPassword = $input['confirm_password'] ?? '';

// Validate required fields
if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    Response::error('Missing required fields: current_password, new_password, confirm_password', 400);
}

// Check if new passwords match
if ($newPassword !== $confirmPassword) {
    Response::error('New password and confirmation do not match', 400);
}

// Validate new password complexity
$passwordValidation = Auth::validatePasswordComplexity($newPassword);
if (!$passwordValidation['valid']) {
    Response::error('New password requirements not met: ' . implode(', ', $passwordValidation['errors']), 400);
}

// Check that new password is different from current
if ($currentPassword === $newPassword) {
    Response::error('New password must be different from current password', 400);
}

$conn = Database::getConnection();
$userId = $user['id'];
$role = $user['role'];

// Get current password hash based on role
$passwordHash = null;
$tableName = null;
$idColumn = null;

switch ($role) {
    case ROLE_STUDENT:
        $tableName = 'verified_students';
        $idColumn = 'student_id';
        break;
    case ROLE_COMPANY:
    case ROLE_PC:
        $tableName = 'verified_companies';
        $idColumn = 'company_id';
        break;
    case ROLE_CDC:
    case ROLE_ADMIN:
        $tableName = 'cdc_users';
        $idColumn = 'id';
        break;
    default:
        Response::error('Invalid user role', 400);
}

// Fetch current password hash
$stmt = $conn->prepare("SELECT password FROM {$tableName} WHERE {$idColumn} = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result) {
    Response::error('User not found', 404);
}

// Verify current password
if (!Auth::verifyPassword($currentPassword, $result['password'])) {
    Response::error('Current password is incorrect', 401);
}

// Hash new password
$newHashedPassword = Auth::hashPassword($newPassword);

// Update password
$stmt = $conn->prepare("UPDATE {$tableName} SET password = ? WHERE {$idColumn} = ?");
$stmt->bind_param('si', $newHashedPassword, $userId);

if (!$stmt->execute()) {
    Response::error('Failed to update password', 500);
}

Response::success([
    'message' => 'Password changed successfully'
], 'Password updated successfully');
