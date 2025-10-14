<?php

/**
 * PUT /companies/students/:id/evaluation
 * Partner Company updates student evaluation rank (0-100)
 */

if ($method !== 'PUT') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireAuth();
if ($user['role'] !== ROLE_COMPANY) {
    Response::error('Only partner companies can evaluate students', 403);
}

$conn = Database::getConnection();
$companyId = intval($user['id']);
$studentId = intval($id);
$data = json_decode(file_get_contents('php://input'), true);

$evaluationRank = isset($data['evaluation_rank']) ? intval($data['evaluation_rank']) : null;

// Validate rank
if ($evaluationRank === null || $evaluationRank < 0 || $evaluationRank > 100) {
    Response::error('Evaluation rank must be between 0 and 100', 400);
}

// Verify student belongs to this company
$stmt = $conn->prepare("SELECT student_id FROM verified_students WHERE student_id = ? AND company_id = ?");
$stmt->bind_param('ii', $studentId, $companyId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('Student not found or not assigned to your company', 404);
}

// Update evaluation rank in verified_students table
$stmt = $conn->prepare("UPDATE verified_students SET evaluation_rank = ? WHERE student_id = ?");
$stmt->bind_param('ii', $evaluationRank, $studentId);
$stmt->execute();

// Get updated student info
$stmt = $conn->prepare("
    SELECT 
        s.student_id,
        s.id_num,
        s.first_name,
        s.last_name,
        s.evaluation_rank
    FROM verified_students s
    WHERE s.student_id = ?
");
$stmt->bind_param('i', $studentId);
$stmt->execute();
$updated = $stmt->get_result()->fetch_assoc();

Response::success($updated, 'Student evaluation updated successfully');
