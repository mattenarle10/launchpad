<?php

/**
 * DELETE /admin/students/:id
 * Delete student (CDC admin only)
 */

if ($method !== 'DELETE') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole([ROLE_CDC]);

$conn = Database::getConnection();
$studentId = intval($id);

// Check if student exists
$stmt = $conn->prepare("SELECT student_id, first_name, last_name FROM verified_students WHERE student_id = ?");
$stmt->bind_param('i', $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('Student not found', 404);
}

$student = $result->fetch_assoc();

// Begin transaction
$conn->begin_transaction();

try {
    // Delete related records first (cascade delete)
    
    // Delete daily reports
    $stmt = $conn->prepare("DELETE FROM daily_reports WHERE student_id = ?");
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    
    // Delete OJT progress
    $stmt = $conn->prepare("DELETE FROM ojt_progress WHERE student_id = ?");
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    
    // Delete the student
    $stmt = $conn->prepare("DELETE FROM verified_students WHERE student_id = ?");
    $stmt->bind_param('i', $studentId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete student');
    }
    
    // Commit transaction
    $conn->commit();
    
    Response::success([
        'deleted_student_id' => $studentId,
        'student_name' => $student['first_name'] . ' ' . $student['last_name']
    ], 'Student and all related records deleted successfully');
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    Response::error('Failed to delete student: ' . $e->getMessage(), 500);
}

