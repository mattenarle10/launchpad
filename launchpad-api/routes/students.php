<?php

/**
 * Students Routes
 */

$pathParts = explode('/', $path);
$conn = Database::getConnection();

// GET /students - List all students (admin only)
if ($method === 'GET' && count($pathParts) === 1) {
    Auth::requireRole([ROLE_CDC, ROLE_PC]);
    
    $page = intval($_GET['page'] ?? 1);
    $pageSize = min(intval($_GET['pageSize'] ?? DEFAULT_PAGE_SIZE), MAX_PAGE_SIZE);
    $offset = ($page - 1) * $pageSize;
    
    $result = $conn->query("SELECT COUNT(*) as total FROM verified_students");
    $total = $result->fetch_assoc()['total'];
    
    $stmt = $conn->prepare("
        SELECT s.*, c.name as company_name, o.done_hours, o.required_hours
        FROM verified_students s
        LEFT JOIN verified_companies c ON s.company_id = c.company_id
        LEFT JOIN ojt_progress o ON s.student_id = o.student_id
        ORDER BY s.verified_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param('ii', $pageSize, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $students = [];
    while ($row = $result->fetch_assoc()) {
        unset($row['password']);
        $students[] = $row;
    }
    
    Response::paginated($students, $page, $pageSize, $total);
}

// GET /students/:id - Get student details
if ($method === 'GET' && count($pathParts) === 2 && is_numeric($pathParts[1])) {
    $user = Auth::requireAuth();
    $studentId = intval($pathParts[1]);
    
    // Students can only view their own data unless admin
    if ($user['role'] === ROLE_STUDENT && $user['id'] !== $studentId) {
        Response::error('Forbidden', 403);
    }
    
    $stmt = $conn->prepare("
        SELECT s.*, c.name as company_name, c.address as company_address,
               o.done_hours, o.required_hours
        FROM verified_students s
        LEFT JOIN verified_companies c ON s.company_id = c.company_id
        LEFT JOIN ojt_progress o ON s.student_id = o.student_id
        WHERE s.student_id = ?
    ");
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        Response::error('Student not found', 404);
    }
    
    $student = $result->fetch_assoc();
    unset($student['password']);
    
    Response::success($student);
}

// GET /students/:id/notifications
if ($method === 'GET' && count($pathParts) === 3 && $pathParts[2] === 'notifications') {
    $user = Auth::requireAuth();
    $studentId = intval($pathParts[1]);
    
    if ($user['role'] === ROLE_STUDENT && $user['id'] !== $studentId) {
        Response::error('Forbidden', 403);
    }
    
    $stmt = $conn->prepare("
        SELECT * FROM notifications 
        WHERE student_id = ? 
        ORDER BY date_sent DESC
    ");
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    Response::success($notifications);
}

// GET /students/:id/reports
if ($method === 'GET' && count($pathParts) === 3 && $pathParts[2] === 'reports') {
    $user = Auth::requireAuth();
    $studentId = intval($pathParts[1]);
    
    if ($user['role'] === ROLE_STUDENT && $user['id'] !== $studentId) {
        Response::error('Forbidden', 403);
    }
    
    $stmt = $conn->prepare("
        SELECT * FROM submission_reports 
        WHERE student_id = ? 
        ORDER BY date_sent DESC
    ");
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
    
    Response::success($reports);
}

// POST /students/:id/reports - Submit report
if ($method === 'POST' && count($pathParts) === 3 && $pathParts[2] === 'reports') {
    $user = Auth::requireRole(ROLE_STUDENT);
    $studentId = intval($pathParts[1]);
    
    if ($user['id'] !== $studentId) {
        Response::error('Forbidden', 403);
    }
    
    if (!isset($_FILES['report'])) {
        Response::error('Report file is required', 400);
    }
    
    $file = $_FILES['report'];
    
    // Validate file
    if (!in_array($file['type'], ALLOWED_DOCUMENT_TYPES)) {
        Response::error('Invalid file type. Only PDF and Word documents allowed', 400);
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        Response::error('File too large. Maximum size is 10MB', 400);
    }
    
    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "report_" . $studentId . "_" . time() . "." . $ext;
    $uploadPath = UPLOAD_DIR . "reports/" . $filename;
    
    if (!file_exists(UPLOAD_DIR . "reports/")) {
        mkdir(UPLOAD_DIR . "reports/", 0777, true);
    }
    
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        Response::error('Failed to upload file', 500);
    }
    
    // Save to database
    $stmt = $conn->prepare("INSERT INTO submission_reports (student_id, report_file) VALUES (?, ?)");
    $stmt->bind_param('is', $studentId, $filename);
    $stmt->execute();
    
    Response::success([
        'report_id' => $conn->insert_id,
        'filename' => $filename
    ], 'Report submitted successfully', 201);
}

Response::error('Students endpoint not found', 404);

