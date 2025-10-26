<?php
/**
 * Check if student_requirements table exists and show structure
 * Access: http://192.168.101.7/LaunchPad/launchpad-api/check-db.php
 */

require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

try {
    $conn = Database::getConnection();
    
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'student_requirements'");
    
    $response = [
        'table_exists' => $result->num_rows > 0,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($result->num_rows > 0) {
        // Get table structure
        $structure = $conn->query("DESCRIBE student_requirements");
        $columns = [];
        while ($row = $structure->fetch_assoc()) {
            $columns[] = $row;
        }
        $response['columns'] = $columns;
        
        // Count records
        $count = $conn->query("SELECT COUNT(*) as total FROM student_requirements");
        $response['record_count'] = $count->fetch_assoc()['total'];
        
        // Get sample data if exists
        $sample = $conn->query("SELECT * FROM student_requirements LIMIT 3");
        $response['sample_data'] = [];
        while ($row = $sample->fetch_assoc()) {
            $response['sample_data'][] = $row;
        }
        
        $response['message'] = '✅ Table exists and is ready!';
    } else {
        $response['message'] = '❌ Table does NOT exist! Run the migration.';
        $response['migration_file'] = 'migrations/add_student_requirements_table.sql';
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
