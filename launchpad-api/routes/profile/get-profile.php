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
    // Get company profile
    $stmt = $conn->prepare("
        SELECT 
            company_id,
            username,
            company_name,
            company_address,
            company_website,
            contact_person,
            contact_email,
            contact_phone,
            industry,
            company_size,
            description,
            verified_at,
            created_at
        FROM verified_companies 
        WHERE company_id = ?
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc();
    
    if (!$profile) {
        Response::error('Profile not found', 404);
    }
    
    $profile['role'] = 'company';
    Response::success($profile);
    
} else {
    Response::error('Invalid user role', 400);
}

