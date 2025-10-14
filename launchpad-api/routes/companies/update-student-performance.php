<?php

/**
 * PUT /companies/students/:id/performance
 * Partner Company updates student performance score (qualitative assessment)
 */

if ($method !== 'PUT') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireAuth();
if ($user['role'] !== ROLE_COMPANY) {
    Response::error('Only partner companies can assess student performance', 403);
}

$conn = Database::getConnection();
$companyId = intval($user['id']);
$studentId = intval($id);
$data = json_decode(file_get_contents('php://input'), true);

$performanceScore = $data['performance_score'] ?? null;

// Valid performance scores
$validScores = ['Excellent', 'Good', 'Satisfactory', 'Needs Improvement', 'Poor'];

// Validate performance score
if ($performanceScore === null || !in_array($performanceScore, $validScores)) {
    Response::error('Performance score must be one of: ' . implode(', ', $validScores), 400);
}

// Verify student belongs to this company
$stmt = $conn->prepare("SELECT student_id FROM verified_students WHERE student_id = ? AND company_id = ?");
$stmt->bind_param('ii', $studentId, $companyId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('Student not found or not assigned to your company', 404);
}

// Update performance score in verified_students table
$stmt = $conn->prepare("UPDATE verified_students SET performance_score = ? WHERE student_id = ?");
$stmt->bind_param('si', $performanceScore, $studentId);
$stmt->execute();

// Get updated student info
$stmt = $conn->prepare("
    SELECT 
        s.student_id,
        s.id_num,
        s.first_name,
        s.last_name,
        s.performance_score
    FROM verified_students s
    WHERE s.student_id = ?
");
$stmt->bind_param('i', $studentId);
$stmt->execute();
$updated = $stmt->get_result()->fetch_assoc();

Response::success($updated, 'Student performance score updated successfully');
