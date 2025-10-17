<?php

/**
 * PUT /profile
 * Update current user's profile
 */

if ($method !== 'PUT') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireAuth();
$conn = Database::getConnection();
$data = json_decode(file_get_contents('php://input'), true);

$role = $user['role'];
$userId = $user['id'];

if ($role === ROLE_CDC) {
    // Update CDC user profile (only email)
    $email = $data['email'] ?? null;
    
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        Response::error('Valid email is required', 400);
    }
    
    // Check if email is already taken by another user
    $stmt = $conn->prepare("SELECT id FROM cdc_users WHERE email = ? AND id != ?");
    $stmt->bind_param('si', $email, $userId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        Response::error('Email already in use', 400);
    }
    
    $stmt = $conn->prepare("UPDATE cdc_users SET email = ? WHERE id = ?");
    $stmt->bind_param('si', $email, $userId);
    $stmt->execute();
    
    Response::success(['message' => 'Profile updated successfully']);
    
} elseif ($role === ROLE_COMPANY) {
    // Update company profile (verified_companies schema)
    // Map incoming fields to DB columns
    $updates = [];
    $params = [];
    $types = '';

    if (isset($data['company_name'])) {
        $updates[] = 'company_name = ?';
        $params[] = $data['company_name'];
        $types .= 's';
    }
    if (isset($data['company_address'])) {
        $updates[] = 'address = ?';
        $params[] = $data['company_address'];
        $types .= 's';
    }
    if (isset($data['company_website'])) {
        $updates[] = 'website = ?';
        $params[] = $data['company_website'];
        $types .= 's';
    }
    if (isset($data['contact_email'])) {
        $email = $data['contact_email'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Valid contact email is required', 400);
        }
        // Check if email already used by another company
        $stmt = $conn->prepare("SELECT company_id FROM verified_companies WHERE email = ? AND company_id != ?");
        $stmt->bind_param('si', $email, $userId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            Response::error('Email already in use', 400);
        }
        $updates[] = 'email = ?';
        $params[] = $email;
        $types .= 's';
    }
    if (isset($data['contact_phone'])) {
        $updates[] = 'contact_num = ?';
        $params[] = $data['contact_phone'];
        $types .= 's';
    }

    if (empty($updates)) {
        Response::success(['message' => 'No changes to update']);
    }

    $sql = 'UPDATE verified_companies SET ' . implode(', ', $updates) . ' WHERE company_id = ?';
    $params[] = $userId;
    $types .= 'i';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    Response::success(['message' => 'Profile updated successfully']);
    
} elseif ($role === ROLE_STUDENT) {
    // Update student profile
    $updates = [];
    $params = [];
    $types = '';

    if (isset($data['first_name'])) {
        $updates[] = 'first_name = ?';
        $params[] = $data['first_name'];
        $types .= 's';
    }
    if (isset($data['last_name'])) {
        $updates[] = 'last_name = ?';
        $params[] = $data['last_name'];
        $types .= 's';
    }
    if (isset($data['email'])) {
        $email = $data['email'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Valid email is required', 400);
        }
        // Check if email already used by another student
        $stmt = $conn->prepare("SELECT student_id FROM verified_students WHERE email = ? AND student_id != ?");
        $stmt->bind_param('si', $email, $userId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            Response::error('Email already in use', 400);
        }
        $updates[] = 'email = ?';
        $params[] = $email;
        $types .= 's';
    }
    if (isset($data['contact_num'])) {
        $updates[] = 'contact_num = ?';
        $params[] = $data['contact_num'];
        $types .= 's';
    }
    if (isset($data['specialization'])) {
        $updates[] = 'specialization = ?';
        $params[] = $data['specialization'];
        $types .= 's';
    }

    if (empty($updates)) {
        Response::success(['message' => 'No changes to update']);
    }

    $sql = 'UPDATE verified_students SET ' . implode(', ', $updates) . ' WHERE student_id = ?';
    $params[] = $userId;
    $types .= 'i';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    Response::success(['message' => 'Profile updated successfully']);
    
} else {
    Response::error('Invalid user role', 400);
}

