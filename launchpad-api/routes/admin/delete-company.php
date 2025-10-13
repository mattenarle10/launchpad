<?php

/**
 * DELETE /admin/companies/:id
 * Delete company (CDC admin only)
 */

if ($method !== 'DELETE') {
    Response::error('Method not allowed', 405);
}

Auth::requireRole([ROLE_CDC]);

$conn = Database::getConnection();
$companyId = intval($id);

// Check if company exists
$stmt = $conn->prepare("SELECT company_id, company_name FROM verified_companies WHERE company_id = ?");
$stmt->bind_param('i', $companyId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    Response::error('Company not found', 404);
}

$company = $result->fetch_assoc();

// Begin transaction
$conn->begin_transaction();

try {
    // Note: Add cascade deletes here if you have related tables
    // For now, just delete the company
    
    // Delete the company
    $stmt = $conn->prepare("DELETE FROM verified_companies WHERE company_id = ?");
    $stmt->bind_param('i', $companyId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete company');
    }
    
    // Commit transaction
    $conn->commit();
    
    Response::success([
        'deleted_company_id' => $companyId,
        'company_name' => $company['company_name']
    ], 'Company deleted successfully');
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    Response::error('Failed to delete company: ' . $e->getMessage(), 500);
}

