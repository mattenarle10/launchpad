<?php

/**
 * DELETE /admin/reject/students/:id
 * Reject student registration (CDC only)
 */

if ($method !== 'DELETE') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_CDC);

$studentId = intval($id);

$conn = Database::getConnection();

$stmt = $conn->prepare("DELETE FROM unverified_students WHERE student_id = ?");
$stmt->bind_param('i', $studentId);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    Response::error('Student not found', 404);
}

Response::success(null, 'Student registration rejected');

