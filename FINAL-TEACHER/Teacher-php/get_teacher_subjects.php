<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../../FINAL-ADMIN/Portal-Main/conn.php';

try {
    if (!isset($_SESSION['professor_id'])) {
        throw new Exception('Not authenticated');
    }

    $professor_id = $_SESSION['professor_id'];

    // Updated query to join with schedules table
    $query = "SELECT DISTINCT
        s.id,
        s.subject_title,
        s.subject_type,
        s.semester,
        s.year_level,
        s.strand,
        sec.id as section_id,
        sec.section_name,
        sec.strand as section_strand,
        sec.yearLevel,
        sec.school_year,
        sch.day_of_week as schedule_day,
        TIME_FORMAT(sch.start_time, '%H:%i') as schedule_start,
        TIME_FORMAT(sch.end_time, '%H:%i') as schedule_end,
        sch.room
    FROM subjects s
    JOIN schedules sch ON s.id = sch.subject_id
    JOIN sections sec ON sec.id = sch.section_id
    WHERE sch.professor_id = ?
    ORDER BY sec.section_name, s.subject_title";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $professor_id);

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $subjects = [];
    $sections = new ArrayObject();

    while ($row = $result->fetch_assoc()) {
        $subjects[] = [
            'id' => $row['id'],
            'subject_title' => $row['subject_title'],
            'semester' => $row['semester'],
            'year_level' => $row['yearLevel'],
            'section_id' => $row['section_id'],
            'strand' => $row['strand'],
            'schedule' => [
                'day' => $row['schedule_day'],
                'time' => $row['schedule_start'] . ' - ' . $row['schedule_end'],
                'room' => $row['room']
            ]
        ];

        // Store unique sections with all necessary information
        $sectionKey = $row['section_id'];
        if (!isset($sections[$sectionKey])) {
            $sections[$sectionKey] = [
                'id' => $row['section_id'],
                'section_name' => $row['section_name'],
                'school_year' => $row['school_year'],
                'yearLevel' => $row['yearLevel'],
                'strand' => $row['section_strand']
            ];
        }
    }

    // Add debug logging
    error_log("Subjects data: " . print_r($subjects, true));
    error_log("Sections data: " . print_r(array_values((array)$sections), true));

    echo json_encode([
        'success' => true,
        'subjects' => $subjects,
        'sections' => array_values((array)$sections)
    ]);

} catch (Exception $e) {
    error_log("Error in get_teacher_subjects.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'sql_error' => $conn->error ?? null,
            'trace' => $e->getTraceAsString()
        ]
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
