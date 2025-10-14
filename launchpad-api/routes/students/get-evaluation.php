<?php

/**
 * GET /students/evaluation
 * Student gets their evaluation score from partner company
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireAuth();
if ($user['role'] !== ROLE_STUDENT) {
    Response::error('Only students can access this endpoint', 403);
}

$conn = Database::getConnection();
$studentId = intval($user['id']);

// Get student's evaluation rank and company info
$stmt = $conn->prepare("
    SELECT 
        s.evaluation_rank,
        s.company_name,
        c.company_name as verified_company_name
    FROM verified_students s
    LEFT JOIN verified_companies c ON s.company_id = c.company_id
    WHERE s.student_id = ?
");

$stmt->bind_param('i', $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('Student not found', 404);
}

$data = $result->fetch_assoc();

$evaluation = [
    'evaluation_rank' => $data['evaluation_rank'] !== null ? intval($data['evaluation_rank']) : null,
    'company_name' => $data['verified_company_name'] ?? $data['company_name'],
    'is_evaluated' => $data['evaluation_rank'] !== null,
    'status' => $data['evaluation_rank'] !== null ? 'evaluated' : 'pending'
];

Response::success($evaluation);
