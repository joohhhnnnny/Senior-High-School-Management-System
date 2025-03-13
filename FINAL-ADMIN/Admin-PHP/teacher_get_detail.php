<?php
header('Content-Type: application/json');
require_once 'conn.php';

try {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if (!$id) throw new Exception('Teacher ID is required');

    $stmt = $conn->prepare("
        SELECT id, professorID, fullname, email, phoneNumber 
        FROM professor 
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$teacher) {
        throw new Exception('Teacher not found');
    }

    echo json_encode([
        'success' => true,
        'data' => $teacher
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
