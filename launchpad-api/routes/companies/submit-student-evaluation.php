<?php

/**
 * POST /companies/students/:id/evaluations
 * Partner Company submits twice-monthly evaluation for a student
 */

if ($method !== 'POST') {
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

$evaluationScore = isset($data['evaluation_score']) ? intval($data['evaluation_score']) : null;
$evaluationPeriod = isset($data['evaluation_period']) ? $data['evaluation_period'] : null;

// Validate score
if ($evaluationScore === null || $evaluationScore < 0 || $evaluationScore > 100) {
    Response::error('Evaluation score must be between 0 and 100', 400);
}

// Validate period
if ($evaluationPeriod !== null && !in_array($evaluationPeriod, ['first_half', 'second_half'])) {
    Response::error('Invalid evaluation period. Must be first_half or second_half', 400);
}

// Verify student belongs to this company
$stmt = $conn->prepare("SELECT student_id FROM verified_students WHERE student_id = ? AND company_id = ?");
$stmt->bind_param('ii', $studentId, $companyId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('Student not found or not assigned to your company', 404);
}

// Use provided period or auto-detect from current date
$currentMonth = intval(date('n'));
$currentYear = intval(date('Y'));

if ($evaluationPeriod === null) {
    // Auto-detect if not provided (backward compatibility)
    $currentDay = intval(date('j'));
    $evaluationPeriod = ($currentDay <= 15) ? 'first_half' : 'second_half';
}

// Calculate category based on score
$category = '';
if ($evaluationScore >= 81) {
    $category = 'Excellent';
} elseif ($evaluationScore >= 61) {
    $category = 'Good';
} elseif ($evaluationScore >= 41) {
    $category = 'Enough';
} elseif ($evaluationScore >= 21) {
    $category = 'Poor';
} else {
    $category = 'Very Poor';
}

// Check if student_evaluations table exists
$tableExists = $conn->query("SHOW TABLES LIKE 'student_evaluations'")->num_rows > 0;

if ($tableExists) {
    // Check if evaluation already exists for this period
    $stmt = $conn->prepare("
        SELECT evaluation_id 
        FROM student_evaluations 
        WHERE student_id = ? 
        AND company_id = ? 
        AND evaluation_period = ? 
        AND evaluation_month = ? 
        AND evaluation_year = ?
    ");
    $stmt->bind_param('iisii', $studentId, $companyId, $evaluationPeriod, $currentMonth, $currentYear);
    $stmt->execute();
    $existing = $stmt->get_result();

    if ($existing->num_rows > 0) {
        // Update existing evaluation
        $stmt = $conn->prepare("
            UPDATE student_evaluations 
            SET evaluation_score = ?, category = ?, evaluated_at = CURRENT_TIMESTAMP
            WHERE student_id = ? 
            AND company_id = ? 
            AND evaluation_period = ? 
            AND evaluation_month = ? 
            AND evaluation_year = ?
        ");
        $stmt->bind_param('isiisii', $evaluationScore, $category, $studentId, $companyId, $evaluationPeriod, $currentMonth, $currentYear);
        $stmt->execute();
    } else {
        // Insert new evaluation
        $stmt = $conn->prepare("
            INSERT INTO student_evaluations 
            (student_id, company_id, evaluation_score, evaluation_period, evaluation_month, evaluation_year, category)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('iiisiis', $studentId, $companyId, $evaluationScore, $evaluationPeriod, $currentMonth, $currentYear, $category);
        $stmt->execute();
    }
}

// Update average evaluation_rank in verified_students table
if ($tableExists) {
    $stmt = $conn->prepare("
        SELECT AVG(evaluation_score) as avg_score 
        FROM student_evaluations 
        WHERE student_id = ?
    ");
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $avgResult = $stmt->get_result()->fetch_assoc();
    $avgScore = $avgResult['avg_score'] ? round($avgResult['avg_score']) : $evaluationScore;
} else {
    // Fallback if table doesn't exist yet - use current score
    $avgScore = $evaluationScore;
}

// Calculate performance_score category based on average
$performanceScore = '';
if ($avgScore >= 81) {
    $performanceScore = 'Excellent';
} elseif ($avgScore >= 61) {
    $performanceScore = 'Good';
} elseif ($avgScore >= 41) {
    $performanceScore = 'Satisfactory';
} elseif ($avgScore >= 21) {
    $performanceScore = 'Needs Improvement';
} else {
    $performanceScore = 'Poor';
}

// Update both evaluation_rank (percentage) and performance_score (category)
$stmt = $conn->prepare("
    UPDATE verified_students 
    SET evaluation_rank = ?, performance_score = ? 
    WHERE student_id = ?
");
$stmt->bind_param('isi', $avgScore, $performanceScore, $studentId);
$stmt->execute();

// Get evaluation count for current month
$stmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM student_evaluations 
    WHERE student_id = ? 
    AND evaluation_month = ? 
    AND evaluation_year = ?
");
$stmt->bind_param('iii', $studentId, $currentMonth, $currentYear);
$stmt->execute();
$countResult = $stmt->get_result()->fetch_assoc();
$evaluationsThisMonth = intval($countResult['count']);

// Get student and company info for notification
$stmt = $conn->prepare("
    SELECT 
        s.first_name, 
        s.last_name,
        c.company_name
    FROM verified_students s
    JOIN verified_companies c ON s.company_id = c.company_id
    WHERE s.student_id = ?
");
$stmt->bind_param('i', $studentId);
$stmt->execute();
$studentInfo = $stmt->get_result()->fetch_assoc();

// Send notification to the student about their evaluation
try {
    $monthName = date('F', mktime(0, 0, 0, $currentMonth, 1));
    
    $notificationTitle = "New Performance Evaluation";
    $notificationMessage = "Hello {$studentInfo['first_name']},\n\n";
    $notificationMessage .= "You have received a performance evaluation from {$studentInfo['company_name']}!\n\n";
    $notificationMessage .= "📊 Score: {$evaluationScore}/100 ({$category})\n";
    $notificationMessage .= "📅 Evaluated: {$monthName} {$currentYear}\n";
    $notificationMessage .= "📈 Overall Performance: {$avgScore}/100 ({$performanceScore})\n\n";
    $notificationMessage .= "Keep up the great work! Check your app for more details.";
    
    NotificationHelper::createCompanyNotification(
        $conn,
        $companyId,
        $notificationTitle,
        $notificationMessage,
        'specific',
        [$studentId]
    );
} catch (Exception $e) {
    // Log error but don't fail evaluation submission
    error_log("Failed to send evaluation notification: " . $e->getMessage());
}

Response::success([
    'student_id' => $studentId,
    'evaluation_score' => $evaluationScore,
    'category' => $category,
    'evaluation_period' => $evaluationPeriod,
    'evaluation_month' => $currentMonth,
    'evaluation_year' => $currentYear,
    'evaluations_this_month' => $evaluationsThisMonth,
    'average_score' => $avgScore,
    'performance_score' => $performanceScore
], 'Evaluation submitted successfully');
