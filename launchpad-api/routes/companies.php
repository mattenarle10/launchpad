<?php

/**
 * Companies Routes
 */

$pathParts = explode('/', $path);
$conn = Database::getConnection();

// GET /companies - List all companies
if ($method === 'GET' && count($pathParts) === 1) {
    $page = intval($_GET['page'] ?? 1);
    $pageSize = min(intval($_GET['pageSize'] ?? DEFAULT_PAGE_SIZE), MAX_PAGE_SIZE);
    $offset = ($page - 1) * $pageSize;
    
    $result = $conn->query("SELECT COUNT(*) as total FROM verified_companies");
    $total = $result->fetch_assoc()['total'];
    
    $stmt = $conn->prepare("
        SELECT company_id, name, email, contact_num, address, website, profile_pic
        FROM verified_companies
        ORDER BY verified_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param('ii', $pageSize, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $companies = [];
    while ($row = $result->fetch_assoc()) {
        $companies[] = $row;
    }
    
    Response::paginated($companies, $page, $pageSize, $total);
}

// GET /companies/:id - Get company details
if ($method === 'GET' && count($pathParts) === 2 && is_numeric($pathParts[1])) {
    $companyId = intval($pathParts[1]);
    
    $stmt = $conn->prepare("
        SELECT company_id, name, email, contact_num, address, website, profile_pic, verified_at
        FROM verified_companies
        WHERE company_id = ?
    ");
    $stmt->bind_param('i', $companyId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        Response::error('Company not found', 404);
    }
    
    $company = $result->fetch_assoc();
    Response::success($company);
}

// GET /companies/:id/students - List students assigned to company
if ($method === 'GET' && count($pathParts) === 3 && $pathParts[2] === 'students') {
    $user = Auth::requireRole([ROLE_COMPANY, ROLE_CDC, ROLE_PC]);
    $companyId = intval($pathParts[1]);
    
    // Companies can only view their own students
    if ($user['role'] === ROLE_COMPANY && $user['id'] !== $companyId) {
        Response::error('Forbidden', 403);
    }
    
    $stmt = $conn->prepare("
        SELECT s.*, o.done_hours, o.required_hours
        FROM verified_students s
        LEFT JOIN ojt_progress o ON s.student_id = o.student_id
        WHERE s.company_id = ?
        ORDER BY s.last_name, s.first_name
    ");
    $stmt->bind_param('i', $companyId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $students = [];
    while ($row = $result->fetch_assoc()) {
        unset($row['password']);
        $students[] = $row;
    }
    
    Response::success($students);
}

// POST /companies/:id/evaluate - Submit student evaluation
if ($method === 'POST' && count($pathParts) === 3 && $pathParts[2] === 'evaluate') {
    $user = Auth::requireRole(ROLE_COMPANY);
    $companyId = intval($pathParts[1]);
    
    if ($user['id'] !== $companyId) {
        Response::error('Forbidden', 403);
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $studentId = intval($data['student_id'] ?? 0);
    $evaluationRank = intval($data['evaluation_rank'] ?? 0);
    
    if ($studentId === 0 || $evaluationRank < 0 || $evaluationRank > 100) {
        Response::error('Invalid evaluation data. Rank must be between 0-100', 400);
    }
    
    // Verify student belongs to this company
    $stmt = $conn->prepare("SELECT student_id FROM verified_students WHERE student_id = ? AND company_id = ?");
    $stmt->bind_param('ii', $studentId, $companyId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        Response::error('Student not assigned to this company', 403);
    }
    
    // Insert evaluation
    $stmt = $conn->prepare("
        INSERT INTO student_evaluations (company_id, student_id, evaluation_rank)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param('iii', $companyId, $studentId, $evaluationRank);
    $stmt->execute();
    
    Response::success([
        'eval_id' => $conn->insert_id,
        'performance_score' => $evaluationRank >= 51 ? 'Good' : 'Bad'
    ], 'Evaluation submitted successfully', 201);
}

Response::error('Companies endpoint not found', 404);

