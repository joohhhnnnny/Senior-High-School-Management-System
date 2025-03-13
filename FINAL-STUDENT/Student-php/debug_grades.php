<?php
require_once 'conn.php';

$studentID = 27; // The student ID we're debugging

// First, let's debug the student table structure
$tableQuery = "DESCRIBE student";
$tableResult = $conn->query($tableQuery);
$tableStructure = $tableResult->fetch_all(MYSQLI_ASSOC);

// Check student data using studentID field
$studentQuery = "SELECT * FROM student WHERE studentID = ?";
$stmt = $conn->prepare($studentQuery);
$stmt->bind_param("i", $studentID);
$stmt->execute();
$studentResult = $stmt->get_result();
$studentData = $studentResult->fetch_assoc();
$stmt->close();

// Check grades data
$gradesQuery = "SELECT * FROM student_grades WHERE studentID = ?";
$stmt = $conn->prepare($gradesQuery);
$stmt->bind_param("i", $studentID);
$stmt->execute();
$gradesResult = $stmt->get_result();
$gradesData = $gradesResult->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Output debug information
header('Content-Type: application/json');
echo json_encode([
    'table_structure' => $tableStructure,
    'student' => $studentData,
    'grades' => $gradesData,
    'query_used' => $studentQuery
], JSON_PRETTY_PRINT);
?>