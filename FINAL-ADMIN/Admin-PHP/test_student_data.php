<?php
require_once '../Portal-Main/conn.php';
header('Content-Type: application/json');

try {
    if (!$conn->ping()) {
        throw new Exception("Database connection lost");
    }

    $sql = "SELECT * FROM student LIMIT 1";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $data = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'data' => $data,
        'connection' => 'alive'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
