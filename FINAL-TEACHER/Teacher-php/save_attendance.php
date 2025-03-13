<?php
session_start();
header('Content-Type: application/json');
require_once 'conn.php';

try {
    if (!isset($_SESSION['professor_id'])) {
        throw new Exception('Not authenticated');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) throw new Exception('Invalid data received');

    $subject_title = $data['subject_title'];
    $date = $data['date'];
    $attendance_records = $data['attendance'];

    // Start transaction
    $conn->begin_transaction();

    // Prepare the insert/update statement
    $stmt = $conn->prepare("INSERT INTO student_attendance 
        (studentID, subject_title, date, status, remarks, semester, yearLevel) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        status = VALUES(status),
        remarks = VALUES(remarks),
        yearLevel = VALUES(yearLevel)");

    foreach ($attendance_records as $record) {
        $studentID = $record['studentID'];
        $yearLevel = $record['yearLevel'];
        $status = $record['status'];
        $remarks = $record['remarks'] ?? '';
        $semester = 'first';  // hardcoded as per your system

        $stmt->bind_param("issssss", 
            $studentID,
            $subject_title,
            $date,
            $status,
            $remarks,
            $semester,
            $yearLevel
        );

        if (!$stmt->execute()) {
            throw new Exception("Error saving attendance: " . $stmt->error);
        }
    }

    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
