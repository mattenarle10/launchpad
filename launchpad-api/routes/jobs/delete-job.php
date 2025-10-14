<?php

/**
 * DELETE /jobs/:id
 * Partner Company deletes their job opportunity
 */

if ($method !== 'DELETE') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireAuth();
if ($user['role'] !== ROLE_COMPANY) {
    Response::error('Only partner companies can delete job opportunities', 403);
}

$conn = Database::getConnection();
$companyId = intval($user['id']);
$jobId = intval($id);

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
    Response::error('You can only delete your own job postings', 403);
}

// Delete the job
$stmt = $conn->prepare("DELETE FROM job_opportunities WHERE job_id = ?");
$stmt->bind_param('i', $jobId);
$stmt->execute();

Response::success(['message' => 'Job opportunity deleted successfully']);
