<?php
// Add this at the top of your PHP file
error_log('Accessing teacher_management.php');

// Prevent any output before headers
ob_start();

// Set error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    // Include database connection silently
    require_once 'conn.php';

    // Clear any previous output
    ob_clean();
    
    // Prepare and execute query
    $query = "SELECT * FROM professor ORDER BY id DESC";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }

    // Fetch all results into array
    $teachers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $teachers[] = $row;
    }
    
    // Clean output buffer
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Send JSON response
    echo json_encode([
        'success' => true,
        'data' => $teachers,
        'count' => count($teachers)
    ]);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// End execution
exit();
?>