<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/delete_error.log');

require_once '../Portal-Main/conn.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

if (ob_get_level()) ob_end_clean();

try {
    $rawInput = file_get_contents('php://input');
    error_log("Raw input received: " . $rawInput);
    
    $data = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }
    
    if (!isset($data['id']) || !is_numeric($data['id'])) {
        throw new Exception('Invalid or missing student ID');
    }
    
    $studentId = intval($data['id']);
    $conn->begin_transaction();
    
    try {
        // Delete from related tables first
        $relatedTables = [
            'student_sections',
            'student_subjects',
            'attendance_records',
            'grade_records'
        ];

        // Delete from all related tables first
        foreach ($relatedTables as $table) {
            $sql = "DELETE FROM $table WHERE student_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error preparing delete for $table: " . $conn->error);
            }
            $stmt->bind_param("i", $studentId);
            $stmt->execute();
            if ($stmt->error) {
                throw new Exception("Error deleting from $table: " . $stmt->error);
            }
            $stmt->close();
        }

        // Finally delete from student table
        $stmt = $conn->prepare("DELETE FROM student WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Error preparing delete for student: " . $conn->error);
        }
        
        $stmt->bind_param("i", $studentId);
        if (!$stmt->execute()) {
            throw new Exception("Delete failed: " . $stmt->error);
        }
        
        if ($stmt->affected_rows === 0) {
            throw new Exception("No student found with ID: $studentId");
        }
        
        $stmt->close();
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Student deleted successfully',
            'studentId' => $studentId
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Delete error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) $conn->close();
}
