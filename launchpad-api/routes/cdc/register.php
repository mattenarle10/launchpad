<?php

/**
 * POST /cdc/register
 * CDC user registration (no auth required)
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

$data = json_decode(file_get_contents('php://input'), true) ?? [];

$firstName = trim($data['first_name'] ?? '');
$lastName = trim($data['last_name'] ?? '');
$email = trim($data['email'] ?? '');
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

// Validate required fields
if (empty($firstName) || empty($lastName) || empty($email) || empty($username) || empty($password)) {
    Response::error('Missing required fields: first_name, last_name, email, username, password', 400);
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Response::error('Invalid email format', 400);
}

// Validate password complexity
$passwordValidation = Auth::validatePasswordComplexity($password);
if (!$passwordValidation['valid']) {
    Response::error('Password requirements not met: ' . implode(', ', $passwordValidation['errors']), 400);
}

$conn = Database::getConnection();

// Check if email already exists in cdc_users or unverified_cdc_users
$stmt = $conn->prepare("\n    SELECT email FROM cdc_users WHERE email = ?\n    UNION\n    SELECT email FROM unverified_cdc_users WHERE email = ?\n");
$stmt->bind_param('ss', $email, $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    Response::error('Email already registered', 400);
}

// Check if username already exists in cdc_users or unverified_cdc_users
$stmt = $conn->prepare("\n    SELECT username FROM cdc_users WHERE username = ?\n    UNION\n    SELECT username FROM unverified_cdc_users WHERE username = ?\n");
$stmt->bind_param('ss', $username, $username);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    Response::error('Username already taken', 400);
}

// Hash password
$hashedPassword = Auth::hashPassword($password);

// Insert into unverified_cdc_users (pending approval by existing CDC admin)
$stmt = $conn->prepare("\n    INSERT INTO unverified_cdc_users \n    (username, email, first_name, last_name, password)\n    VALUES (?, ?, ?, ?, ?)\n");
$stmt->bind_param(
    'sssss',
    $username,
    $email,
    $firstName,
    $lastName,
    $hashedPassword
);
$stmt->execute();

Response::success([
    'id' => $conn->insert_id,
    'status' => 'pending',
    'message' => 'CDC registration submitted! Waiting for CDC admin approval.'
], 'Registration successful', 201);
