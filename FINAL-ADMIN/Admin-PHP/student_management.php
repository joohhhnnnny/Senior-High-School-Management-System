<?php
require_once 'debug_middleware.php';
require_once '../Portal-Main/conn.php';

// Clear any previous output and set headers
ob_clean();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

addDebugHeaders();
debugLog('Starting student management request');

try {
    // Test connection
    if (!$conn->ping()) {
        debugLog('Database connection failed');
        throw new Exception("Database connection lost");
    }
    debugLog('Database connection successful');

    // Use prepared statement to select all relevant fields from student table
    $stmt = $conn->prepare("
        SELECT 
            id, 
            studentID, 
            fullname, 
            yearLevel,
            year_strand,
            email,
            address,
            phoneNumber
        FROM student 
        ORDER BY id DESC
    ");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    debugLog('Query executed, found ' . $result->num_rows . ' rows');
    
    $students = [];
    while ($row = $result->fetch_assoc()) {
        // Parse year_strand field
        $yearStrandParts = explode('-', $row['year_strand'] ?? '');
        $yearLevel = $yearStrandParts[0] ?? $row['yearLevel'] ?? 'N/A';
        $strand = $yearStrandParts[1] ?? 'N/A';

        // Build student data array with proper null handling
        $students[] = [
            'id' => (int)$row['id'],
            'studentID' => (int)$row['studentID'],
            'fullname' => htmlspecialchars($row['fullname'] ?? 'N/A'),
            'yearLevel' => htmlspecialchars($yearLevel),
            'strand' => htmlspecialchars($strand),
            'year_strand' => htmlspecialchars($row['year_strand'] ?? 'N/A'),
            'email' => htmlspecialchars($row['email'] ?? 'N/A'),
            'address' => htmlspecialchars($row['address'] ?? 'N/A'),
            'phoneNumber' => htmlspecialchars($row['phoneNumber'] ?? 'N/A')
        ];
    }
    
    debugLog('Sending response with ' . count($students) . ' students');
    echo json_encode([
        'success' => true,
        'data' => $students,
        'count' => count($students)
    ]);

} catch (Exception $e) {
    debugLog('Error occurred: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>