<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
require_once '../Portal-Main/conn.php';

$stmt = null;

try {
    // Log connection status
    if (!$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Validate and sanitize inputs with detailed logging
    $strand = isset($_GET['strand']) ? mysqli_real_escape_string($conn, $_GET['strand']) : null;
    $year = isset($_GET['year']) ? mysqli_real_escape_string($conn, $_GET['year']) : null;
    $semester = isset($_GET['semester']) ? mysqli_real_escape_string($conn, $_GET['semester']) : null;

    // Detailed parameter logging
    error_log("Processing request with parameters:");
    error_log("Strand: " . ($strand ?? 'null'));
    error_log("Year: " . ($year ?? 'null'));
    error_log("Semester: " . ($semester ?? 'null'));

    if (!$strand || !$year || !$semester) {
        throw new Exception('Missing required parameters: ' . 
            (!$strand ? 'strand ' : '') .
            (!$year ? 'year ' : '') .
            (!$semester ? 'semester ' : ''));
    }

    // Debug log the query before preparation
    $query = "SELECT id, subject_title, subject_type 
             FROM subjects 
             WHERE strand = ? 
             AND year_level = ? 
             AND semester = ?
             ORDER BY subject_title";
    
    error_log("Executing query: " . $query);

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("sss", $strand, $year, $semester);
    
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Failed to get result set: " . $stmt->error);
    }

    $subjects = [];
    while ($row = $result->fetch_assoc()) {
        $subjects[] = [
            'id' => $row['id'],
            'subject_title' => $row['subject_title']
            // Removed subject_type since we don't need it for display
        ];
    }

    // Close statement before sending response
    if ($stmt) {
        $stmt->close();
        $stmt = null;
    }

    // Log result count
    error_log("Found " . count($subjects) . " subjects");

    echo json_encode([
        'success' => true,
        'data' => $subjects,
        'count' => count($subjects)
    ]);

} catch (Exception $e) {
    error_log('Error in get_subjects.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => __FILE__,
            'line' => __LINE__,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
} finally {
    // Only close if not already closed
    if ($stmt) {
        $stmt->close();
    }
    if ($conn) {
        $conn->close();
    }
}
exit();
?>