<?php

/**
 * POST /admin/verify/students/:id
 * Verify student registration (CDC only)
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_CDC);

$studentId = intval($id);

$conn = Database::getConnection();

// Get unverified student
$stmt = $conn->prepare("SELECT * FROM unverified_students WHERE student_id = ?");
$stmt->bind_param('i', $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('Unverified student not found', 404);
}

$student = $result->fetch_assoc();

// Move to verified_students
$stmt = $conn->prepare("
    INSERT INTO verified_students 
    (id_num, first_name, last_name, email, contact_num, course, password, id_photo, company_name)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    'sssssssss',
    $student['id_num'],
    $student['first_name'],
    $student['last_name'],
    $student['email'],
    $student['contact_num'],
    $student['course'],
    $student['password'],
    $student['id_photo'],
    $student['company_name']
);
$stmt->execute();
$newStudentId = $conn->insert_id;

// Create OJT progress with required hours based on course
// IT = 500 hours, COMSCI = 300 hours, EMC = 300 hours
$requiredHours = match($student['course']) {
    'IT' => 500,
    'COMSCI' => 300,
    'EMC' => 500,
    default => 500
};

$stmt = $conn->prepare("
    INSERT INTO ojt_progress (student_id, required_hours, status)
    VALUES (?, ?, 'not_started')
");
$stmt->bind_param('ii', $newStudentId, $requiredHours);
$stmt->execute();

// Delete from unverified
$stmt = $conn->prepare("DELETE FROM unverified_students WHERE student_id = ?");
$stmt->bind_param('i', $studentId);
$stmt->execute();

Response::success(['student_id' => $newStudentId], 'Student verified successfully');

