<?php
session_start();
header('Content-Type: application/json');
require_once 'conn.php';

try {
    if (!isset($_SESSION['professor_id'])) {
        throw new Exception('Not authenticated');
    }

    $professor_id = intval($_SESSION['professor_id']);
    $subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

    // Get subjects taught by professor with section info
    $subjects_query = "SELECT DISTINCT 
                        sub.id, 
                        sub.subject_title,
                        sec.section_name,
                        sec.id as section_id
                      FROM schedules sch
                      INNER JOIN subjects sub ON sch.subject_id = sub.id
                      INNER JOIN sections sec ON sch.section_id = sec.id
                      WHERE sch.professor_id = ?
                      AND sch.semester = 'first'
                      AND sch.school_year = '2024-2025'";
    
    $stmt = $conn->prepare($subjects_query);
    $stmt->bind_param("i", $professor_id);
    $stmt->execute();
    $subjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get students enrolled in the selected subject's section
    if ($subject_id > 0) {
        $students_query = "SELECT 
                            s.id as student_id,
                            s.studentID,
                            s.fullname,
                            s.yearLevel,
                            s.year_strand,
                            COALESCE(sa.status, 'Not Recorded') as status,
                            sa.remarks,
                            sa.date,
                            sub.subject_title
                          FROM student s
                          INNER JOIN student_sections ss ON s.id = ss.student_id
                          INNER JOIN sections sec ON ss.section_id = sec.id
                          INNER JOIN schedules sch ON sec.id = sch.section_id
                          INNER JOIN subjects sub ON sch.subject_id = sub.id
                          LEFT JOIN student_attendance sa ON s.studentID = sa.studentID 
                            AND sa.subject_title = sub.subject_title
                            AND sa.date = ?
                          WHERE sch.professor_id = ?
                            AND sch.subject_id = ?
                          ORDER BY s.fullname";

        $stmt = $conn->prepare($students_query);
        $stmt->bind_param("sii", $date, $professor_id, $subject_id);
        $stmt->execute();
        $attendance = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $attendance = [];
    }

    echo json_encode([
        'subjects' => $subjects,
        'attendance' => $attendance,
        'date' => $date,
        'debug' => [
            'professor_id' => $professor_id,
            'subject_id' => $subject_id
        ]
    ]);

} catch (Exception $e) {
    error_log("Attendance Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
