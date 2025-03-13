<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1); // Enable error display for debugging

require_once 'conn.php'; // Changed to use existing conn.php

try {
    // Add logging for debugging
    error_log("Attempting to fetch teachers from database");
    
    $stmt = $conn->prepare("SELECT id, professorID, fullname, email, phoneNumber FROM professor ORDER BY professorID");
    $stmt->execute();
    
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($teachers === false) {
        throw new Exception('Failed to fetch teachers data');
    }

    echo json_encode([
        'success' => true,
        'data' => $teachers
    ]);

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
