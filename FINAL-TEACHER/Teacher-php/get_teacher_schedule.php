<?php
ob_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

session_start();

if (!isset($_SESSION['professor_id'])) {
    ob_end_clean();
    die(json_encode(['error' => 'Not authenticated']));
}

try {
    require_once 'conn.php';
    
    $professor_id = $_SESSION['professor_id'];
    $year_level = isset($_GET['year_level']) ? $_GET['year_level'] : '11';
    
    // Prepare and execute schedule query
    $query = "SELECT DISTINCT 
        s.day_of_week,
        s.start_time,
        s.end_time,
        s.room,
        sub.subject_title,
        sec.section_name,
        sec.yearLevel
    FROM schedules s
    JOIN subjects sub ON s.subject_id = sub.id
    JOIN sections sec ON s.section_id = sec.id
    WHERE s.professor_id = ? 
    AND sec.yearLevel = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $professor_id, $year_level);
    $stmt->execute();
    $result = $stmt->get_result();
    $schedule = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get year levels
    $yearQuery = "SELECT DISTINCT sec.yearLevel 
                 FROM schedules s
                 JOIN sections sec ON s.section_id = sec.id
                 WHERE s.professor_id = ?
                 ORDER BY sec.yearLevel";
    
    $yearStmt = $conn->prepare($yearQuery);
    $yearStmt->bind_param("i", $professor_id);
    $yearStmt->execute();
    $yearResult = $yearStmt->get_result();
    $yearLevels = [];
    while ($row = $yearResult->fetch_array(MYSQLI_NUM)) {
        $yearLevels[] = $row[0];
    }
    
    $stmt->close();
    $yearStmt->close();
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'schedule' => $schedule,
        'yearLevels' => $yearLevels
    ]);

} catch (Exception $e) {
    ob_end_clean();
    error_log("Error in get_teacher_schedule.php: " . $e->getMessage());
    echo json_encode([
        'error' => 'Database error',
        'debug' => $e->getMessage()
    ]);
}
?>