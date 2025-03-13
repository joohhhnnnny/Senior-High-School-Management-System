<?php
// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/delete_error.log');

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Buffer output
ob_start();

require_once 'conn.php';

try {
    // Validate input
    $input = file_get_contents('php://input');
    if (!$input) {
        throw new Exception('No input data received');
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }
    
    if (!isset($data['id']) || !is_numeric($data['id'])) {
        throw new Exception('Valid Professor ID is required');
    }
    
    $id = intval($data['id']);

    // Start transaction
    mysqli_autocommit($conn, FALSE);
    
    // Check if professor exists first
    $checkStmt = mysqli_prepare($conn, "SELECT id FROM professor WHERE id = ?");
    if (!$checkStmt) {
        throw new Exception('Failed to prepare check statement: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($checkStmt, "i", $id);
    if (!mysqli_stmt_execute($checkStmt)) {
        throw new Exception('Failed to execute check: ' . mysqli_error($conn));
    }

    mysqli_stmt_store_result($checkStmt);
    if (mysqli_stmt_num_rows($checkStmt) === 0) {
        throw new Exception('Professor not found');
    }

    // Delete associated schedules
    $scheduleStmt = mysqli_prepare($conn, "DELETE FROM schedules WHERE professor_id = ?");
    if (!$scheduleStmt) {
        throw new Exception('Failed to prepare schedule deletion: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($scheduleStmt, "i", $id);
    if (!mysqli_stmt_execute($scheduleStmt)) {
        throw new Exception('Failed to delete schedules: ' . mysqli_error($conn));
    }

    // Delete the professor
    $profStmt = mysqli_prepare($conn, "DELETE FROM professor WHERE id = ?");
    if (!$profStmt) {
        throw new Exception('Failed to prepare professor deletion: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($profStmt, "i", $id);
    if (!mysqli_stmt_execute($profStmt)) {
        throw new Exception('Failed to delete professor: ' . mysqli_error($conn));
    }

    // Commit the transaction
    if (!mysqli_commit($conn)) {
        throw new Exception('Failed to commit transaction: ' . mysqli_error($conn));
    }

    // Clear buffer and send success response
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Professor and associated schedules deleted successfully',
        'id' => $id
    ]);

} catch (Exception $e) {
    // Rollback transaction
    mysqli_rollback($conn);
    
    // Log error
    error_log("Delete Error: " . $e->getMessage());
    
    // Clear buffer and send error response
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_type' => 'deletion_error'
    ]);
} finally {
    // Clean up
    if (isset($checkStmt)) mysqli_stmt_close($checkStmt);
    if (isset($scheduleStmt)) mysqli_stmt_close($scheduleStmt);
    if (isset($profStmt)) mysqli_stmt_close($profStmt);
    
    // Reset autocommit and close connection
    mysqli_autocommit($conn, TRUE);
    mysqli_close($conn);
}

exit();