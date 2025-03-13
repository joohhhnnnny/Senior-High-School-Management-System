<?php
require_once '../Portal-Main/conn.php';
header('Content-Type: application/json');

// Prevent any output before JSON response
ob_clean();

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = isset($data['id']) ? intval($data['id']) : 0;

    if (!$id) {
        throw new Exception('No ID provided');
    }

    $conn->begin_transaction();

    // Update application status
    $sql = "UPDATE professorpendingapply SET status = 'rejected' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update status: " . $stmt->error);
    }

    $conn->commit();
    
    // Send only ONE JSON response
    echo json_encode([
        'success' => true,
        'message' => 'Professor application rejected'
    ]);

} catch (Exception $e) {
    if (isset($conn) && !$conn->connect_error) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;
?>
