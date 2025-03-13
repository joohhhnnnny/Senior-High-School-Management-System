<?php
session_start();
include 'conn.php';

echo "<pre>";
echo "Session Data:\n";
print_r($_SESSION);

if (isset($_SESSION['professor_id'])) {
    $professor_id = $_SESSION['professor_id'];
    echo "\nTesting Queries for Professor ID: $professor_id\n\n";

    // Test subjects query
    $query = "SELECT COUNT(*) as count FROM schedules WHERE professor_id = $professor_id";
    $result = $conn->query($query);
    echo "Subjects Query Result:\n";
    print_r($result->fetch_assoc());

    // Test students query
    $query = "SELECT COUNT(DISTINCT ss.student_id) as count 
              FROM student_subjects ss
              INNER JOIN schedules s ON ss.subject_id = s.subject_id
              WHERE s.professor_id = $professor_id";
    $result = $conn->query($query);
    echo "\nStudents Query Result:\n";
    print_r($result->fetch_assoc());
}
echo "</pre>";
?>
