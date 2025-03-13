<?php
header('Content-Type: application/json');
require_once 'conn.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = isset($data['id']) ? $data['id'] : null;
    
    if (!$id) throw new Exception('Teacher ID is required');

    $stmt = $conn->prepare("DELETE FROM professor WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Teacher not found or already deleted');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Teacher deleted successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
