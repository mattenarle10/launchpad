<?php

/**
 * Authentication Routes
 */

$pathParts = explode('/', $path);

// POST /auth/login
if ($method === 'POST' && $pathParts[1] === 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $userType = $data['userType'] ?? 'student'; // student | company | cdc | pc

    if (empty($username) || empty($password)) {
        Response::error('Username and password are required', 400);
    }

    $conn = Database::getConnection();
    
    // Determine which table to query based on user type
    $table = match($userType) {
        'student' => 'verified_students',
        'company' => 'verified_companies',
        'cdc' => 'cdc_users',
        'pc' => 'program_coordinators',
        default => Response::error('Invalid user type', 400)
    };

    // For students, use id_num; for companies, use username; for admins, use username
    $identifierField = $userType === 'student' ? 'id_num' : 'username';
    
    $stmt = $conn->prepare("SELECT * FROM $table WHERE $identifierField = ? LIMIT 1");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        Response::error('Invalid credentials', 401);
    }

    $user = $result->fetch_assoc();
    
    if (!Auth::verifyPassword($password, $user['password'])) {
        Response::error('Invalid credentials', 401);
    }

    // Generate JWT token
    $payload = [
        'id' => $user[$userType === 'student' ? 'student_id' : ($userType === 'company' ? 'company_id' : 'id')],
        'username' => $username,
        'role' => $userType
    ];
    
    $token = Auth::generateToken($payload);

    // Remove sensitive data
    unset($user['password']);

    Response::success([
        'token' => $token,
        'user' => $user,
        'expiresIn' => JWT_EXPIRATION
    ], 'Login successful');
}

// POST /auth/logout
if ($method === 'POST' && $pathParts[1] === 'logout') {
    Auth::requireAuth();
    Response::success(null, 'Logout successful');
}

// POST /auth/refresh
if ($method === 'POST' && $pathParts[1] === 'refresh') {
    $payload = Auth::requireAuth();
    $newToken = Auth::generateToken($payload);
    
    Response::success([
        'token' => $newToken,
        'expiresIn' => JWT_EXPIRATION
    ], 'Token refreshed');
}

Response::error('Auth endpoint not found', 404);

