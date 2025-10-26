<?php

/**
 * DELETE /students/:id/requirements/:requirement_id
 * Student deletes their own requirement submission
 */

if ($method !== 'DELETE') {
    Response::error('Method not allowed', 405);
}

$user = Auth::requireRole(ROLE_STUDENT);
$studentId = intval($id);

if ($user['id'] !== $studentId) {
    Response::error('Forbidden', 403);
}

// Get requirement_id from path
$requirementId = intval($parts[5] ?? 0);

if (!$requirementId) {
    Response::error('Requirement ID is required', 400);
}

$conn = Database::getConnection();

// Get requirement details and verify ownership
$stmt = $conn->prepare("
    SELECT file_path, requirement_type 
    FROM student_requirements 
    WHERE requirement_id = ? AND student_id = ?
");
$stmt->bind_param('ii', $requirementId, $studentId);
$stmt->execute();
$result = $stmt->get_result();
$requirement = $result->fetch_assoc();

if (!$requirement) {
    Response::error('Requirement not found or you do not have permission to delete it', 404);
}

// Delete the physical file
$filePath = UPLOAD_DIR . "requirements/" . $requirement['requirement_type'] . "/" . $requirement['file_path'];
if (file_exists($filePath)) {
    unlink($filePath);
}

// Delete from database
$stmt = $conn->prepare("DELETE FROM student_requirements WHERE requirement_id = ?");
$stmt->bind_param('i', $requirementId);
$stmt->execute();

Response::success([
    'requirement_id' => $requirementId,
    'message' => 'Requirement deleted successfully'
], 'Requirement deleted successfully');
