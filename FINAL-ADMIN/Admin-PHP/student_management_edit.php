<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/edit_error.log');

header('Content-Type: application/json');
require_once '../Portal-Main/conn.php';

error_log("🔄 Edit PHP Script Started");

try {
    $method = $_SERVER['REQUEST_METHOD'];
    error_log("📍 Request Method: $method");
    
    if ($method === 'GET') {
        if (!isset($_GET['id'])) {
            throw new Exception('Student ID not provided');
        }
        
        $id = intval($_GET['id']);
        error_log("🔍 Fetching student ID: $id");
        
        $stmt = $conn->prepare("SELECT * FROM student WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        
        if (!$student) {
            throw new Exception('Student not found');
        }
        
        error_log("✅ Student data found: " . json_encode($student));
        echo json_encode(['success' => true, 'data' => $student]);
        exit;
    }
    
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        error_log("📥 Received POST data: " . print_r($input, true));
        
        if (!$input || !isset($input['id'])) {
            throw new Exception('Invalid input data');
        }
        
        error_log("🔄 Preparing update query...");
        $stmt = $conn->prepare("
            UPDATE student 
            SET fullname = ?, 
                address = ?, 
                phoneNumber = ?
            WHERE id = ?
        ");
        
        if (!$stmt) {
            error_log("❌ Prepare failed: " . $conn->error);
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        error_log("📝 Binding parameters...");
        $stmt->bind_param("sssi", 
            $input['fullname'],
            $input['address'],
            $input['phoneNumber'],
            $input['id']
        );
        
        error_log("📤 Executing update...");
        if (!$stmt->execute()) {
            error_log("❌ Execute failed: " . $stmt->error);
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        error_log("✅ Update completed. Rows affected: " . $stmt->affected_rows);
        
        echo json_encode([
            'success' => true,
            'message' => 'Student updated successfully',
            'rows_affected' => $stmt->affected_rows
        ]);
    }
    
} catch (Exception $e) {
    error_log("❌ Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
    error_log("🏁 Edit PHP Script Ended");
}
?>