<?php

/**
 * GET /jobs
 * Get all active job opportunities (for students and CDC)
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

Auth::requireAuth(); // Any authenticated user can view jobs

$conn = Database::getConnection();

// Get all active jobs
$stmt = $conn->query("
    SELECT 
        j.*,
        c.company_name
    FROM job_opportunities j
    JOIN verified_companies c ON j.company_id = c.company_id
    WHERE j.is_active = TRUE
    ORDER BY j.created_at DESC
");

$jobs = [];
while ($row = $stmt->fetch_assoc()) {
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
        'application_url' => $row['application_url'],
        'tags' => $row['tags'],
        'is_active' => (bool)$row['is_active'],
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at']
    ];
}

Response::success($jobs);
