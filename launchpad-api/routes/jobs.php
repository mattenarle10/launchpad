<?php

/**
 * Job Opportunities Routes
 */

$pathParts = explode('/', $path);
$conn = Database::getConnection();

// GET /jobs - List all job opportunities
if ($method === 'GET' && count($pathParts) === 1) {
    $page = intval($_GET['page'] ?? 1);
    $pageSize = min(intval($_GET['pageSize'] ?? DEFAULT_PAGE_SIZE), MAX_PAGE_SIZE);
    $offset = ($page - 1) * $pageSize;
    
    $result = $conn->query("SELECT COUNT(*) as total FROM job_opportunities");
    $total = $result->fetch_assoc()['total'];
    
    $stmt = $conn->prepare("
        SELECT j.*, c.name as company_name, c.address as company_address
        FROM job_opportunities j
        JOIN verified_companies c ON j.company_id = c.company_id
        ORDER BY j.date_sent DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param('ii', $pageSize, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $jobs = [];
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
    
    Response::paginated($jobs, $page, $pageSize, $total);
}

// GET /jobs/:id - Get job details
if ($method === 'GET' && count($pathParts) === 2 && is_numeric($pathParts[1])) {
    $jobId = intval($pathParts[1]);
    
    $stmt = $conn->prepare("
        SELECT j.*, c.name as company_name, c.address as company_address, c.website as company_website
        FROM job_opportunities j
        JOIN verified_companies c ON j.company_id = c.company_id
        WHERE j.job_id = ?
    ");
    $stmt->bind_param('i', $jobId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        Response::error('Job not found', 404);
    }
    
    $job = $result->fetch_assoc();
    Response::success($job);
}

// POST /jobs - Create job posting (company only)
if ($method === 'POST' && count($pathParts) === 1) {
    $user = Auth::requireRole(ROLE_COMPANY);
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $required = ['job_title', 'job_location', 'job_setup', 'job_tags', 'job_pay_min', 'job_pay_max', 'job_requirements', 'job_responsibilities'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            Response::error("Field '$field' is required", 400);
        }
    }
    
    $stmt = $conn->prepare("
        INSERT INTO job_opportunities 
        (company_id, job_title, job_location, job_setup, job_tags, job_pay_min, job_pay_max, job_requirements, job_responsibilities)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'issssiiis',
        $user['id'],
        $data['job_title'],
        $data['job_location'],
        $data['job_setup'],
        $data['job_tags'],
        $data['job_pay_min'],
        $data['job_pay_max'],
        $data['job_requirements'],
        $data['job_responsibilities']
    );
    $stmt->execute();
    
    Response::success([
        'job_id' => $conn->insert_id
    ], 'Job posted successfully', 201);
}

// DELETE /jobs/:id - Delete job posting
if ($method === 'DELETE' && count($pathParts) === 2 && is_numeric($pathParts[1])) {
    $user = Auth::requireRole(ROLE_COMPANY);
    $jobId = intval($pathParts[1]);
    
    // Verify job belongs to this company
    $stmt = $conn->prepare("SELECT job_id FROM job_opportunities WHERE job_id = ? AND company_id = ?");
    $stmt->bind_param('ii', $jobId, $user['id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        Response::error('Job not found or unauthorized', 403);
    }
    
    $stmt = $conn->prepare("DELETE FROM job_opportunities WHERE job_id = ?");
    $stmt->bind_param('i', $jobId);
    $stmt->execute();
    
    Response::success(null, 'Job deleted successfully');
}

Response::error('Jobs endpoint not found', 404);

