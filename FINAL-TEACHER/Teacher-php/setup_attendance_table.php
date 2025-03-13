<?php
require_once 'conn.php';

try {
    // Drop the existing foreign key constraint
    $conn->query("ALTER TABLE student_attendance DROP FOREIGN KEY student_attendance_ibfk_2");
    
    // Drop the recorded_by column
    $conn->query("ALTER TABLE student_attendance DROP COLUMN recorded_by");
    
    echo "Database structure updated successfully";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
