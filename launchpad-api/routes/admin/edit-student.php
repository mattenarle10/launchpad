<?php

/**
 * PUT /admin/students/:id
 * Edit student information (CDC admin only)
 */

if ($method !== 'PUT') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole([ROLE_CDC]);

$conn = Database::getConnection();
$studentId = intval($id);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate student exists
$stmt = $conn->prepare("SELECT student_id FROM verified_students WHERE student_id = ?");
$stmt->bind_param('i', $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('Student not found', 404);
}

// Build dynamic update query based on provided fields
$updates = [];
$params = [];
$types = '';

$allowedFields = [
    'first_name' => 's',
    'last_name' => 's',
    'email' => 's',
    'course' => 's',
    'contact_num' => 's',
    'company_id' => 'i'
];

foreach ($allowedFields as $field => $type) {
    if (isset($input[$field])) {
        // Handle company_id - allow NULL for unassignment
        if ($field === 'company_id' && $input[$field] === '') {
            $updates[] = "$field = NULL";
        } else {
            $updates[] = "$field = ?";
            $params[] = $input[$field];
            $types .= $type;
        }
    }
}

if (empty($updates)) {
    Response::error('No valid fields to update', 400);
}

// Validate course if provided
if (isset($input['course']) && !in_array($input['course'], ['IT', 'COMSCI', 'EMC'])) {
    Response::error('Invalid course. Must be IT, COMSCI, or EMC', 400);
}

// Validate email if provided
if (isset($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    Response::error('Invalid email format', 400);
}

// Check if email is already taken by another student
if (isset($input['email'])) {
    $stmt = $conn->prepare("SELECT student_id FROM verified_students WHERE email = ? AND student_id != ?");
    $stmt->bind_param('si', $input['email'], $studentId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        Response::error('Email already exists', 409);
    }
}

// Execute update
$sql = "UPDATE verified_students SET " . implode(', ', $updates) . " WHERE student_id = ?";
$params[] = $studentId;
$types .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if (!$stmt->execute()) {
    Response::error('Failed to update student', 500);
}

// Get updated student data
$stmt = $conn->prepare("
    SELECT student_id, id_num, first_name, last_name, email, course, 
           contact_num, company_name, profile_pic, verified_at
    FROM verified_students 
    WHERE student_id = ?
");
$stmt->bind_param('i', $studentId);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

Response::success($student, 'Student updated successfully');

