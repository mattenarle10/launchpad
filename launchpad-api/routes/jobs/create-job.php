<?php

/**
 * POST /jobs
 * Partner Company creates a job opportunity
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireAuth();
if ($user['role'] !== ROLE_COMPANY) {
    Response::error('Only partner companies can post job opportunities', 403);
}

$conn = Database::getConnection();
$companyId = intval($user['id']);
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$title = $data['title'] ?? null;
$description = $data['description'] ?? null;

if (!$title || !$description) {
    Response::error('Title and description are required', 400);
}

$requirements = $data['requirements'] ?? null;
$location = $data['location'] ?? null;
$jobType = $data['job_type'] ?? 'Full-time';
$salaryRange = $data['salary_range'] ?? null;

// Validate job type
$validJobTypes = ['Full-time', 'Part-time', 'Contract', 'Internship'];
if (!in_array($jobType, $validJobTypes)) {
    Response::error('Invalid job type', 400);
}

// Insert job opportunity
$stmt = $conn->prepare("
    INSERT INTO job_opportunities (company_id, title, description, requirements, location, job_type, salary_range)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param('issssss', $companyId, $title, $description, $requirements, $location, $jobType, $salaryRange);
$stmt->execute();

$jobId = $conn->insert_id;

// Get created job
$stmt = $conn->prepare("
    SELECT 
        j.*,
        c.company_name
    FROM job_opportunities j
    JOIN verified_companies c ON j.company_id = c.company_id
    WHERE j.job_id = ?
");
$stmt->bind_param('i', $jobId);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

Response::success($job, 'Job opportunity created successfully');
