<?php
require_once '../Portal-Main/conn.php';
header('Content-Type: application/json');

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', '../error.log');

try {
    // Parse input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['id'])) {
        throw new Exception('No ID provided');
    }
    
    $studentID = $data['id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    // First check if record exists
    $checkStmt = $conn->prepare("SELECT studentID FROM studentpendingenroll WHERE studentID = ?");
    if (!$checkStmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $checkStmt->bind_param("s", $studentID);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Record not found");
    }
    
    $checkStmt->close();
    
    // Delete from parent_guardian FIRST
    $deleteParentStmt = $conn->prepare("DELETE FROM parent_guardian WHERE studentID = ?");
    if (!$deleteParentStmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $deleteParentStmt->bind_param("s", $studentID);
    
    if (!$deleteParentStmt->execute()) {
        throw new Exception("Failed to delete parent/guardian record: " . $deleteParentStmt->error);
    }
    
    $deleteParentStmt->close();

    // Delete from studentpendingenroll SECOND
    $deleteEnrollStmt = $conn->prepare("DELETE FROM studentpendingenroll WHERE studentID = ?");
    if (!$deleteEnrollStmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $deleteEnrollStmt->bind_param("s", $studentID);
    
    if (!$deleteEnrollStmt->execute()) {
        throw new Exception("Failed to delete enrollment record: " . $deleteEnrollStmt->error);
    }
    
    $deleteEnrollStmt->close();
    
    // Delete from studentapply LAST
    $deleteApplyStmt = $conn->prepare("DELETE FROM studentapply WHERE id = ?");
    if (!$deleteApplyStmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $deleteApplyStmt->bind_param("s", $studentID);
    
    if (!$deleteApplyStmt->execute()) {
        throw new Exception("Failed to delete application record: " . $deleteApplyStmt->error);
    }
    
    $deleteApplyStmt->close();
    
    // Commit transaction
    $conn->commit();
    
    // Get updated stats
    $statsQuery = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved
        FROM studentpendingenroll";
        
    $statsResult = $conn->query($statsQuery);
    if (!$statsResult) {
        throw new Exception("Failed to get updated stats");
    }
    
    $stats = $statsResult->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'message' => 'Application, enrollment, and parent/guardian records deleted successfully',
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    error_log("Delete application error: " . $e->getMessage());
    
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