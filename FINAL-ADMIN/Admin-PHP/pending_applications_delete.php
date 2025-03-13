<?php
// Disable errors and set content type
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Clear any existing output
if (ob_get_length()) ob_clean();

require_once '../Portal-Main/conn.php';

try {
    // Log incoming request for debugging
    error_log('Delete request received: ' . file_get_contents('php://input'));

    // Parse and validate input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        throw new Exception('Invalid or missing ID');
    }

    $id = intval($input['id']);

    // Start transaction
    $conn->begin_transaction();

    // First get the professor email from pending applications
    $get_email_sql = "SELECT pa.email, ppa.professorID 
                      FROM professorpendingapply ppa 
                      JOIN professorapply pa ON pa.id = ppa.professorID 
                      WHERE ppa.id = ?";
    $get_email_stmt = $conn->prepare($get_email_sql);
    
    if (!$get_email_stmt) {
        throw new Exception("Prepare get email failed: " . $conn->error);
    }

    $get_email_stmt->bind_param("i", $id);
    $get_email_stmt->execute();
    $email_result = $get_email_stmt->get_result();
    $professor_data = $email_result->fetch_assoc();

    if (!$professor_data) {
        throw new Exception("Application not found");
    }

    // Delete from professorpendingapply first (foreign key relationship)
    $delete_pending_sql = "DELETE FROM professorpendingapply WHERE id = ?";
    $delete_pending_stmt = $conn->prepare($delete_pending_sql);
    
    if (!$delete_pending_stmt) {
        throw new Exception("Prepare delete pending failed: " . $conn->error);
    }

    $delete_pending_stmt->bind_param("i", $id);
    
    if (!$delete_pending_stmt->execute()) {
        throw new Exception("Delete from pending applications failed: " . $delete_pending_stmt->error);
    }

    // Delete from professorapply
    $delete_apply_sql = "DELETE FROM professorapply WHERE id = ?";
    $delete_apply_stmt = $conn->prepare($delete_apply_sql);
    
    if (!$delete_apply_stmt) {
        throw new Exception("Prepare delete apply failed: " . $conn->error);
    }

    $delete_apply_stmt->bind_param("i", $professor_data['professorID']);
    
    if (!$delete_apply_stmt->execute()) {
        throw new Exception("Delete from professor applications failed: " . $delete_apply_stmt->error);
    }

    // Commit transaction
    $conn->commit();

    // Clear output buffer and send success response
    ob_clean();
    
    echo json_encode([
        'success' => true,
        'message' => 'Application deleted successfully',
        'id' => $id
    ]);

} catch (Exception $e) {
    // Rollback transaction
    if (isset($conn)) {
        $conn->rollback();
    }

    // Log error
    error_log("Delete error: " . $e->getMessage());

    // Clear output buffer and send error response
    ob_clean();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);

} finally {
    // Close all statements
    if (isset($get_email_stmt)) $get_email_stmt->close();
    if (isset($delete_pending_stmt)) $delete_pending_stmt->close();
    if (isset($delete_apply_stmt)) $delete_apply_stmt->close();
    if (isset($conn)) $conn->close();
}
?>
