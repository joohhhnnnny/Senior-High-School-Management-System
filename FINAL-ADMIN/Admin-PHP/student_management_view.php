<?php
require_once '../Portal-Main/conn.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Student ID not provided');
    }

    $id = intval($_GET['id']);
    
    // Get student details with error logging
    $stmt = $conn->prepare("
        SELECT 
            s.id, 
            s.studentID, 
            s.fullname, 
            s.year_strand,
            s.email, 
            s.address, 
            s.phoneNumber,
            SUBSTRING_INDEX(s.year_strand, '-', 1) as yearLevel,
            SUBSTRING_INDEX(s.year_strand, '-', -1) as strand
        FROM student s 
        WHERE s.id = ?
    ");
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        throw new Exception('Database error');
    }

    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        throw new Exception('Failed to fetch student data');
    }

    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    if (!$student) {
        throw new Exception('Student not found');
    }

    // Get grades with error checking
    $gradesStmt = $conn->prepare("
        SELECT 
            subject_title,
            semester,
            yearLevel,
            midterm,
            finals,
            final_grade,
            remarks
        FROM student_grades
        WHERE studentID = ?
        ORDER BY semester ASC, subject_title ASC
    ");

    if (!$gradesStmt) {
        error_log("Grades prepare failed: " . $conn->error);
        throw new Exception('Database error');
    }

    $gradesStmt->bind_param("i", $student['studentID']);
    if (!$gradesStmt->execute()) {
        error_log("Grades execute failed: " . $gradesStmt->error);
        throw new Exception('Failed to fetch grades');
    }

    $gradesResult = $gradesStmt->get_result();
    $grades = $gradesResult->fetch_all(MYSQLI_ASSOC);

    // Get attendance with error checking
    $attendanceStmt = $conn->prepare("
        SELECT 
            subject_title,
            date,
            status,
            semester,
            yearLevel,
            remarks
        FROM student_attendance
        WHERE studentID = ?
        ORDER BY date DESC
    ");

    if (!$attendanceStmt) {
        error_log("Attendance prepare failed: " . $conn->error);
        throw new Exception('Database error');
    }

    $attendanceStmt->bind_param("i", $student['studentID']);
    if (!$attendanceStmt->execute()) {
        error_log("Attendance execute failed: " . $attendanceStmt->error);
        throw new Exception('Failed to fetch attendance');
    }

    $attendanceResult = $attendanceStmt->get_result();
    $attendance = $attendanceResult->fetch_all(MYSQLI_ASSOC);

    // Return the data with success status
    echo json_encode([
        'success' => true,
        'data' => [
            'student' => $student,
            'grades' => $grades,
            'attendance' => $attendance
        ]
    ]);

} catch (Exception $e) {
    error_log("View student error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($gradesStmt)) $gradesStmt->close();
    if (isset($attendanceStmt)) $attendanceStmt->close();
    if (isset($conn)) $conn->close();
}
