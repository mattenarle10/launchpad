<?php

/**
 * GET /students/evaluations
 * Get evaluation history for the logged-in student
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_STUDENT);

$conn = Database::getConnection();
$studentId = Auth::getUserId();

// Get all evaluations for this student from student_evaluations table
$stmt = $conn->prepare("
    SELECT 
        se.evaluation_id,
        se.evaluation_score,
        se.evaluation_period,
        se.evaluation_month,
        se.evaluation_year,
        se.category,
        se.evaluated_at,
        c.company_name,
        c.company_logo
    FROM student_evaluations se
    JOIN verified_companies c ON se.company_id = c.company_id
    WHERE se.student_id = ?
    ORDER BY se.evaluation_year DESC, se.evaluation_month DESC, 
             FIELD(se.evaluation_period, 'second_half', 'first_half')
");
$stmt->bind_param('i', $studentId);
$stmt->execute();
$result = $stmt->get_result();

$evaluations = [];
while ($row = $result->fetch_assoc()) {
    // Format period name
    $periodName = $row['evaluation_period'] === 'first_half' ? '1st-15th' : '16th-End';
    $monthName = date('F', mktime(0, 0, 0, $row['evaluation_month'], 1));
    
    $evaluations[] = [
        'evaluation_id' => intval($row['evaluation_id']),
        'evaluation_rank' => intval($row['evaluation_score']),
        'performance_score' => $row['category'],
        'evaluation_period' => $row['evaluation_period'],
        'period_name' => $periodName,
        'evaluation_month' => intval($row['evaluation_month']),
        'month_name' => $monthName,
        'evaluation_year' => intval($row['evaluation_year']),
        'evaluation_date' => $row['evaluated_at'],
        'company_name' => $row['company_name'],
        'company_logo' => $row['company_logo'],
        'feedback' => null // Can add feedback field later if needed
    ];
}

Response::success([
    'history' => $evaluations,
    'total_evaluations' => count($evaluations)
]);
