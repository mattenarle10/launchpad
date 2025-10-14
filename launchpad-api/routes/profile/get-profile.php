<?php

/**
 * GET /profile
 * Get current user's profile
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireAuth();
$conn = Database::getConnection();

$role = $user['role'];
$userId = $user['id'];

if ($role === ROLE_CDC) {
    // Get CDC user profile
    $stmt = $conn->prepare("SELECT id, username, email, created_at FROM cdc_users WHERE id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc();
    
    if (!$profile) {
        Response::error('Profile not found', 404);
    }
    
    $profile['role'] = 'cdc';
    Response::success($profile);
    
} elseif ($role === ROLE_COMPANY) {
    // Get company profile (match verified_companies schema)
    $stmt = $conn->prepare("
        SELECT 
            company_id,
            username,
            company_name,
            address,
            website,
            email,
            contact_num,
            company_logo,
            verified_at
        FROM verified_companies 
        WHERE company_id = ?
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    
    if (!$row) {
        Response::error('Profile not found', 404);
    }
    
    // Normalize keys for frontend
    $profile = [
        'role' => 'company',
        'company_id' => intval($row['company_id']),
        'username' => $row['username'],
        'company_name' => $row['company_name'],
        'company_address' => $row['address'],
        'company_website' => $row['website'],
        'contact_email' => $row['email'],
        'contact_phone' => $row['contact_num'],
        'company_logo' => $row['company_logo'],
        'verified_at' => $row['verified_at'],
    ];
    
    Response::success($profile);
    
} elseif ($role === ROLE_STUDENT) {
    // Get student profile
    $stmt = $conn->prepare("
        SELECT 
            student_id,
            id_num,
            first_name,
            last_name,
            email,
            course,
            contact_num,
            company_name,
            profile_pic,
            cor,
            verified_at
        FROM verified_students 
        WHERE student_id = ?
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    
    if (!$row) {
        Response::error('Profile not found', 404);
    }
    
    $profile = [
        'role' => 'student',
        'student_id' => intval($row['student_id']),
        'id_num' => $row['id_num'],
        'first_name' => $row['first_name'],
        'last_name' => $row['last_name'],
        'email' => $row['email'],
        'course' => $row['course'],
        'contact_num' => $row['contact_num'],
        'company_name' => $row['company_name'],
        'profile_pic' => $row['profile_pic'],
        'cor' => $row['cor'],
        'verified_at' => $row['verified_at'],
    ];
    
    Response::success($profile);
    
} else {
    Response::error('Invalid user role', 400);
}

