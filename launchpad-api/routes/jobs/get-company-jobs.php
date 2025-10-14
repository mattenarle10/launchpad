<?php

/**
 * GET /jobs/company
 * Get all job opportunities for the authenticated company
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

// Get all jobs for this company
$stmt = $conn->prepare("
    SELECT 
        j.*,
        c.company_name
    FROM job_opportunities j
    JOIN verified_companies c ON j.company_id = c.company_id
    WHERE j.company_id = ?
    ORDER BY j.created_at DESC
");

$stmt->bind_param('i', $companyId);
$stmt->execute();
$result = $stmt->get_result();

$jobs = [];
while ($row = $result->fetch_assoc()) {
    $jobs[] = [
        'job_id' => intval($row['job_id']),
        'company_id' => intval($row['company_id']),
        'company_name' => $row['company_name'],
        'title' => $row['title'],
        'description' => $row['description'],
        'requirements' => $row['requirements'],
        'location' => $row['location'],
        'job_type' => $row['job_type'],
        'salary_range' => $row['salary_range'],
        'is_active' => (bool)$row['is_active'],
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at']
    ];
}

Response::success($jobs);
