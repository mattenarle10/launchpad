<?php

/**
 * POST /auth/login
 * User authentication
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';
$userType = $data['user_type'] ?? $data['userType'] ?? 'student'; // Support both formats

if (empty($username) || empty($password)) {
    Response::error('Username and password are required', 400);
}

$conn = Database::getConnection();

$table = match($userType) {
    'student' => 'verified_students',
    'company' => 'verified_companies',
    'cdc' => 'cdc_users',
    'pc' => 'program_coordinators',
    default => Response::error('Invalid user type', 400)
};

$identifierField = $userType === 'student' ? 'id_num' : 'username';

$stmt = $conn->prepare("SELECT * FROM $table WHERE $identifierField = ? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // For students, check if they exist in unverified_students table
    if ($userType === 'student') {
        $unverifiedStmt = $conn->prepare("SELECT * FROM unverified_students WHERE id_num = ? LIMIT 1");
        $unverifiedStmt->bind_param('s', $username);
        $unverifiedStmt->execute();
        $unverifiedResult = $unverifiedStmt->get_result();
        
        if ($unverifiedResult->num_rows > 0) {
            Response::error('Your account is pending verification. Please wait for admin approval.', 401);
        }
    }
    
    // For companies, check if they exist in unverified_companies table
    if ($userType === 'company') {
        $unverifiedStmt = $conn->prepare("SELECT * FROM unverified_companies WHERE username = ? LIMIT 1");
        $unverifiedStmt->bind_param('s', $username);
        $unverifiedStmt->execute();
        $unverifiedResult = $unverifiedStmt->get_result();
        
        if ($unverifiedResult->num_rows > 0) {
            Response::error('Your company account is pending verification. Please wait for CDC approval.', 401);
        }
    }
    
    Response::error('Invalid credentials', 401);
}

$user = $result->fetch_assoc();

if (!Auth::verifyPassword($password, $user['password'])) {
    Response::error('Invalid credentials', 401);
}

$payload = [
    'id' => $user[$userType === 'student' ? 'student_id' : ($userType === 'company' ? 'company_id' : 'id')],
    'username' => $username,
    'role' => $userType
];

$token = Auth::generateToken($payload);

unset($user['password']);

Response::success([
    'token' => $token,
    'user' => $user,
    'expiresIn' => JWT_EXPIRATION
], 'Login successful');

