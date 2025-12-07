<?php

/**
 * GET /students/evaluation/history
 * Get evaluation history for the logged-in student
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_STUDENT);

$conn = Database::getConnection();
$studentId = Auth::getUserId();

// Get all evaluation history for the student
$stmt = $conn->prepare("
    SELECT 
        eh.evaluation_history_id,
        eh.evaluation_rank,
        eh.performance_score,
        eh.feedback,
        eh.evaluation_date,
        c.company_name,
        c.company_logo,
        s.semester,
        s.academic_year
    FROM evaluation_history eh
    JOIN verified_companies c ON eh.company_id = c.company_id
    JOIN verified_students s ON eh.student_id = s.student_id
    WHERE eh.student_id = ?
    ORDER BY eh.evaluation_date DESC
");

$stmt->bind_param('i', $studentId);
$stmt->execute();
$result = $stmt->get_result();

$history = [];
while ($row = $result->fetch_assoc()) {
    $history[] = [
        'evaluation_history_id' => intval($row['evaluation_history_id']),
        'evaluation_rank' => intval($row['evaluation_rank']),
        'performance_score' => $row['performance_score'],
        'feedback' => $row['feedback'],
        'evaluation_date' => $row['evaluation_date'],
        'company_name' => $row['company_name'],
        'company_logo' => $row['company_logo'],
        'semester' => $row['semester'],
        'academic_year' => $row['academic_year']
    ];
}

Response::success([
    'history' => $history,
    'total_evaluations' => count($history)
]);
