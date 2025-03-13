<?php
// Prevent any HTML error output
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set proper JSON headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

require_once 'conn.php';

try {
    $stats = [];
    
    // Get student count - Modified for mysqli
    $studentQuery = $conn->query("SELECT COUNT(*) as count FROM student");
    $studentRow = $studentQuery->fetch_assoc();
    $stats['students'] = $studentRow['count'];
    
    // Get teacher count - Modified for mysqli
    $teacherQuery = $conn->query("SELECT COUNT(*) as count FROM professor");
    $teacherRow = $teacherQuery->fetch_assoc();
    $stats['teachers'] = $teacherRow['count'];
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
exit;
