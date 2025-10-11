<?php

/**
 * Admin Routes (CDC & PC)
 */

$pathParts = explode('/', $path);
$conn = Database::getConnection();

// All admin routes require CDC or PC role
Auth::requireRole([ROLE_CDC, ROLE_PC]);

// POST /admin/verify/students/:id - Verify student registration
if ($method === 'POST' && $pathParts[1] === 'verify' && $pathParts[2] === 'students') {
    $studentId = intval($pathParts[3] ?? 0);
    
    // Get unverified student
    $stmt = $conn->prepare("SELECT * FROM unverified_students WHERE student_id = ?");
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        Response::error('Unverified student not found', 404);
    }
    
    $student = $result->fetch_assoc();
    
    // Get company_id from company_name
    $stmt = $conn->prepare("SELECT company_id FROM verified_companies WHERE name = ?");
    $stmt->bind_param('s', $student['company_name']);
    $stmt->execute();
    $companyResult = $stmt->get_result();
    $companyId = $companyResult->num_rows > 0 ? $companyResult->fetch_assoc()['company_id'] : null;
    
    // Move to verified_students
    $stmt = $conn->prepare("
        INSERT INTO verified_students 
        (id_num, first_name, last_name, email, contact_num, course, specialization, company_id, password, cor)
        VALUES (?, ?, ?, ?, ?, ?, '', ?, ?, ?)
    ");
    $stmt->bind_param(
        'ssssssiss',
        $student['id_num'],
        $student['first_name'],
        $student['last_name'],
        $student['email'],
        $student['contact_num'],
        $student['course'],
        $companyId,
        $student['password'],
        $student['cor']
    );
    $stmt->execute();
    $newStudentId = $conn->insert_id;
    
    // Create OJT progress entry
    $defaultHours = DEFAULT_OJT_HOURS;
    $stmt = $conn->prepare("INSERT INTO ojt_progress (student_id, done_hours, required_hours) VALUES (?, 0, ?)");
    $stmt->bind_param('ii', $newStudentId, $defaultHours);
    $stmt->execute();
    
    // Delete from unverified
    $stmt = $conn->prepare("DELETE FROM unverified_students WHERE student_id = ?");
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    
    Response::success(['student_id' => $newStudentId], 'Student verified successfully');
}

// DELETE /admin/reject/students/:id - Reject student registration
if ($method === 'DELETE' && $pathParts[1] === 'reject' && $pathParts[2] === 'students') {
    $studentId = intval($pathParts[3] ?? 0);
    
    $stmt = $conn->prepare("DELETE FROM unverified_students WHERE student_id = ?");
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        Response::error('Student not found', 404);
    }
    
    Response::success(null, 'Student registration rejected');
}

// POST /admin/verify/companies/:id - Verify company registration
if ($method === 'POST' && $pathParts[1] === 'verify' && $pathParts[2] === 'companies') {
    $companyId = intval($pathParts[3] ?? 0);
    
    // Get unverified company
    $stmt = $conn->prepare("SELECT * FROM unverified_companies WHERE company_id = ?");
    $stmt->bind_param('i', $companyId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        Response::error('Unverified company not found', 404);
    }
    
    $company = $result->fetch_assoc();
    
    // Move to verified_companies
    $stmt = $conn->prepare("
        INSERT INTO verified_companies 
        (name, username, email, contact_num, address, website, password, id_img, moa)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'sssssssss',
        $company['name'],
        $company['username'],
        $company['email'],
        $company['contact_num'],
        $company['address'],
        $company['website'],
        $company['password'],
        $company['id_img'],
        $company['moa']
    );
    $stmt->execute();
    $newCompanyId = $conn->insert_id;
    
    // Delete from unverified
    $stmt = $conn->prepare("DELETE FROM unverified_companies WHERE company_id = ?");
    $stmt->bind_param('i', $companyId);
    $stmt->execute();
    
    Response::success(['company_id' => $newCompanyId], 'Company verified successfully');
}

// DELETE /admin/reject/companies/:id - Reject company registration
if ($method === 'DELETE' && $pathParts[1] === 'reject' && $pathParts[2] === 'companies') {
    $companyId = intval($pathParts[3] ?? 0);
    
    $stmt = $conn->prepare("DELETE FROM unverified_companies WHERE company_id = ?");
    $stmt->bind_param('i', $companyId);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        Response::error('Company not found', 404);
    }
    
    Response::success(null, 'Company registration rejected');
}

// POST /admin/notifications - Broadcast notification to students
if ($method === 'POST' && $pathParts[1] === 'notifications') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $title = $data['title'] ?? '';
    $description = $data['description'] ?? '';
    $deadline = $data['deadline'] ?? null;
    $studentIds = $data['student_ids'] ?? []; // Empty array = broadcast to all
    
    if (empty($title) || empty($description)) {
        Response::error('Title and description are required', 400);
    }
    
    // Get target students
    if (empty($studentIds)) {
        $result = $conn->query("SELECT student_id FROM verified_students");
        while ($row = $result->fetch_assoc()) {
            $studentIds[] = $row['student_id'];
        }
    }
    
    // Insert notifications
    $stmt = $conn->prepare("INSERT INTO notifications (student_id, title, description, deadline) VALUES (?, ?, ?, ?)");
    foreach ($studentIds as $studentId) {
        $stmt->bind_param('isss', $studentId, $title, $description, $deadline);
        $stmt->execute();
    }
    
    Response::success([
        'sent_to' => count($studentIds)
    ], 'Notification broadcast successfully');
}

// GET /admin/stats - Dashboard statistics
if ($method === 'GET' && $pathParts[1] === 'stats') {
    $stats = [
        'total_students' => $conn->query("SELECT COUNT(*) as count FROM verified_students")->fetch_assoc()['count'],
        'total_companies' => $conn->query("SELECT COUNT(*) as count FROM verified_companies")->fetch_assoc()['count'],
        'total_jobs' => $conn->query("SELECT COUNT(*) as count FROM job_opportunities")->fetch_assoc()['count'],
        'pending_students' => $conn->query("SELECT COUNT(*) as count FROM unverified_students")->fetch_assoc()['count'],
        'pending_companies' => $conn->query("SELECT COUNT(*) as count FROM unverified_companies")->fetch_assoc()['count'],
        'total_reports' => $conn->query("SELECT COUNT(*) as count FROM submission_reports")->fetch_assoc()['count'],
    ];
    
    Response::success($stats);
}

Response::error('Admin endpoint not found', 404);

