<?php
header('Content-Type: application/json');
require_once '../Portal-Main/conn.php';

// Initialize variables
$stmt = null;
$sectionStmt = null;

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Debug logging
    error_log("Received data: " . json_encode($data));
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON data received");
    }
    
    if (!isset($data['professor_id']) || !is_numeric($data['professor_id'])) {
        throw new Exception("Invalid professor ID format");
    }

    $professor_id = (int)$data['professor_id'];

    // Modified professor conflict query with correct time overlap logic
    $professorConflictQuery = "
        SELECT s.*, sub.subject_title 
        FROM schedules s
        JOIN subjects sub ON s.subject_id = sub.id
        WHERE s.professor_id = ? 
        AND s.day_of_week = ?
        AND (
            (TIME(?) < s.end_time AND TIME(?) > s.start_time)
        )";

    $stmt = $conn->prepare($professorConflictQuery);
    $stmt->bind_param("isss", 
        $professor_id,
        $data['day'],
        $data['end_time'],
        $data['start_time']
    );
    
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $conflict = $result->fetch_assoc();
        throw new Exception("Schedule conflict: Professor already has a class (" . 
            $conflict['subject_title'] . ") from " . 
            $conflict['start_time'] . " to " . 
            $conflict['end_time'] . " on " . 
            $conflict['day_of_week']);
    }

    // Get section ID
    $sectionQuery = "SELECT id FROM sections WHERE strand = ? AND yearLevel = ? AND school_year = ? LIMIT 1";
    $sectionStmt = $conn->prepare($sectionQuery);
    
    if (!$sectionStmt) {
        throw new Exception("Failed to prepare section query: " . $conn->error);
    }

    $sectionStmt->bind_param("sss", $data['strand'], $data['yearLevel'], $data['school_year']);
    $sectionStmt->execute();
    $sectionResult = $sectionStmt->get_result();
    
    if ($sectionResult->num_rows === 0) {
        throw new Exception("Section not found");
    }
    
    $section = $sectionResult->fetch_assoc();
    $section_id = $section['id'];
    
    // Modified section conflict query with correct time overlap logic
    $sectionConflictQuery = "
        SELECT s.*, sub.subject_title
        FROM schedules s
        JOIN subjects sub ON s.subject_id = sub.id
        WHERE s.section_id = ? 
        AND s.day_of_week = ?
        AND (
            (TIME(?) < s.end_time AND TIME(?) > s.start_time)
        )";
    
    $stmt = $conn->prepare($sectionConflictQuery);
    $stmt->bind_param("isss", 
        $section_id,
        $data['day'],
        $data['end_time'],
        $data['start_time']
    );
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $conflict = $result->fetch_assoc();
        throw new Exception("Schedule conflict: Section already has a class (" . 
            $conflict['subject_title'] . ") from " . 
            $conflict['start_time'] . " to " . 
            $conflict['end_time'] . " on " . 
            $conflict['day_of_week']);
    }
    
    // If no conflicts, insert the new schedule
    $insertQuery = "INSERT INTO schedules (
        section_id, 
        subject_id, 
        professor_id, 
        day_of_week, 
        start_time, 
        end_time, 
        room, 
        semester,
        school_year
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($insertQuery);
    if (!$stmt) {
        throw new Exception("Failed to prepare insert query: " . $conn->error);
    }

    $stmt->bind_param("iiissssss", 
        $section_id,
        $data['subject_id'],
        $professor_id,
        $data['day'],
        $data['start_time'],
        $data['end_time'],
        $data['room'],
        $data['semester'],
        $data['school_year']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert schedule: " . $stmt->error);
    }

    // Clean any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Schedule assigned successfully'
    ]);

} catch (Exception $e) {
    // Clean any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    error_log("Error in assign_schedule.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Clean up resources
    if ($stmt) {
        $stmt->close();
    }
    if ($sectionStmt) {
        $sectionStmt->close();
    }
    if ($conn) {
        $conn->close();
    }
}

exit();
?>