<?php

/**
 * PUT /jobs/:id
 * Partner Company updates their job opportunity
 */

if ($method !== 'PUT') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireAuth();
if ($user['role'] !== ROLE_COMPANY) {
    Response::error('Only partner companies can update job opportunities', 403);
}

$conn = Database::getConnection();
$companyId = intval($user['id']);
$jobId = intval($id);
$data = json_decode(file_get_contents('php://input'), true);

// Verify job belongs to this company
$stmt = $conn->prepare("SELECT company_id FROM job_opportunities WHERE job_id = ?");
$stmt->bind_param('i', $jobId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('Job not found', 404);
}

$job = $result->fetch_assoc();
if (intval($job['company_id']) !== $companyId) {
    Response::error('You can only update your own job postings', 403);
}

// Build update query
$updates = [];
$params = [];
$types = '';

if (isset($data['title'])) {
    $updates[] = 'title = ?';
    $params[] = $data['title'];
    $types .= 's';
}
if (isset($data['description'])) {
    $updates[] = 'description = ?';
    $params[] = $data['description'];
    $types .= 's';
}
if (isset($data['requirements'])) {
    $updates[] = 'requirements = ?';
    $params[] = $data['requirements'];
    $types .= 's';
}
if (isset($data['location'])) {
    $updates[] = 'location = ?';
    $params[] = $data['location'];
    $types .= 's';
}
if (isset($data['job_type'])) {
    $validJobTypes = ['Full-time', 'Part-time', 'Contract', 'Internship'];
    if (!in_array($data['job_type'], $validJobTypes)) {
        Response::error('Invalid job type', 400);
    }
    $updates[] = 'job_type = ?';
    $params[] = $data['job_type'];
    $types .= 's';
}
if (isset($data['salary_range'])) {
    $updates[] = 'salary_range = ?';
    $params[] = $data['salary_range'];
    $types .= 's';
}
if (isset($data['application_url'])) {
    $updates[] = 'application_url = ?';
    $params[] = $data['application_url'];
    $types .= 's';
}
if (isset($data['is_active'])) {
    $updates[] = 'is_active = ?';
    $params[] = $data['is_active'] ? 1 : 0;
    $types .= 'i';
}
if (isset($data['tags'])) {
    $updates[] = 'tags = ?';
    $params[] = $data['tags'];
    $types .= 's';
}

if (empty($updates)) {
    Response::success(['message' => 'No changes to update']);
}

$sql = 'UPDATE job_opportunities SET ' . implode(', ', $updates) . ' WHERE job_id = ?';
$params[] = $jobId;
$types .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();

// Get updated job
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
$updatedJob = $stmt->get_result()->fetch_assoc();

Response::success($updatedJob, 'Job opportunity updated successfully');
