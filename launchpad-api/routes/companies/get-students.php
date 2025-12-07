<?php

/**
 * GET /companies/students
 * Partner Company gets their assigned students
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireAuth();
if ($user['role'] !== ROLE_COMPANY) {
    Response::error('Only partner companies can access this endpoint', 403);
}

$conn = Database::getConnection();
$companyId = intval($user['id']);

// Get all students assigned to this company with their OJT progress
$stmt = $conn->prepare("
    SELECT 
        p.progress_id,
        s.student_id,
        s.id_num,
        s.first_name,
        s.last_name,
        s.email,
        s.course,
        s.semester,
        s.academic_year,
        s.contact_num,
        s.specialization,
        s.verified_at,
        s.evaluation_rank,
        s.performance_score,
        p.status as ojt_status,
        p.completed_hours,
        p.required_hours,
        CASE 
            WHEN p.required_hours > 0 THEN ROUND((p.completed_hours / p.required_hours) * 100, 2)
            ELSE 0
        END as completion_percentage
    FROM verified_students s
    LEFT JOIN ojt_progress p ON s.student_id = p.student_id
    WHERE s.company_id = ?
    ORDER BY s.last_name, s.first_name
");

$stmt->bind_param('i', $companyId);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = [
        'progress_id' => isset($row['progress_id']) ? intval($row['progress_id']) : null,
        'student_id' => intval($row['student_id']),
        'id_num' => $row['id_num'],
        'first_name' => $row['first_name'],
        'last_name' => $row['last_name'],
        'email' => $row['email'],
        'course' => $row['course'],
        'semester' => $row['semester'],
        'academic_year' => $row['academic_year'],
        'contact_num' => $row['contact_num'],
        'specialization' => $row['specialization'],
        'verified_at' => $row['verified_at'],
        'evaluation_rank' => $row['evaluation_rank'] !== null ? intval($row['evaluation_rank']) : null,
        'performance_score' => $row['performance_score'],
        'ojt_status' => $row['ojt_status'] ?? 'not_started',
        'completed_hours' => floatval($row['completed_hours'] ?? 0),
        'required_hours' => floatval($row['required_hours'] ?? 0),
        'completion_percentage' => floatval($row['completion_percentage'] ?? 0)
    ];
}

Response::success($students);
