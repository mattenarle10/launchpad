<?php

/**
 * GET /students/:id
 * Get student details
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireAuth();
$studentId = intval($id);

if ($user['role'] === ROLE_STUDENT && $user['id'] !== $studentId) {
    Response::error('Forbidden', 403);
}

$conn = Database::getConnection();
$stmt = $conn->prepare("
    SELECT s.*, c.name as company_name, c.address as company_address,
           o.done_hours, o.required_hours
    FROM verified_students s
    LEFT JOIN verified_companies c ON s.company_id = c.company_id
    LEFT JOIN ojt_progress o ON s.student_id = o.student_id
    WHERE s.student_id = ?
");
$stmt->bind_param('i', $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('Student not found', 404);
}

$student = $result->fetch_assoc();
unset($student['password']);

Response::success($student);

