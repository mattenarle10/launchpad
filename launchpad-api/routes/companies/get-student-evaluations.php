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
// Allow evaluation for both periods regardless of current date
$currentPeriod = null; // Not restricting by period

// Get all evaluations for this student
$stmt = $conn->prepare("
    SELECT 
        evaluation_id,
        evaluation_score,
        evaluation_period,
        evaluation_month,
        evaluation_year,
        category,
        comments,
        answers_json,
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
    $answers = null;
    if (!empty($row['answers_json'])) {
        $decoded = json_decode($row['answers_json'], true);
        if (is_array($decoded)) {
            $answers = $decoded;
        }
    }

    $evaluations[] = [
        'evaluation_id' => intval($row['evaluation_id']),
        'evaluation_score' => intval($row['evaluation_score']),
        'evaluation_period' => $row['evaluation_period'],
        'evaluation_month' => intval($row['evaluation_month']),
        'evaluation_year' => intval($row['evaluation_year']),
        'category' => $row['category'],
        'comments' => $row['comments'],
        'answers' => $answers,
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

// Get evaluations for each period this month
$stmt = $conn->prepare("
    SELECT evaluation_period, evaluation_score, category
    FROM student_evaluations 
    WHERE student_id = ? 
    AND company_id = ?
    AND evaluation_month = ? 
    AND evaluation_year = ?
");
$stmt->bind_param('iiii', $studentId, $companyId, $currentMonth, $currentYear);
$stmt->execute();
$periodEvals = $stmt->get_result();

$firstHalfEval = null;
$secondHalfEval = null;

while ($row = $periodEvals->fetch_assoc()) {
    if ($row['evaluation_period'] === 'first_half') {
        $firstHalfEval = [
            'score' => intval($row['evaluation_score']),
            'category' => $row['category']
        ];
    } else {
        $secondHalfEval = [
            'score' => intval($row['evaluation_score']),
            'category' => $row['category']
        ];
    }
}

Response::success([
    'evaluations' => $evaluations,
    'current_month' => $currentMonth,
    'current_year' => $currentYear,
    'evaluations_this_month' => $evaluationsThisMonth,
    'first_half_evaluation' => $firstHalfEval,
    'second_half_evaluation' => $secondHalfEval,
    'can_evaluate_first_half' => true,  // Can always evaluate both periods
    'can_evaluate_second_half' => true
]);
