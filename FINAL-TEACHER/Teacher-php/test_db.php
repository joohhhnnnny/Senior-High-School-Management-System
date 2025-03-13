<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Testing Database Connection\n";
echo "-------------------------\n\n";

try {
    require_once 'conn.php';
    
    echo "Connection established successfully\n\n";
    
    echo "Session Data:\n";
    session_start();
    print_r($_SESSION);
    
    echo "\nTesting Queries:\n";
    
    // Test schedules table
    $result = $conn->query("SELECT COUNT(*) as count FROM schedules");
    if ($result === false) {
        throw new Exception("Error querying schedules: " . $conn->error);
    }
    echo "\nTotal schedules: " . $result->fetch_assoc()['count'] . "\n";
    
    // Test subjects table
    $result = $conn->query("SELECT COUNT(*) as count FROM subjects");
    if ($result === false) {
        throw new Exception("Error querying subjects: " . $conn->error);
    }
    echo "Total subjects: " . $result->fetch_assoc()['count'] . "\n";
    
    // Test student_subjects table
    $result = $conn->query("SELECT COUNT(*) as count FROM student_subjects");
    if ($result === false) {
        throw new Exception("Error querying student_subjects: " . $conn->error);
    }
    echo "Total student_subjects: " . $result->fetch_assoc()['count'] . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

if (isset($conn)) {
    $conn->close();
    echo "\nConnection closed successfully";
}
echo "</pre>";
?>
