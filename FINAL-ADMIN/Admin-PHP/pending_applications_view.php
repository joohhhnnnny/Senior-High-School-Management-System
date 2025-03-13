<?php
// Disable error reporting for production
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
    // Get raw POST data
    $rawData = file_get_contents('php://input');
    
    // Parse JSON data
    $data = json_decode($rawData, true);
    
    // Validate ID
    if (!isset($data['id']) || !is_numeric($data['id'])) {
        throw new Exception('Invalid or missing ID');
    }
    
    $id = intval($data['id']);

    // Prepare and execute query
    $stmt = $conn->prepare("SELECT * FROM professorpendingapply WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("No application found with ID: " . $id);
    }
    
    $professor = $result->fetch_assoc();
    
    // Safely get values with null fallback
    $response = [
        'success' => true,
        'data' => [
            'id' => $professor['id'] ?? null,
            'professorID' => $professor['professorID'] ?? null,
            'fullname' => $professor['fullname'] ?? null,
            'email' => $professor['email'] ?? null,
            'phoneNumber' => $professor['phoneNumber'] ?? null,
            'address' => $professor['address'] ?? null,
            'status' => $professor['status'] ?? null,
            'date' => $professor['date'] ?? null
        ]
    ];
    
    // Ensure clean output
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Send JSON response
    echo json_encode($response);

} catch (Exception $e) {
    // Clean any output
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Send error response
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
