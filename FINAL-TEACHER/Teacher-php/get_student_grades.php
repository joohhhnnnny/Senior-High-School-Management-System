<?php
session_start();
header('Content-Type: application/json');
require_once '../../FINAL-ADMIN/Portal-Main/conn.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check for valid teacher session
    if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] !== true) {
        throw new Exception('Not authenticated');
    }

    $subject_id = $_GET['subject_id'] ?? null;
    $section_id = $_GET['section_id'] ?? null;

    if (!$subject_id || !$section_id) {
        throw new Exception('Missing required parameters');
    }

    // Get students and their grades for the section and subject
    $query = "SELECT 
                st.id as student_id,
                st.studentID as student_number,
                st.fullname,
                st.yearLevel,
                sub.subject_title,
                sub.semester as subject_semester,
                COALESCE(sg.midterm, NULL) as midterm,
                COALESCE(sg.finals, NULL) as finals,
                COALESCE(sg.final_grade, NULL) as final_grade,
                COALESCE(sg.remarks, 'Incomplete') as remarks
              FROM student st
              JOIN student_sections ss ON st.id = ss.student_id
              JOIN sections sec ON ss.section_id = sec.id
              JOIN subjects sub ON sub.id = ?
              LEFT JOIN student_grades sg 
                ON st.studentID = sg.studentID 
                AND sub.subject_title = sg.subject_title
              WHERE sec.id = ?
              ORDER BY st.fullname";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ii", $subject_id, $section_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $students = [];
    
    while ($row = $result->fetch_assoc()) {
        $students[] = [
            'student_id' => $row['student_number'],
            'fullname' => $row['fullname'],
            'yearLevel' => $row['yearLevel'],
            'subject' => $row['subject_title'],
            'semester' => $row['subject_semester'],
            'midterm' => $row['midterm'],
            'finals' => $row['finals'],
            'final_grade' => $row['final_grade'],
            'remarks' => $row['remarks']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $students
    ]);

} catch (Exception $e) {
    error_log("Error in get_student_grades.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'session' => $_SESSION,
            'get' => $_GET
        ]
    ]);
}
?>
