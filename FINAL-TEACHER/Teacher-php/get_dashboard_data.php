<?php
session_start();
date_default_timezone_set('Asia/Manila');
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'conn.php';
    
    if (!isset($_SESSION['professor_id'])) {
        throw new Exception('Professor ID not found in session');
    }

    $professor_id = intval($_SESSION['professor_id']);
    $response = [];

    // Get total subjects
    $subjects_query = "SELECT COUNT(DISTINCT s.subject_id) as total
                      FROM schedules s
                      WHERE s.professor_id = ?";
    $stmt = $conn->prepare($subjects_query);
    $stmt->bind_param("i", $professor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $response['total_subjects'] = $result->fetch_assoc()['total'];

    // Get total students with fixed query
    $students_query = "SELECT COUNT(DISTINCT st.id) as total
                      FROM student st
                      WHERE st.year_strand IN (
                          SELECT CONCAT(sec.yearLevel, '-', sec.strand)
                          FROM schedules sch
                          INNER JOIN sections sec ON sch.section_id = sec.id
                          WHERE sch.professor_id = ?
                          AND sch.semester = 'first'
                          AND sec.school_year = '2024-2025'
                      )";
    
    $stmt = $conn->prepare($students_query);
    $stmt->bind_param("i", $professor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $students_data = $result->fetch_assoc();
    $response['total_students'] = $students_data['total'];

    // Add debug information
    $debug_query = "SELECT DISTINCT st.year_strand, COUNT(st.id) as count
                   FROM student st
                   WHERE st.year_strand IN (
                       SELECT CONCAT(sec.yearLevel, '-', sec.strand)
                       FROM schedules sch
                       INNER JOIN sections sec ON sch.section_id = sec.id
                       WHERE sch.professor_id = ?
                   )
                   GROUP BY st.year_strand";
    
    $stmt = $conn->prepare($debug_query);
    $stmt->bind_param("i", $professor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student_counts = [];
    while ($row = $result->fetch_assoc()) {
        $student_counts[] = $row;
    }
    $response['debug_student_counts'] = $student_counts;

    // Get section info for debugging
    $section_query = "SELECT DISTINCT sec.yearLevel, sec.strand
                     FROM sections sec
                     INNER JOIN schedules sch ON sec.id = sch.section_id
                     WHERE sch.professor_id = ?";
    
    $stmt = $conn->prepare($section_query);
    $stmt->bind_param("i", $professor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $sections = [];
    while($row = $result->fetch_assoc()) {
        $sections[] = $row;
    }
    $response['debug_sections'] = $sections;

    // Get today's schedule with subject names
    $today = date('l');
    $current_time = date('H:i:s');

    $schedule_query = "SELECT 
        s.start_time,
        s.end_time,
        s.room,
        sub.subject_title as subject_name,
        sec.section_name,
        TIME_FORMAT(s.start_time, '%h:%i %p') as formatted_start_time,
        TIME_FORMAT(s.end_time, '%h:%i %p') as formatted_end_time
    FROM schedules s
    LEFT JOIN subjects sub ON s.subject_id = sub.id
    LEFT JOIN sections sec ON s.section_id = sec.id
    WHERE s.professor_id = ? 
    AND s.day_of_week = ?
    AND s.semester = 'first'  -- Add semester condition
    AND sec.school_year = '2024-2025'  -- Add school year condition
    ORDER BY s.start_time ASC";

    $stmt = $conn->prepare($schedule_query);
    $stmt->bind_param("is", $professor_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $schedules = [];
    while ($row = $result->fetch_assoc()) {
        // Set timezone to Asia/Manila
        date_default_timezone_set('Asia/Manila');
        
        $current_time = date('H:i:s');
        $start_time = $row['start_time'];
        $end_time = $row['end_time'];
        
        // Add debug information
        $row['debug'] = [
            'current_time' => $current_time,
            'start_time' => $start_time,
            'end_time' => $end_time,
        ];
        
        // Simple time string comparison
        if (strtotime($current_time) >= strtotime($start_time) && 
            strtotime($current_time) <= strtotime($end_time)) {
            $status = 'ongoing';
        } elseif (strtotime($current_time) > strtotime($end_time)) {
            $status = 'done';
        } else {
            $status = 'upcoming';
        }
        
        $row['status'] = $status;
        $schedules[] = $row;
    }

    $response['schedules'] = $schedules;

    // Add debugging information
    $response['debug_info'] = [
        'current_day' => $today,
        'current_time' => $current_time,
        'timezone' => date_default_timezone_get(),
        'raw_schedule_count' => count($schedules)
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'stack_trace' => $e->getTraceAsString()
    ]);
}

if (isset($conn)) {
    $conn->close();
}
?>