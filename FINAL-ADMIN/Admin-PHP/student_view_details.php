<?php
header('Content-Type: application/json');
require_once '../Portal-Main/conn.php';

try {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$id) throw new Exception('Invalid or missing ID parameter');

    // Get the server's root URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $baseImageUrl = $protocol . $host . '/FINAL-Program-images/student/';

    // Modified query to handle both direct ID and pending enrollment ID
    $stmt = $conn->prepare("
        SELECT 
            s.id,
            s.fullname,
            s.birthdate,
            s.gender,
            s.address,
            s.phoneNumber,
            s.email,
            s.yearLevel,
            s.strand,
            s.birthcert,
            s.form138
        FROM studentapply s
        LEFT JOIN studentpendingenroll e ON s.id = e.studentID
        WHERE s.id = ? OR e.id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        error_log("MySQL Error: " . $conn->error);
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }

    $stmt->bind_param("ii", $id, $id);
    
    if (!$stmt->execute()) {
        error_log("Execute Error: " . $stmt->error);
        throw new Exception('Failed to execute query: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    
    if ($result === false) {
        error_log("Result Error: " . $stmt->error);
        throw new Exception('Failed to get result: ' . $stmt->error);
    }

    if ($result->num_rows === 0) {
        throw new Exception("No student found with ID: $id");
    }

    $student = $result->fetch_assoc();
    
    // Format birthdate
    if (isset($student['birthdate'])) {
        $student['birthdate'] = date('F j, Y', strtotime($student['birthdate']));
    }

    // Debug image paths
    error_log("Base Image URL: " . $baseImageUrl);
    error_log("Birth Cert Path: " . ($student['birthcert'] ?? 'null'));
    error_log("Form 138 Path: " . ($student['form138'] ?? 'null'));

    // Convert image paths to full URLs
    $student['birthcert'] = $student['birthcert'] ? $baseImageUrl . $student['birthcert'] : null;
    $student['form138'] = $student['form138'] ? $baseImageUrl . $student['form138'] : null;

    echo json_encode([
        'success' => true,
        'data' => $student
    ]);

} catch (Exception $e) {
    error_log("Error in student_view_details.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'id' => $id ?? null,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>