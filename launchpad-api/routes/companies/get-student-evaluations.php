<?php

/**
 * GET /companies/students/:id/evaluations
 * Get evaluation history for a student
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireAuth();
if ($user['role'] !== ROLE_COMPANY) {
    Response::error('Only partner companies can view evaluations', 403);
}

$conn = Database::getConnection();
$companyId = intval($user['id']);
$studentId = intval($id);

// Verify student belongs to this company
$stmt = $conn->prepare("SELECT student_id FROM verified_students WHERE student_id = ? AND company_id = ?");
$stmt->bind_param('ii', $studentId, $companyId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('Student not found or not assigned to your company', 404);
}

// Get current month/year info
$currentMonth = intval(date('n'));
$currentYear = intval(date('Y'));
$currentDay = intval(date('j'));
$currentPeriod = ($currentDay <= 15) ? 'first_half' : 'second_half';

// Get all evaluations for this student
$stmt = $conn->prepare("
    SELECT 
        evaluation_id,
        evaluation_score,
        evaluation_period,
        evaluation_month,
        evaluation_year,
        category,
        evaluated_at
    FROM student_evaluations
    WHERE student_id = ? AND company_id = ?
    ORDER BY evaluation_year DESC, evaluation_month DESC, evaluation_period DESC
");
$stmt->bind_param('ii', $studentId, $companyId);
$stmt->execute();
$result = $stmt->get_result();

$evaluations = [];
while ($row = $result->fetch_assoc()) {
    $evaluations[] = [
        'evaluation_id' => intval($row['evaluation_id']),
        'evaluation_score' => intval($row['evaluation_score']),
        'evaluation_period' => $row['evaluation_period'],
        'evaluation_month' => intval($row['evaluation_month']),
        'evaluation_year' => intval($row['evaluation_year']),
        'category' => $row['category'],
        'evaluated_at' => $row['evaluated_at']
    ];
}

// Count evaluations for current month
$stmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM student_evaluations 
    WHERE student_id = ? 
    AND company_id = ?
    AND evaluation_month = ? 
    AND evaluation_year = ?
");
$stmt->bind_param('iiii', $studentId, $companyId, $currentMonth, $currentYear);
$stmt->execute();
$countResult = $stmt->get_result()->fetch_assoc();
$evaluationsThisMonth = intval($countResult['count']);

// Check if current period evaluation exists
$stmt = $conn->prepare("
    SELECT evaluation_score, category
    FROM student_evaluations 
    WHERE student_id = ? 
    AND company_id = ?
    AND evaluation_period = ? 
    AND evaluation_month = ? 
    AND evaluation_year = ?
");
$stmt->bind_param('iisii', $studentId, $companyId, $currentPeriod, $currentMonth, $currentYear);
$stmt->execute();
$currentEvalResult = $stmt->get_result();
$currentEvaluation = $currentEvalResult->num_rows > 0 ? $currentEvalResult->fetch_assoc() : null;

Response::success([
    'evaluations' => $evaluations,
    'current_month' => $currentMonth,
    'current_year' => $currentYear,
    'current_period' => $currentPeriod,
    'evaluations_this_month' => $evaluationsThisMonth,
    'current_evaluation' => $currentEvaluation ? [
        'score' => intval($currentEvaluation['evaluation_score']),
        'category' => $currentEvaluation['category']
    ] : null,
    'can_evaluate' => true // Can always evaluate/update current period
]);
