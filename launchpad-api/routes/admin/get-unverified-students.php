<?php

/**
 * GET /admin/unverified/students
 * List all unverified students (CDC only)
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_CDC);

$conn = Database::getConnection();

$result = $conn->query("
    SELECT student_id, id_num, first_name, last_name, email, course, contact_num, 
           cor, company_name, created_at
    FROM unverified_students
    ORDER BY created_at DESC
");

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

Response::success($students);

