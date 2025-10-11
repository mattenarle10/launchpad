<?php

/**
 * GET /companies
 * List all companies
 */

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

$conn = Database::getConnection();
$page = intval($_GET['page'] ?? 1);
$pageSize = min(intval($_GET['pageSize'] ?? DEFAULT_PAGE_SIZE), MAX_PAGE_SIZE);
$offset = ($page - 1) * $pageSize;

$result = $conn->query("SELECT COUNT(*) as total FROM verified_companies");
$total = $result->fetch_assoc()['total'];

$stmt = $conn->prepare("
    SELECT company_id, company_name, username, email, contact_num, address, website, company_logo, verified_at
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

