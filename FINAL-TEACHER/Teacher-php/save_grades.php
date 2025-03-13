<?php
session_start();
header('Content-Type: application/json');
require_once '../../FINAL-ADMIN/Portal-Main/conn.php';

try {
    if (!isset($_SESSION['professor_id'])) {
        throw new Exception('Not authenticated');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    error_log("Raw received data: " . file_get_contents('php://input'));
    error_log("Decoded grade data: " . print_r($data, true));
    
    // Validate required fields
    if (!isset($data['studentId']) || !isset($data['subject_title']) || !isset($data['semester'])) {
        throw new Exception('Missing required data');
    }

    // Validate semester enum
    error_log("Semester validation check: " . 
        "Value: '" . $data['semester'] . "', " .
        "Valid values: " . implode(",", ['first', 'second']) . ", " .
        "Result: " . (in_array($data['semester'], ['first', 'second']) ? 'valid' : 'invalid')
    );
    if (!in_array($data['semester'], ['first', 'second'])) {
        throw new Exception('Invalid semester value');
    }

    // Validate yearLevel enum
    if (!in_array($data['yearLevel'], ['11', '12'])) {
        throw new Exception('Invalid year level');
    }

    // Sanitize and prepare data
    $studentId = (int)$data['studentId'];
    $subject_title = trim($data['subject_title']);
    $semester = $data['semester'];
    $yearLevel = $data['yearLevel'];
    $midterm = $data['midterm'] !== null ? number_format(floatval($data['midterm']), 2) : null;
    $finals = $data['finals'] !== null ? number_format(floatval($data['finals']), 2) : null;

    // Determine remarks based on final grade
    $remarks = 'Incomplete'; // default value
    if ($midterm !== null && $finals !== null) {
        $final_grade = ($midterm + $finals) / 2;
        $remarks = $final_grade >= 75 ? 'Passed' : 'Failed';
    }

    // Check if record exists
    $check_sql = "SELECT id FROM student_grades 
                  WHERE studentID = ? 
                  AND subject_title = ? 
                  AND semester = ?";
    
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("iss", $studentId, $subject_title, $semester);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $exists = $result->num_rows > 0;
    $check_stmt->close();

    error_log("SQL parameters: " . print_r([
        'studentId' => $studentId,
        'subject_title' => $subject_title,
        'semester' => $semester,
        'yearLevel' => $yearLevel
    ], true));

    error_log("Final processed parameters: " . print_r([
        'studentId' => $studentId,
        'subject_title' => $subject_title,
        'semester' => $semester,
        'yearLevel' => $yearLevel,
        'exists' => $exists,
        'query_type' => $exists ? 'UPDATE' : 'INSERT'
    ], true));

    if ($exists) {
        $sql = "UPDATE student_grades 
                SET midterm = ?,
                    finals = ?,
                    remarks = ?,
                    yearLevel = ?
                WHERE studentID = ? 
                AND subject_title = ? 
                AND semester = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ddssiss",
            $midterm,
            $finals,
            $remarks,
            $yearLevel,
            $studentId,
            $subject_title,
            $semester
        );
    } else {
        $sql = "INSERT INTO student_grades 
                (studentID, subject_title, semester, yearLevel, midterm, finals, remarks)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssdds",
            $studentId,
            $subject_title,
            $semester,
            $yearLevel,
            $midterm,
            $finals,
            $remarks
        );
    }

    if (!$stmt->execute()) {
        throw new Exception("Failed to " . ($exists ? "update" : "insert") . " grades: " . $stmt->error);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Grades ' . ($exists ? 'updated' : 'saved') . ' successfully',
        'operation' => $exists ? 'update' : 'insert'
    ]);

} catch (Exception $e) {
    error_log("Error in save_grades.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
