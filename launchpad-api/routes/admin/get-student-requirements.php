<?php

/**
 * GET /admin/students/:student_id/requirements
 * CDC views all requirements submitted by a specific student
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireRole(ROLE_CDC);

// Get student_id from path
$studentId = intval($parts[3] ?? 0);

if (!$studentId) {
    Response::error('Student ID is required', 400);
}

$conn = Database::getConnection();

// Verify student exists
$stmt = $conn->prepare("
    SELECT student_id, id_num, first_name, last_name, course, company_name 
    FROM verified_students 
    WHERE student_id = ?
");
$stmt->bind_param('i', $studentId);
$stmt->execute();
$studentInfo = $stmt->get_result()->fetch_assoc();

if (!$studentInfo) {
    Response::error('Student not found', 404);
}

// Optional filter by requirement type
$requirementType = $_GET['type'] ?? null;

if ($requirementType) {
    $validTypes = ['pre_deployment', 'deployment', 'final_requirements'];
    if (!in_array($requirementType, $validTypes)) {
        Response::error('Invalid requirement type', 400);
    }
    
    $stmt = $conn->prepare("
        SELECT 
            requirement_id,
            requirement_type,
            file_name,
            file_path,
            file_size,
            description,
            submitted_at
        FROM student_requirements
        WHERE student_id = ? AND requirement_type = ?
        ORDER BY submitted_at DESC
    ");
    $stmt->bind_param('is', $studentId, $requirementType);
} else {
    $stmt = $conn->prepare("
        SELECT 
            requirement_id,
            requirement_type,
            file_name,
            file_path,
            file_size,
            description,
            submitted_at
        FROM student_requirements
        WHERE student_id = ?
        ORDER BY requirement_type, submitted_at DESC
    ");
    $stmt->bind_param('i', $studentId);
}

$stmt->execute();
$result = $stmt->get_result();

$requirements = [];
while ($row = $result->fetch_assoc()) {
    $requirements[] = [
        'requirement_id' => $row['requirement_id'],
        'requirement_type' => $row['requirement_type'],
        'file_name' => $row['file_name'],
        'file_path' => $row['file_path'],
        'file_size' => $row['file_size'],
        'file_size_mb' => round($row['file_size'] / 1048576, 2),
        'description' => $row['description'],
        'submitted_at' => $row['submitted_at']
    ];
}

// Group by requirement type
$groupedRequirements = [
    'pre_deployment' => [],
    'deployment' => [],
    'final_requirements' => []
];

foreach ($requirements as $req) {
    $groupedRequirements[$req['requirement_type']][] = $req;
}

// Count files per type
$counts = [
    'pre_deployment' => count($groupedRequirements['pre_deployment']),
    'deployment' => count($groupedRequirements['deployment']),
    'final_requirements' => count($groupedRequirements['final_requirements'])
];

Response::success([
    'student_info' => $studentInfo,
    'all_requirements' => $requirements,
    'grouped_by_type' => $groupedRequirements,
    'counts_by_type' => $counts,
    'total_count' => count($requirements)
], 'Requirements retrieved successfully');
