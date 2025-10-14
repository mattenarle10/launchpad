<?php

/**
 * DELETE /admin/jobs/:id
 * CDC deletes any job opportunity
 */

if ($method !== 'DELETE') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole(ROLE_CDC);

$conn = Database::getConnection();
$jobId = intval($id);

// Check if job exists
$stmt = $conn->prepare("SELECT job_id FROM job_opportunities WHERE job_id = ?");
$stmt->bind_param('i', $jobId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('Job not found', 404);
}

// Delete the job
$stmt = $conn->prepare("DELETE FROM job_opportunities WHERE job_id = ?");
$stmt->bind_param('i', $jobId);
$stmt->execute();

Response::success(['message' => 'Job opportunity deleted successfully']);
