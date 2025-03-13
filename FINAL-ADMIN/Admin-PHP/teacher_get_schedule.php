<?php
header('Content-Type: application/json');
require_once 'conn.php';

try {
    error_log("\n=== Teacher Schedule Request ===");
    error_log("Time: " . date('Y-m-d H:i:s'));
    
    // Validate ID parameter
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    error_log("Requested ID: " . ($id ?? 'null'));
    
    if (!$id) {
        throw new Exception('Invalid or missing ID parameter');
    }

    // Test database connection
    if (!$conn) {
        error_log("Database connection failed!");
        throw new Exception('Database connection failed');
    }
    error_log("Database connected successfully");

    // Get teacher info
    $teacherQuery = "SELECT id, professorID, fullname, email, phoneNumber FROM professor WHERE id = ?";
    error_log("Executing teacher query: " . $teacherQuery);
    
    $stmt = $conn->prepare($teacherQuery);
    $stmt->execute([$id]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$teacher) {
        error_log("No teacher found with ID: $id");
        throw new Exception('Teacher not found');
    }
    error_log("Teacher found: " . json_encode($teacher));

    // Get schedules with subject and section details
    $scheduleQuery = "
        SELECT 
            s.id,
            sub.subject_title as subject,
            sec.section_name,
            s.day_of_week as day,
            s.start_time,
            s.end_time,
            s.room,
            s.semester,
            s.school_year
        FROM schedules s
        JOIN subjects sub ON s.subject_id = sub.id
        JOIN sections sec ON s.section_id = sec.id
        WHERE s.professor_id = ?
        ORDER BY 
            FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
            s.start_time
    ";
    
    error_log("Executing schedule query: " . $scheduleQuery);
    
    $stmt = $conn->prepare($scheduleQuery);
    $stmt->execute([$id]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Found " . count($schedules) . " schedules");
    error_log("Schedule data: " . json_encode($schedules));

    // Format time for display
    foreach ($schedules as &$schedule) {
        $schedule['start_time'] = date('h:i A', strtotime($schedule['start_time']));
        $schedule['end_time'] = date('h:i A', strtotime($schedule['end_time']));
    }

    // Prepare response
    $response = [
        'success' => true,
        'teacher' => $teacher,
        'schedule' => $schedules,
        'debug' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'teacherId' => $id,
            'scheduleCount' => count($schedules)
        ]
    ];

    error_log("Sending response: " . json_encode($response));
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in teacher_get_schedule.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => __FILE__,
            'line' => $e->getLine()
        ]
    ]);
}

error_log("=== Teacher Schedule Request End ===\n");
?>
