<?php
header('Content-Type: application/json');
require_once '../Portal-Main/conn.php';

try {
    $strand = $_GET['strand'] ?? '';
    $semester = $_GET['semester'] ?? 'first';
    $school_year = $_GET['school_year'] ?? '2024-2025';
    
    if (!$strand) {
        throw new Exception('Strand is required');
    }

    $query = "
        SELECT 
            s.id as schedule_id,
            s.day_of_week as day,
            TIME_FORMAT(s.start_time, '%H:%i') as start_time,
            TIME_FORMAT(s.end_time, '%H:%i') as end_time,
            s.room,
            sec.section_name,
            sub.subject_title AS subject_name,
            sub.subject_type,
            CONCAT(p.fullname) AS professor_name,
            sec.strand
        FROM schedules s
        JOIN sections sec ON s.section_id = sec.id
        JOIN subjects sub ON s.subject_id = sub.id
        JOIN professor p ON s.professor_id = p.id
        WHERE sec.strand = ? 
        AND sec.yearLevel = '11'
        AND s.semester = ?
        AND s.school_year = ?
        ORDER BY s.start_time
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $strand, $semester, $school_year);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $schedules = $result->fetch_all(MYSQLI_ASSOC);

    // Debug information
    error_log("Fetched schedules for strand: $strand, semester: $semester");
    error_log("Number of schedules found: " . count($schedules));

    echo json_encode([
        'success' => true,
        'schedules' => $schedules,
        'debug' => [
            'strand' => $strand,
            'semester' => $semester,
            'count' => count($schedules)
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in get_grade11_schedules.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
