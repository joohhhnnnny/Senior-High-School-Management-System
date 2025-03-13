<?php
require_once 'conn.php';

echo "<pre>";
echo "Checking student_attendance table structure:\n\n";

$result = $conn->query("DESCRIBE student_attendance");
while ($row = $result->fetch_assoc()) {
    print_r($row);
}

echo "\nTesting attendance insert:\n";

try {
    $test_data = [
        'studentID' => 1,
        'subject_title' => 'Test Subject',
        'date' => date('Y-m-d'),
        'status' => 'Present',
        'semester' => 'first',
        'yearLevel' => '11',
        'remarks' => 'Test remark'
    ];

    $query = "INSERT INTO student_attendance 
              (studentID, subject_title, date, status, semester, yearLevel, remarks) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issssss", 
        $test_data['studentID'],
        $test_data['subject_title'],
        $test_data['date'],
        $test_data['status'],
        $test_data['semester'],
        $test_data['yearLevel'],
        $test_data['remarks']
    );
    
    $result = $stmt->execute();
    echo "Test insert " . ($result ? "successful" : "failed") . "\n";
    
    if ($result) {
        // Clean up test data
        $conn->query("DELETE FROM student_attendance WHERE subject_title = 'Test Subject'");
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
