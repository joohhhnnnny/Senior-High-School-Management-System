<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'php_errors.log');

// Start output buffer to catch any early output
ob_start();

try {
    require_once '../Portal-Main/conn.php';
    
    if (!$conn) {
        throw new Exception("Database connection failed - no connection object");
    }
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    error_log("Database connected successfully");

    // Get applications
    $sql = "SELECT * FROM professorpendingapply ORDER BY date DESC";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    error_log("Found " . $result->num_rows . " applications");

    $applications = [];
    while ($row = $result->fetch_assoc()) {
        $applications[] = [
            'id' => (int)$row['id'],
            'professorID' => (int)$row['professorID'],
            'email' => $row['email'],
            'status' => $row['status'],
            'date' => $row['date']
        ];
    }

    

    // Get stats
    $stats = [
        'pending' => (int)$conn->query("SELECT COUNT(*) as count FROM professorpendingapply WHERE status='pending'")->fetch_assoc()['count'],
        'for_review' => (int)$conn->query("SELECT COUNT(*) as count FROM professorpendingapply WHERE status='for review'")->fetch_assoc()['count'],
        'processed_today' => (int)$conn->query("SELECT COUNT(*) as count FROM professorpendingapply WHERE DATE(date)=CURDATE()")->fetch_assoc()['count']
    ];

    // Clear any buffered output
    ob_clean();

    $response = [
        'success' => true,
        'data' => $applications,
        'stats' => $stats,
        'debug' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'row_count' => count($applications)
        ]
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
