<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'conn.php';

echo "<pre>";
echo "Session Data:\n";
print_r($_SESSION);

if (isset($_SESSION['professor_id'])) {
    $professor_id = $_SESSION['professor_id'];
    
    echo "\nChecking professor ID: $professor_id\n\n";
    
    // Check professor table
    $query = "SELECT * FROM professor WHERE professorID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $professor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "Professor exists in database: " . ($result->num_rows > 0 ? "Yes" : "No") . "\n";
    
    if ($result->num_rows > 0) {
        $professor = $result->fetch_assoc();
        echo "\nProfessor Details:\n";
        print_r($professor);
    }
    
    // Check if professor has any schedules
    $query = "SELECT COUNT(*) as count FROM schedules WHERE professor_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $professor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    echo "\nNumber of schedules assigned: $count\n";
}

echo "</pre>";
?>
