<?php
if (!isset($conn)) {
    require_once 'conn.php';
}

try {
    $id = $_SESSION['user_id'];
    
    if (!isset($_SESSION['student_info'])) {
        $stmt = $conn->prepare("SELECT * FROM student WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Student not found");
        }
        
        $_SESSION['student_info'] = $result->fetch_assoc();
        $stmt->close();
    }

    $student = $_SESSION['student_info'];

} catch (Exception $e) {
    error_log("Error in fetchAttendance.php: " . $e->getMessage());
    $error_message = "Failed to load student data";
}