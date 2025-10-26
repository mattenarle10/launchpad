<?php

/**
 * GET /students/:id/requirements
 * Get all requirements submitted by a student (optionally filter by type)
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireRole(ROLE_STUDENT);
$studentId = intval($id);

if ($user['id'] !== $studentId) {
    Response::error('Forbidden', 403);
}

$conn = Database::getConnection();

// Optional filter by requirement type
$requirementType = $_GET['type'] ?? null;

if ($requirementType) {
    // Validate requirement type if provided
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

// Group by requirement type for easier access
$groupedRequirements = [
    'pre_deployment' => [],
    'deployment' => [],
    'final_requirements' => []
];

foreach ($requirements as $req) {
    $groupedRequirements[$req['requirement_type']][] = $req;
}

Response::success([
    'all_requirements' => $requirements,
    'grouped_by_type' => $groupedRequirements,
    'total_count' => count($requirements)
], 'Requirements retrieved successfully');
