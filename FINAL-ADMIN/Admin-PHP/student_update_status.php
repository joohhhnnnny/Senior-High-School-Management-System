<?php
require_once '../Portal-Main/conn.php';
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Decode JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id']) || !isset($data['status'])) {
        throw new Exception('Missing required parameters');
    }

    $id = intval($data['id']);
    $status = $data['status'];
    
    // First query - update status
    $stmt = $conn->prepare("UPDATE studentpendingenroll SET status = ? WHERE studentID = ? OR id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("sii", $status, $id, $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("No records were updated");
    }

    // Get updated stats
    $stats = [
        'pending' => 0,
        'approved' => 0,
        'total' => 0
    ];

    $statsQuery = "SELECT status, COUNT(*) as count FROM studentpendingenroll GROUP BY status";
    $statsResult = $conn->query($statsQuery);
    
    if ($statsResult) {
        while ($row = $statsResult->fetch_assoc()) {
            if ($row['status'] === 'pending') {
                $stats['pending'] = $row['count'];
            } elseif ($row['status'] === 'approved') {
                $stats['approved'] = $row['count'];
            }
            $stats['total'] += $row['count'];
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully',
        'stats' => $stats
    ]);

} catch (Exception $e) {
    error_log("Error in student_update_status.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>