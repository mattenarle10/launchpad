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

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Require company_id to attach student to an existing verified company
$companyId = isset($data['company_id']) ? intval($data['company_id']) : 0;
if ($companyId <= 0) {
    Response::error('company_id is required and must be a positive integer', 400);
}

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

// Validate target company exists (must be a verified company)
$stmt = $conn->prepare("SELECT company_id, company_name FROM verified_companies WHERE company_id = ?");
$stmt->bind_param('i', $companyId);
$stmt->execute();
$companyResult = $stmt->get_result();
if ($companyResult->num_rows === 0) {
    Response::error('Company not found or not verified', 404);
}
$company = $companyResult->fetch_assoc();

// Move to verified_students
$stmt = $conn->prepare("
    INSERT INTO verified_students 
    (id_num, first_name, last_name, email, contact_num, course, password, cor, company_name, company_id)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    'sssssssssi',
    $student['id_num'],
    $student['first_name'],
    $student['last_name'],
    $student['email'],
    $student['contact_num'],
    $student['course'],
    $student['password'],
    $student['cor'],
    $company['company_name'],
    $company['company_id']
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

// Send verification email notification
$studentName = $student['first_name'] . ' ' . $student['last_name'];
Mailer::sendVerificationApproved(
    $student['email'],
    $studentName,
    'student',
    ['company_name' => $company['company_name']]
);

Response::success(['student_id' => $newStudentId], 'Student verified successfully');

