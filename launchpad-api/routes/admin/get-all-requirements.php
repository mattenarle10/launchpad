<?php

/**
 * GET /admin/requirements
 * CDC views all student requirements with filtering options
 * Can filter by requirement_type to see which students have submitted specific types
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireRole(ROLE_CDC);

$conn = Database::getConnection();

// Optional filter by requirement type
$requirementType = $_GET['type'] ?? null;

if ($requirementType) {
    $validTypes = ['pre_deployment', 'deployment', 'final_requirements'];
    if (!in_array($requirementType, $validTypes)) {
        Response::error('Invalid requirement type', 400);
    }
}

// Get all verified students with their requirements count
$query = "
    SELECT 
        vs.student_id,
        vs.id_num,
        vs.first_name,
        vs.last_name,
        vs.course,
        vs.company_name,
        COUNT(CASE WHEN sr.requirement_type = 'pre_deployment' THEN 1 END) as pre_deployment_count,
        COUNT(CASE WHEN sr.requirement_type = 'deployment' THEN 1 END) as deployment_count,
        COUNT(CASE WHEN sr.requirement_type = 'final_requirements' THEN 1 END) as final_requirements_count,
        COUNT(sr.requirement_id) as total_requirements,
        MAX(sr.submitted_at) as last_submission
    FROM verified_students vs
    LEFT JOIN student_requirements sr ON vs.student_id = sr.student_id
";

// Add filter if requirement type is specified
if ($requirementType) {
    $query .= " AND sr.requirement_type = ?";
}

$query .= "
    GROUP BY vs.student_id, vs.id_num, vs.first_name, vs.last_name, vs.course, vs.company_name
    ORDER BY vs.last_name, vs.first_name
";

if ($requirementType) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $requirementType);
} else {
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = [
        'student_id' => $row['student_id'],
        'id_num' => $row['id_num'],
        'first_name' => $row['first_name'],
        'last_name' => $row['last_name'],
        'full_name' => $row['first_name'] . ' ' . $row['last_name'],
        'course' => $row['course'],
        'company_name' => $row['company_name'],
        'requirements_count' => [
            'pre_deployment' => (int)$row['pre_deployment_count'],
            'deployment' => (int)$row['deployment_count'],
            'final_requirements' => (int)$row['final_requirements_count'],
            'total' => (int)$row['total_requirements']
        ],
        'last_submission' => $row['last_submission']
    ];
}

// Get overall statistics
$statsQuery = "
    SELECT 
        COUNT(DISTINCT student_id) as students_with_requirements,
        COUNT(CASE WHEN requirement_type = 'pre_deployment' THEN 1 END) as total_pre_deployment,
        COUNT(CASE WHEN requirement_type = 'deployment' THEN 1 END) as total_deployment,
        COUNT(CASE WHEN requirement_type = 'final_requirements' THEN 1 END) as total_final_requirements,
        COUNT(*) as total_files
    FROM student_requirements
";
$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();

Response::success([
    'students' => $students,
    'total_students' => count($students),
    'statistics' => [
        'students_with_requirements' => (int)$stats['students_with_requirements'],
        'total_files' => (int)$stats['total_files'],
        'by_type' => [
            'pre_deployment' => (int)$stats['total_pre_deployment'],
            'deployment' => (int)$stats['total_deployment'],
            'final_requirements' => (int)$stats['total_final_requirements']
        ]
    ],
    'filter_applied' => $requirementType
], 'Requirements overview retrieved successfully');
